<?php
/**
 * Security scanner — checks site configuration and exposure.
 *
 * @package NeoNews_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Scanner engine.
 */
class NeoNews_Security_Scanner {

    /**
     * Run full scan.
     *
     * @return array
     */
    public static function run_scan() {
        $findings = array();

        $checks = array(
            'check_debug_mode',
            'check_file_editing',
            'check_xmlrpc',
            'check_https',
            'check_wp_version_exposed',
            'check_default_admin',
            'check_db_prefix',
            'check_uploads_protection',
            'check_security_headers',
            'check_rest_user_enum',
            'check_php_version',
            'check_inactive_admins',
            'check_outdated_components',
            'check_display_errors',
            'check_file_permissions',
            'check_security_plugin_settings',
            'check_membership_security',
        );

        foreach ( $checks as $method ) {
            if ( method_exists( __CLASS__, $method ) ) {
                $result = self::$method();
                if ( $result ) {
                    if ( isset( $result['id'] ) ) {
                        $findings[] = $result;
                    } else {
                        $findings = array_merge( $findings, $result );
                    }
                }
            }
        }

        update_option( 'neonews_security_last_scan', array(
            'time'     => time(),
            'findings' => $findings,
            'score'    => self::calculate_score( $findings ),
        ), false );

        NeoNews_Security_Log::log(
            'scan',
            'info',
            sprintf(
                /* translators: %d: finding count */
                __( 'Security scan completed — %d findings.', 'neonews-security' ),
                count( $findings )
            ),
            array( 'score' => self::calculate_score( $findings ) )
        );

        return $findings;
    }

    /**
     * Calculate security score 0-100.
     *
     * @param array $findings Findings.
     * @return int
     */
    public static function calculate_score( $findings ) {
        $score = 100;
        $weights = array(
            'critical' => 25,
            'high'     => 15,
            'medium'   => 8,
            'low'      => 3,
        );

        foreach ( $findings as $f ) {
            $sev = $f['severity'] ?? 'low';
            $score -= $weights[ $sev ] ?? 2;
        }

        return max( 0, min( 100, $score ) );
    }

    /**
     * Get last scan.
     *
     * @return array
     */
    public static function get_last_scan() {
        $last = get_option( 'neonews_security_last_scan', array() );
        return wp_parse_args( $last, array(
            'time'     => 0,
            'findings' => array(),
            'score'    => 0,
        ) );
    }

    /**
     * Build finding array.
     *
     * @param string $id       Fix ID.
     * @param string $severity Severity.
     * @param string $title    Title.
     * @param string $detail   Detail.
     * @param bool   $fixable  Auto-fixable.
     * @return array
     */
    private static function finding( $id, $severity, $title, $detail, $fixable = true ) {
        return array(
            'id'       => $id,
            'severity' => $severity,
            'title'    => $title,
            'detail'   => $detail,
            'fixable'  => $fixable,
        );
    }

