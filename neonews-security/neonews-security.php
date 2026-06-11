<?php
/**
 * Plugin Name: NeoNews Security Center
 * Plugin URI: https://example.com/neonews
 * Description: Security scanning, encrypted secrets, activity monitoring, AI threat analysis, and one-click hardening fixes for NeoNews.
 * Version: 1.0.0
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: NeoNews Team
 * License: GPL v2 or later
 * Text Domain: neonews-security
 *
 * @package NeoNews_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NEONEWS_SECURITY_VERSION', '1.0.0' );
define( 'NEONEWS_SECURITY_FILE', __FILE__ );
define( 'NEONEWS_SECURITY_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEONEWS_SECURITY_URL', plugin_dir_url( __FILE__ ) );
define( 'NEONEWS_SECURITY_DB_VERSION', '1.0' );

/**
 * Main plugin bootstrap.
 */
final class NeoNews_Security {

    /**
     * @var NeoNews_Security|null
     */
    private static $instance = null;

    /**
     * @return NeoNews_Security
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->includes();
        $this->hooks();
    }

    /**
     * Load classes.
     */
    private function includes() {
        require_once NEONEWS_SECURITY_DIR . 'inc/class-encryption.php';
        require_once NEONEWS_SECURITY_DIR . 'inc/class-security-log.php';
        require_once NEONEWS_SECURITY_DIR . 'inc/class-security-scanner.php';
        require_once NEONEWS_SECURITY_DIR . 'inc/class-threat-analyzer.php';
        require_once NEONEWS_SECURITY_DIR . 'inc/class-auto-fix.php';
        require_once NEONEWS_SECURITY_DIR . 'inc/class-hardening.php';
        require_once NEONEWS_SECURITY_DIR . 'inc/class-login-guard.php';
        require_once NEONEWS_SECURITY_DIR . 'inc/class-encrypted-options.php';

        if ( is_admin() ) {
            require_once NEONEWS_SECURITY_DIR . 'admin/class-security-dashboard.php';
        }
    }

    /**
     * Register hooks.
     */
    private function hooks() {
        register_activation_hook( NEONEWS_SECURITY_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( NEONEWS_SECURITY_FILE, array( $this, 'deactivate' ) );

        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( $this, 'init' ), 5 );
    }

    /**
     * Activation.
     */
    public function activate() {
        NeoNews_Security_Log::create_table();
        NeoNews_Encryption::ensure_key();
        NeoNews_Hardening::maybe_apply_enabled_rules();
        NeoNews_Encrypted_Options::migrate_existing_keys();
        update_option( 'neonews_security_db_version', NEONEWS_SECURITY_DB_VERSION );

        NeoNews_Security_Log::log(
            'system',
            'info',
            __( 'NeoNews Security Center activated.', 'neonews-security' )
        );
    }

    /**
     * Deactivation.
     */
    public function deactivate() {
        NeoNews_Security_Log::log(
            'system',
            'info',
            __( 'NeoNews Security Center deactivated.', 'neonews-security' )
        );
    }

    /**
     * Load translations.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'neonews-security', false, dirname( plugin_basename( NEONEWS_SECURITY_FILE ) ) . '/languages' );
    }

    /**
     * Init components.
     */
    public function init() {
        NeoNews_Security_Log::maybe_upgrade_table();
        NeoNews_Hardening::init();
        NeoNews_Login_Guard::init();
        NeoNews_Encrypted_Options::init();

        if ( is_admin() ) {
            NeoNews_Security_Dashboard::init();
        }
    }

    /**
     * Get plugin settings.
     *
     * @return array
     */
    public static function get_settings() {
        $defaults = array(
            'encrypt_api_keys'       => true,
            'security_headers'       => true,
            'block_xmlrpc'           => true,
            'hide_wp_version'        => true,
            'block_user_enum'        => true,
            'disable_file_edit'      => true,
            'login_limit_enabled'    => true,
            'login_max_attempts'     => 5,
            'login_lockout_minutes'  => 15,
            'uploads_php_block'      => true,
            'force_secure_auth_cookie' => true,
            'log_retention_days'     => 90,
            'email_alerts'           => false,
            'alert_email'            => get_option( 'admin_email' ),
        );

        $saved = get_option( 'neonews_security_settings', array() );
        return wp_parse_args( $saved, $defaults );
    }

    /**
     * Update settings.
     *
     * @param array $settings Settings.
     */
    public static function update_settings( $settings ) {
        update_option( 'neonews_security_settings', $settings );
        NeoNews_Hardening::maybe_apply_enabled_rules();
    }
}

NeoNews_Security::get_instance();
