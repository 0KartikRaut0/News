<?php
/**
 * Runtime hardening rules.
 *
 * @package NeoNews_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hardening hooks.
 */
class NeoNews_Hardening {

    /**
     * Init hooks based on settings.
     */
    public static function init() {
        $settings = NeoNews_Security::get_settings();

        if ( ! empty( $settings['block_xmlrpc'] ) ) {
            add_filter( 'xmlrpc_enabled', '__return_false' );
            add_filter( 'wp_headers', array( __CLASS__, 'block_xmlrpc_header' ) );
        }

        if ( ! empty( $settings['hide_wp_version'] ) ) {
            remove_action( 'wp_head', 'wp_generator' );
            add_filter( 'the_generator', '__return_empty_string' );
        }

        if ( ! empty( $settings['disable_file_edit'] ) ) {
            add_filter( 'file_mod_allowed', array( __CLASS__, 'disallow_file_edit' ), 10, 2 );
        }

        if ( ! empty( $settings['security_headers'] ) ) {
            add_filter( 'wp_headers', array( __CLASS__, 'security_headers' ) );
        }

        if ( ! empty( $settings['block_user_enum'] ) ) {
            add_action( 'template_redirect', array( __CLASS__, 'block_author_enum' ), 1 );
            add_filter( 'rest_endpoints', array( __CLASS__, 'restrict_user_rest' ) );
        }

        if ( ! empty( $settings['force_secure_auth_cookie'] ) && is_ssl() ) {
            add_filter( 'secure_auth_cookie', '__return_true' );
            add_filter( 'secure_logged_in_cookie', '__return_true' );
        }

        add_filter( 'login_errors', array( __CLASS__, 'generic_login_errors' ) );
    }

    /**
     * Apply enabled rules on activation/settings save.
     */
    public static function maybe_apply_enabled_rules() {
        $settings = NeoNews_Security::get_settings();
        if ( ! empty( $settings['uploads_php_block'] ) ) {
            NeoNews_Auto_Fix::write_uploads_htaccess();
        }
        if ( ! empty( $settings['disable_file_edit'] ) ) {
            NeoNews_Auto_Fix::write_mu_plugin_hardening();
        }
    }

    /**
     * Block file editor.
     *
     * @param bool   $allowed Allowed.
     * @param string $context Context.
     * @return bool
     */
    public static function disallow_file_edit( $allowed, $context ) {
        if ( 'edit_plugins' === $context || 'edit_themes' === $context ) {
            return false;
        }
        return $allowed;
    }

    /**
     * Security headers.
     *
     * @param array $headers Headers.
     * @return array
     */
    public static function security_headers( $headers ) {
        $headers['X-Content-Type-Options']    = 'nosniff';
        $headers['X-Frame-Options']            = 'SAMEORIGIN';
        $headers['Referrer-Policy']            = 'strict-origin-when-cross-origin';
        $headers['Permissions-Policy']          = 'camera=(), microphone=(), geolocation=()';
        $headers['X-XSS-Protection']            = '1; mode=block';
        if ( is_ssl() ) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }
        return $headers;
    }

    /**
     * Block ?author= enumeration.
     */
    public static function block_author_enum() {
        if ( is_admin() || ! isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }
        if ( is_numeric( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            wp_safe_redirect( home_url( '/' ), 301 );
            exit;
        }
    }

    /**
     * Restrict user listing via REST for non-admins.
     *
     * @param array $endpoints Endpoints.
     * @return array
     */
    public static function restrict_user_rest( $endpoints ) {
        if ( current_user_can( 'list_users' ) ) {
            return $endpoints;
        }
        if ( isset( $endpoints['/wp/v2/users'] ) ) {
            unset( $endpoints['/wp/v2/users'] );
        }
        if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
            unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
        }
        return $endpoints;
    }

    /**
     * Generic login errors.
     *
     * @return string
     */
    public static function generic_login_errors() {
        return __( 'Invalid credentials. Please try again.', 'neonews-security' );
    }

    /**
     * Block XML-RPC via header on pingback path.
     *
     * @param array $headers Headers.
     * @return array
     */
    public static function block_xmlrpc_header( $headers ) {
        if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
            status_header( 403 );
            exit;
        }
        return $headers;
    }
}