    /**
     * Debug mode enabled.
     *
     * @return array|null
     */
    private static function check_debug_mode() {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            return self::finding(
                'disable_debug_display',
                'high',
                __( 'WordPress debug mode is ON', 'neonews-security' ),
                __( 'WP_DEBUG exposes errors and paths to visitors. Disable in production wp-config.php.', 'neonews-security' ),
                false
            );
        }
        return null;
    }

    /**
     * File editor enabled.
     *
     * @return array|null
     */
    private static function check_file_editing() {
        if ( ! defined( 'DISALLOW_FILE_EDIT' ) || ! DISALLOW_FILE_EDIT ) {
            $settings = NeoNews_Security::get_settings();
            if ( empty( $settings['disable_file_edit'] ) ) {
                return self::finding(
                    'disable_file_edit',
                    'high',
                    __( 'Theme/plugin file editor is enabled', 'neonews-security' ),
                    __( 'Attackers who gain admin access can edit PHP files from the dashboard.', 'neonews-security' )
                );
            }
        }
        return null;
    }

    /**
     * XML-RPC enabled.
     *
     * @return array|null
     */
    private static function check_xmlrpc() {
        if ( apply_filters( 'xmlrpc_enabled', true ) ) {
            $settings = NeoNews_Security::get_settings();
            if ( empty( $settings['block_xmlrpc'] ) ) {
                return self::finding(
                    'block_xmlrpc',
                    'medium',
                    __( 'XML-RPC is enabled', 'neonews-security' ),
                    __( 'XML-RPC is often abused for brute-force and pingback attacks.', 'neonews-security' )
                );
            }
        }
        return null;
    }

    /**
     * HTTPS check.
     *
     * @return array|null
     */
    private static function check_https() {
        if ( ! is_ssl() && ! self::is_local_env() ) {
            return self::finding(
                'force_ssl_admin',
                'critical',
                __( 'Site is not using HTTPS', 'neonews-security' ),
                __( 'Traffic and login credentials can be intercepted. Enable SSL certificate site-wide.', 'neonews-security' ),
                false
            );
        }
        return null;
    }

    /**
     * WP version in HTML.
     *
     * @return array|null
     */
    private static function check_wp_version_exposed() {
        $settings = NeoNews_Security::get_settings();
        if ( empty( $settings['hide_wp_version'] ) ) {
            return self::finding(
                'hide_wp_version',
                'low',
                __( 'WordPress version may be exposed', 'neonews-security' ),
                __( 'Hiding the generator tag reduces fingerprinting for targeted exploits.', 'neonews-security' )
            );
        }
        return null;
    }

    /**
     * Default admin username.
     *
     * @return array|null
     */
    private static function check_default_admin() {
        if ( username_exists( 'admin' ) ) {
            return self::finding(
                'rename_admin_user',
                'medium',
                __( 'Default "admin" username exists', 'neonews-security' ),
                __( 'Create a new administrator account and delete the "admin" user manually.', 'neonews-security' ),
                false
            );
        }
        return null;
    }

    /**
     * Default DB prefix.
     *
     * @return array|null
     */
    private static function check_db_prefix() {
        global $wpdb;
        if ( 'wp_' === $wpdb->prefix ) {
            return self::finding(
                'change_db_prefix',
                'low',
                __( 'Default database table prefix (wp_)', 'neonews-security' ),
                __( 'Custom prefixes add defense-in-depth. Change only on fresh installs.', 'neonews-security' ),
                false
            );
        }
        return null;
    }

    /**
     * Uploads PHP execution.
     *
     * @return array|null
     */
    private static function check_uploads_protection() {
        $upload_dir = wp_upload_dir();
        if ( empty( $upload_dir['basedir'] ) ) {
            return null;
        }

        $htaccess = trailingslashit( $upload_dir['basedir'] ) . '.htaccess';
        $protected = file_exists( $htaccess ) && false !== strpos( file_get_contents( $htaccess ), 'deny' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

        $settings = NeoNews_Security::get_settings();
        if ( ! $protected && ! empty( $settings['uploads_php_block'] ) ) {
            return self::finding(
                'block_uploads_php',
                'high',
                __( 'Uploads folder may allow PHP execution', 'neonews-security' ),
                __( 'Malicious uploads could run as scripts. Add .htaccess rules to block PHP in uploads.', 'neonews-security' )
            );
        }

        if ( ! $protected && empty( $settings['uploads_php_block'] ) ) {
            return self::finding(
                'block_uploads_php',
                'high',
                __( 'Uploads folder is not hardened', 'neonews-security' ),
                __( 'Enable uploads PHP blocking in Security Center.', 'neonews-security' )
            );
        }

        return null;
    }

    /**
     * Security headers.
     *
     * @return array|null
     */
    private static function check_security_headers() {
        $settings = NeoNews_Security::get_settings();
        if ( empty( $settings['security_headers'] ) ) {
            return self::finding(
                'enable_security_headers',
                'medium',
                __( 'Security headers are disabled', 'neonews-security' ),
                __( 'X-Frame-Options, X-Content-Type-Options, and Referrer-Policy reduce clickjacking and MIME attacks.', 'neonews-security' )
            );
        }
        return null;
    }

    /**
     * REST user enumeration.
     *
     * @return array|null
     */
    private static function check_rest_user_enum() {
        $settings = NeoNews_Security::get_settings();
        if ( empty( $settings['block_user_enum'] ) ) {
            return self::finding(
                'block_user_enumeration',
                'medium',
                __( 'Author/user enumeration not blocked', 'neonews-security' ),
                __( '?author=1 and REST user endpoints can reveal usernames for brute-force attacks.', 'neonews-security' )
            );
        }
        return null;
    }

    /**
     * PHP version.
     *
     * @return array|null
     */
    private static function check_php_version() {
        if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
            return self::finding(
                'upgrade_php',
                'high',
                __( 'Outdated PHP version', 'neonews-security' ),
                sprintf(
                    /* translators: %s: PHP version */
                    __( 'Running PHP %s. Upgrade to PHP 8.1+ for security patches.', 'neonews-security' ),
                    PHP_VERSION
                ),
                false
            );
        }
        return null;
    }

    /**
     * Inactive admin accounts.
     *
     * @return array|null
     */
    private static function check_inactive_admins() {
        $admins = get_users( array(
            'role'   => 'administrator',
            'number' => 50,
        ) );

        $stale = array();
        foreach ( $admins as $admin ) {
            $last = get_user_meta( $admin->ID, 'neonews_last_login', true );
            if ( ! $last && ( time() - strtotime( $admin->user_registered ) ) > YEAR_IN_SECONDS ) {
                $stale[] = $admin->user_login;
            }
        }

        if ( ! empty( $stale ) ) {
            return self::finding(
                'review_stale_admins',
                'low',
                __( 'Stale administrator accounts detected', 'neonews-security' ),
                __( 'Review unused admin accounts and remove or demote them.', 'neonews-security' ),
                false
            );
        }
        return null;
    }

    /**
     * Outdated plugins/themes.
     *
     * @return array
     */
    private static function check_outdated_components() {
        $findings = array();
        if ( ! function_exists( 'get_plugin_updates' ) ) {
            require_once ABSPATH . 'wp-admin/includes/update.php';
        }

        wp_update_plugins();
        wp_update_themes();

        $plugin_updates = get_plugin_updates();
        if ( ! empty( $plugin_updates ) ) {
            $findings[] = self::finding(
                'update_plugins',
                'high',
                sprintf(
                    /* translators: %d: count */
                    __( '%d plugin update(s) available', 'neonews-security' ),
                    count( $plugin_updates )
                ),
                __( 'Outdated plugins are the #1 WordPress compromise vector. Update from Dashboard → Updates.', 'neonews-security' ),
                false
            );
        }

        $theme_updates = get_theme_updates();
        if ( ! empty( $theme_updates ) ) {
            $findings[] = self::finding(
                'update_themes',
                'medium',
                sprintf(
                    /* translators: %d: count */
                    __( '%d theme update(s) available', 'neonews-security' ),
                    count( $theme_updates )
                ),
                __( 'Update themes to patch known vulnerabilities.', 'neonews-security' ),
                false
            );
        }

        return $findings;
    }

    /**
     * display_errors on.
     *
     * @return array|null
     */
    private static function check_display_errors() {
        if ( ini_get( 'display_errors' ) ) {
            return self::finding(
                'disable_display_errors',
                'medium',
                __( 'PHP display_errors is enabled', 'neonews-security' ),
                __( 'Server exposes PHP errors to visitors. Disable in php.ini or hosting panel.', 'neonews-security' ),
                false
            );
        }
        return null;
    }

    /**
     * Writable wp-config.
     *
     * @return array|null
     */
    private static function check_file_permissions() {
        if ( file_exists( ABSPATH . 'wp-config.php' ) && is_writable( ABSPATH . 'wp-config.php' ) ) {
            return self::finding(
                'harden_file_permissions',
                'medium',
                __( 'wp-config.php is world-writable', 'neonews-security' ),
                __( 'Set file permissions to 440 or 400 via FTP/hosting panel.', 'neonews-security' ),
                false
            );
        }
        return null;
    }

    /**
     * Security plugin toggles off.
     *
     * @return array|null
     */
    private static function check_security_plugin_settings() {
        $settings = NeoNews_Security::get_settings();
        $off      = 0;
        foreach ( array( 'login_limit_enabled', 'security_headers', 'block_xmlrpc' ) as $key ) {
            if ( empty( $settings[ $key ] ) ) {
                $off++;
            }
        }
        if ( $off >= 2 ) {
            return self::finding(
                'enable_all_hardening',
                'medium',
                __( 'Multiple hardening features are disabled', 'neonews-security' ),
                __( 'Enable login limits, security headers, and XML-RPC blocking for baseline protection.', 'neonews-security' )
            );
        }
        return null;
    }

    /**
     * Membership monetization security checks.
     *
     * @return array|null
     */
    private static function check_membership_security() {
        if ( ! class_exists( 'NeoNews_Membership' ) ) {
            return null;
        }

        $findings = array();
        $sec      = NeoNews_Security::get_settings();
        $member   = NeoNews_Membership::get_settings();

        if ( ! empty( $member['payment_gateway_enabled'] ) ) {
            $has_secret = ! empty( $member['stripe_test_secret_key'] ) || ! empty( $member['stripe_live_secret_key'] );
            if ( $has_secret && empty( $sec['encrypt_api_keys'] ) ) {
                $findings[] = self::finding(
                    'encrypt_api_keys',
                    'high',
                    __( 'Membership Stripe keys stored without encryption', 'neonews-security' ),
                    __( 'Enable Encrypt API keys so Stripe secret keys in Membership settings are stored encrypted at rest.', 'neonews-security' )
                );
            }
        }

        if ( empty( $sec['login_limit_enabled'] ) && ! empty( $member['enable_paywall'] ) ) {
            $findings[] = self::finding(
                'enable_login_limit',
                'medium',
                __( 'Login brute-force protection off while paywall is active', 'neonews-security' ),
                __( 'Enable Login brute-force protection to reduce account takeover attempts against premium accounts.', 'neonews-security' )
            );
        }

        if ( empty( $findings ) ) {
            return null;
        }

        return count( $findings ) === 1 ? $findings[0] : $findings;
    }

    /**
     * Is local dev environment.
     *
     * @return bool
     */
    private static function is_local_env() {
        $host = wp_parse_url( home_url(), PHP_URL_HOST );
        return in_array( $host, array( 'localhost', '127.0.0.1' ), true ) || ( is_string( $host ) && false !== strpos( $host, '.local' ) );
    }
}
