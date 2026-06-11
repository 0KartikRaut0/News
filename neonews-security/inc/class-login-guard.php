<?php
/**
 * Login brute-force protection.
 *
 * @package NeoNews_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Login guard.
 */
class NeoNews_Login_Guard {

    /**
     * Init hooks.
     */
    public static function init() {
        add_filter( 'authenticate', array( __CLASS__, 'check_lockout' ), 30, 1 );
        add_action( 'wp_login_failed', array( __CLASS__, 'log_failed_login' ) );
        add_action( 'wp_login', array( __CLASS__, 'log_success_login' ), 10, 2 );
    }

    /**
     * Settings helper.
     *
     * @return array
     */
    private static function limits() {
        $s = NeoNews_Security::get_settings();
        return array(
            'enabled'  => ! empty( $s['login_limit_enabled'] ),
            'max'      => max( 3, absint( $s['login_max_attempts'] ) ),
            'minutes'  => max( 5, absint( $s['login_lockout_minutes'] ) ),
        );
    }

    /**
     * Transient key for IP.
     *
     * @return string
     */
    private static function ip_key() {
        return 'nn_sec_login_' . NeoNews_Security_Log::hash_ip();
    }

    /**
     * Block auth if locked out.
     *
     * @param WP_User|WP_Error|null $user User.
     * @return WP_User|WP_Error|null
     */
    public static function check_lockout( $user ) {
        $limits = self::limits();
        if ( ! $limits['enabled'] ) {
            return $user;
        }

        $lock = get_transient( self::ip_key() . '_lock' );
        if ( $lock ) {
            NeoNews_Security_Log::log(
                'login_blocked',
                'high',
                __( 'Login blocked — too many failed attempts.', 'neonews-security' ),
                array( 'lockout' => true )
            );
            return new WP_Error(
                'nn_sec_lockout',
                sprintf(
                    /* translators: %d: minutes */
                    __( 'Too many failed login attempts. Try again in %d minutes.', 'neonews-security' ),
                    $limits['minutes']
                )
            );
        }

        return $user;
    }

    /**
     * Log failed login and increment counter.
     */
    public static function log_failed_login() {
        $limits = self::limits();
        if ( ! $limits['enabled'] ) {
            return;
        }

        $key   = self::ip_key();
        $count = (int) get_transient( $key );
        $count++;

        set_transient( $key, $count, $limits['minutes'] * MINUTE_IN_SECONDS );

        NeoNews_Security_Log::log(
            'login_failed',
            $count >= $limits['max'] ? 'high' : 'medium',
            sprintf(
                /* translators: %d: attempt count */
                __( 'Failed login attempt (%1$d of %2$d).', 'neonews-security' ),
                $count,
                $limits['max']
            ),
            array( 'attempt' => $count )
        );

        if ( $count >= $limits['max'] ) {
            set_transient( $key . '_lock', 1, $limits['minutes'] * MINUTE_IN_SECONDS );
            delete_transient( $key );

            NeoNews_Security_Log::log(
                'login_lockout',
                'critical',
                __( 'IP locked out after repeated failed logins.', 'neonews-security' )
            );

            self::maybe_send_alert( __( 'Login lockout triggered on your site.', 'neonews-security' ) );
        }
    }

    /**
     * Log successful login.
     *
     * @param string  $user_login Username.
     * @param WP_User $user       User.
     */
    public static function log_success_login( $user_login, $user ) {
        delete_transient( self::ip_key() );
        delete_transient( self::ip_key() . '_lock' );

        update_user_meta( $user->ID, 'neonews_last_login', current_time( 'mysql' ) );

        NeoNews_Security_Log::log(
            'login_success',
            'info',
            sprintf(
                /* translators: %s: username */
                __( 'Successful login: %s', 'neonews-security' ),
                $user_login
            ),
            array(),
            $user->ID
        );
    }

    /**
     * Email alert for critical events.
     *
     * @param string $subject Subject line.
     */
    private static function maybe_send_alert( $subject ) {
        $settings = NeoNews_Security::get_settings();
        if ( empty( $settings['email_alerts'] ) || empty( $settings['alert_email'] ) ) {
            return;
        }
        wp_mail(
            $settings['alert_email'],
            '[NeoNews Security] ' . $subject,
            $subject . "\n\n" . home_url( '/' ) . "\n" . admin_url( 'admin.php?page=neonews-security' )
        );
    }
}
