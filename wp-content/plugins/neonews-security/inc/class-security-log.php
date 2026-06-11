<?php
/**
 * Security audit log.
 *
 * @package NeoNews_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Security event logger.
 */
class NeoNews_Security_Log {

    /**
     * Table name without prefix.
     */
    const TABLE = 'neonews_security_log';

    /**
     * Create DB table.
     */
    public static function create_table() {
        global $wpdb;

        $table   = $wpdb->prefix . self::TABLE;
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL DEFAULT '',
            severity varchar(20) NOT NULL DEFAULT 'info',
            message text NOT NULL,
            context longtext NULL,
            ip_hash varchar(64) NOT NULL DEFAULT '',
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY event_type (event_type),
            KEY severity (severity),
            KEY created_at (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Upgrade if needed.
     */
    public static function maybe_upgrade_table() {
        if ( get_option( 'neonews_security_db_version' ) !== NEONEWS_SECURITY_DB_VERSION ) {
            self::create_table();
            update_option( 'neonews_security_db_version', NEONEWS_SECURITY_DB_VERSION );
        }
    }

    /**
     * Log an event.
     *
     * @param string $type     Event type.
     * @param string $severity info|low|medium|high|critical.
     * @param string $message  Message.
     * @param array  $context  Extra data.
     * @param int    $user_id  User ID.
     */
    public static function log( $type, $severity, $message, $context = array(), $user_id = 0 ) {
        global $wpdb;

        $table = $wpdb->prefix . self::TABLE;
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            self::create_table();
        }

        $user_id = $user_id ? $user_id : get_current_user_id();
        $context_json = ! empty( $context ) ? wp_json_encode( $context ) : '';
        if ( $context_json ) {
            $context_json = NeoNews_Encryption::encrypt( $context_json );
        }

        $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $table,
            array(
                'event_type' => sanitize_key( $type ),
                'severity'   => sanitize_key( $severity ),
                'message'    => sanitize_text_field( $message ),
                'context'    => $context_json,
                'ip_hash'    => self::hash_ip(),
                'user_id'    => absint( $user_id ),
                'created_at' => current_time( 'mysql', true ),
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
        );

        self::maybe_prune();
    }

    /**
     * Hash client IP for privacy.
     *
     * @return string
     */
    public static function hash_ip() {
        $ip = '';
        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
            $ip = trim( explode( ',', $ip )[0] );
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }
        return md5( $ip . wp_salt( 'nonce' ) . gmdate( 'Y-m-d' ) );
    }

    /**
     * Prune old logs.
     */
    private static function maybe_prune() {
        $settings = NeoNews_Security::get_settings();
        $days     = absint( $settings['log_retention_days'] );
        if ( $days < 7 ) {
            $days = 7;
        }

        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "DELETE FROM {$table} WHERE created_at < %s",
                gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) )
            )
        );
    }

    /**
     * Fetch recent logs.
     *
     * @param int    $limit  Limit.
     * @param string $type   Optional filter.
     * @return array
     */
    public static function get_recent( $limit = 50, $type = '' ) {
        global $wpdb;

        $table = $wpdb->prefix . self::TABLE;
        $limit = absint( $limit );
        $sql   = "SELECT * FROM {$table}";
        $args  = array();

        if ( $type ) {
            $sql   .= ' WHERE event_type = %s';
            $args[] = sanitize_key( $type );
        }

        $sql     .= ' ORDER BY created_at DESC LIMIT %d';
        $args[]   = $limit;

        if ( $args ) {
            $rows = $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        } else {
            $rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d", $limit ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        }

        foreach ( $rows as &$row ) {
            if ( ! empty( $row['context'] ) ) {
                $decrypted = NeoNews_Encryption::decrypt( $row['context'] );
                $row['context'] = json_decode( $decrypted, true );
            }
            if ( $row['user_id'] ) {
                $user = get_userdata( (int) $row['user_id'] );
                $row['user_label'] = $user ? $user->user_login : '';
            }
        }

        return $rows;
    }

    /**
     * Count by severity since date.
     *
     * @param string $since MySQL datetime.
     * @return array
     */
    public static function count_by_severity( $since = '' ) {
        global $wpdb;

        $table = $wpdb->prefix . self::TABLE;
        $since = $since ? $since : gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) );

        $results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT severity, COUNT(*) as total FROM {$table} WHERE created_at >= %s GROUP BY severity",
                $since
            ),
            ARRAY_A
        );

        $counts = array(
            'critical' => 0,
            'high'     => 0,
            'medium'   => 0,
            'low'      => 0,
            'info'     => 0,
        );

        foreach ( $results as $row ) {
            $sev = $row['severity'];
            if ( isset( $counts[ $sev ] ) ) {
                $counts[ $sev ] = (int) $row['total'];
            }
        }

        return $counts;
    }
}
