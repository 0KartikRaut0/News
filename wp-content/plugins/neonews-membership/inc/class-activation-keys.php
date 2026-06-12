<?php
/**
 * Activation key generation and redemption.
 *
 * @package NeoNews_Membership
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Activation keys handler.
 */
class NeoNews_Activation_Keys {

    /**
     * Table name without prefix.
     */
    const TABLE = 'neonews_activation_keys';

    /**
     * Create DB table.
     */
    public static function create_table() {
        global $wpdb;

        $table   = self::get_table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            key_code varchar(32) NOT NULL,
            duration_days int unsigned NOT NULL DEFAULT 30,
            batch_label varchar(100) DEFAULT '',
            created_at datetime NOT NULL,
            key_expires_at datetime DEFAULT NULL,
            used_by bigint(20) unsigned DEFAULT NULL,
            used_at datetime DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            PRIMARY KEY  (id),
            UNIQUE KEY key_code (key_code),
            KEY status (status)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * @return string
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE;
    }

    /**
     * Generate a unique activation key.
     *
     * @param int    $duration_days Premium days granted on redeem.
     * @param string $batch_label   Optional admin label.
     * @param int    $key_valid_days Days until unused key expires (0 = never).
     * @return string|WP_Error Key code or error.
     */
    public static function generate_key( $duration_days = null, $batch_label = '', $key_valid_days = 0 ) {
        global $wpdb;

        if ( null === $duration_days ) {
            $duration_days = absint( NeoNews_Membership::get_settings( 'key_default_duration_days' ) );
        }

        $duration_days = max( 1, absint( $duration_days ) );
        $prefix          = sanitize_text_field( NeoNews_Membership::get_settings( 'key_prefix' ) );
        $prefix          = $prefix ? strtoupper( preg_replace( '/[^A-Z0-9\-]/', '', $prefix ) ) : 'NN';

        for ( $attempt = 0; $attempt < 10; $attempt++ ) {
            $code = self::build_key_code( $prefix );

            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    'SELECT id FROM ' . self::get_table_name() . ' WHERE key_code = %s',
                    $code
                )
            );

            if ( $exists ) {
                continue;
            }

            $key_expires = null;
            if ( $key_valid_days > 0 ) {
                $key_expires = gmdate( 'Y-m-d H:i:s', time() + ( $key_valid_days * DAY_IN_SECONDS ) );
            }

            $inserted = $wpdb->insert(
                self::get_table_name(),
                array(
                    'key_code'       => $code,
                    'duration_days'  => $duration_days,
                    'batch_label'    => sanitize_text_field( $batch_label ),
                    'created_at'     => current_time( 'mysql', true ),
                    'key_expires_at' => $key_expires,
                    'status'         => 'active',
                ),
                array( '%s', '%d', '%s', '%s', '%s', '%s' )
            );

            if ( $inserted ) {
                return $code;
            }
        }

        return new WP_Error( 'key_generation_failed', __( 'Could not generate a unique activation key.', 'neonews-membership' ) );
    }

    /**
     * @param string $prefix Key prefix.
     * @return string
     */
    private static function build_key_code( $prefix ) {
        $part = strtoupper( bin2hex( random_bytes( 4 ) ) );
        return $prefix . '-' . substr( $part, 0, 4 ) . '-' . substr( $part, 4, 4 );
    }

    /**
     * Redeem key for user.
     *
     * @param string $raw_code User-entered code.
     * @param int    $user_id  User ID.
     * @return true|WP_Error
     */
    public static function redeem_key( $raw_code, $user_id ) {
        global $wpdb;

        $user_id = absint( $user_id );
        if ( ! $user_id ) {
            return new WP_Error( 'invalid_user', __( 'You must be logged in to redeem a key.', 'neonews-membership' ) );
        }

        if ( self::is_rate_limited( $user_id ) ) {
            return new WP_Error( 'too_many_attempts', __( 'Too many failed attempts. Please wait 15 minutes and try again.', 'neonews-membership' ) );
        }

        $code = self::normalize_code( $raw_code );
        if ( strlen( $code ) < 8 ) {
            return new WP_Error( 'invalid_key', __( 'Invalid activation key. Please check and try again.', 'neonews-membership' ) );
        }

        $row = $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . self::get_table_name() . ' WHERE key_code = %s LIMIT 1',
                $code
            )
        );

        if ( ! $row ) {
            self::log_failed_key_attempt( $user_id, 'invalid_key' );
            return new WP_Error( 'invalid_key', __( 'Invalid activation key. Please check and try again.', 'neonews-membership' ) );
        }

        if ( 'active' !== $row->status ) {
            self::log_failed_key_attempt( $user_id, 'key_used' );
            return new WP_Error( 'key_used', __( 'This activation key has already been used or revoked.', 'neonews-membership' ) );
        }

        if ( ! empty( $row->key_expires_at ) && strtotime( $row->key_expires_at ) < time() ) {
            $wpdb->update(
                self::get_table_name(),
                array( 'status' => 'expired' ),
                array( 'id' => $row->id ),
                array( '%s' ),
                array( '%d' )
            );
            return new WP_Error( 'key_expired', __( 'This activation key has expired.', 'neonews-membership' ) );
        }

        self::clear_failed_attempts( $user_id );

        NeoNews_Membership_Levels::extend_premium( $user_id, (int) $row->duration_days );

        $wpdb->update(
            self::get_table_name(),
            array(
                'status'  => 'used',
                'used_by' => $user_id,
                'used_at' => current_time( 'mysql', true ),
            ),
            array( 'id' => $row->id ),
            array( '%s', '%d', '%s' ),
            array( '%d' )
        );

        do_action( 'neonews_activation_key_redeemed', $user_id, $code, (int) $row->duration_days );

        return true;
    }

    /**
     * @param string $raw Raw input.
     * @return string
     */
    public static function normalize_code( $raw ) {
        $code = strtoupper( preg_replace( '/[^A-Z0-9\-]/', '', (string) $raw ) );
        return trim( $code );
    }

    /**
     * @param int $limit Limit.
     * @return array
     */
    public static function get_recent_keys( $limit = 50 ) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM ' . self::get_table_name() . ' ORDER BY id DESC LIMIT %d',
                absint( $limit )
            )
        );
    }

    /**
     * @param int $key_id Key row ID.
     * @return bool
     */
    public static function revoke_key( $key_id ) {
        global $wpdb;

        return (bool) $wpdb->update(
            self::get_table_name(),
            array( 'status' => 'revoked' ),
            array(
                'id'     => absint( $key_id ),
                'status' => 'active',
            ),
            array( '%s' ),
            array( '%d', '%s' )
        );
    }

    /**
     * Rate-limit failed redemption attempts.
     *
     * @param int $user_id User ID.
     * @return bool
     */
    private static function is_rate_limited( $user_id ) {
        $attempts = (int) get_transient( self::get_attempt_transient_key( $user_id ) );
        return $attempts >= 5;
    }

    /**
     * @param int    $user_id User ID.
     * @param string $reason  Failure reason.
     */
    private static function log_failed_key_attempt( $user_id, $reason ) {
        $key      = self::get_attempt_transient_key( $user_id );
        $attempts = (int) get_transient( $key );
        set_transient( $key, $attempts + 1, 15 * MINUTE_IN_SECONDS );

        if ( class_exists( 'NeoNews_Security_Log' ) ) {
            NeoNews_Security_Log::log(
                'membership',
                'warning',
                __( 'Failed premium activation key attempt', 'neonews-membership' ),
                array(
                    'user_id' => $user_id,
                    'reason'  => sanitize_key( $reason ),
                )
            );
        }
    }

    /**
     * @param int $user_id User ID.
     */
    private static function clear_failed_attempts( $user_id ) {
        delete_transient( self::get_attempt_transient_key( $user_id ) );
    }

    /**
     * @param int $user_id User ID.
     * @return string
     */
    private static function get_attempt_transient_key( $user_id ) {
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
        return 'nn_key_fail_' . md5( absint( $user_id ) . '|' . $ip );
    }
}
