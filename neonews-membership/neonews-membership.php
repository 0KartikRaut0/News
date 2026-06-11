<?php
/**
 * Plugin Name: NeoNews Membership
 * Plugin URI: https://example.com/neonews
 * Description: Membership and premium content system for NeoNews. Activation keys, badges, exclusive content, and paywall.
 * Version: 2.0.0
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: NeoNews Team
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: neonews-membership
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NEONEWS_MEMBERSHIP_VERSION', '2.0.0' );
define( 'NEONEWS_MEMBERSHIP_FILE', __FILE__ );
define( 'NEONEWS_MEMBERSHIP_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEONEWS_MEMBERSHIP_URL', plugin_dir_url( __FILE__ ) );

require_once NEONEWS_MEMBERSHIP_DIR . 'inc/membership-settings.php';

/**
 * Main Plugin Class
 */
final class NeoNews_Membership {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    private function includes() {
        require_once NEONEWS_MEMBERSHIP_DIR . 'inc/class-activation-keys.php';
        require_once NEONEWS_MEMBERSHIP_DIR . 'inc/membership-icons.php';
        require_once NEONEWS_MEMBERSHIP_DIR . 'inc/class-premium-content.php';
        require_once NEONEWS_MEMBERSHIP_DIR . 'inc/class-membership-levels.php';
        require_once NEONEWS_MEMBERSHIP_DIR . 'inc/class-membership-display.php';
        require_once NEONEWS_MEMBERSHIP_DIR . 'inc/class-content-restriction.php';
        require_once NEONEWS_MEMBERSHIP_DIR . 'inc/class-membership-frontend.php';
        require_once NEONEWS_MEMBERSHIP_DIR . 'inc/class-membership-ads.php';

        if ( is_admin() ) {
            require_once NEONEWS_MEMBERSHIP_DIR . 'admin/class-membership-admin.php';
        }
    }

    private function init_hooks() {
        register_activation_hook( NEONEWS_MEMBERSHIP_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( NEONEWS_MEMBERSHIP_FILE, array( $this, 'deactivate' ) );

        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'plugins_loaded', array( $this, 'maybe_upgrade' ), 5 );
    }

    /**
     * Run DB/settings upgrades without reactivation.
     */
    public function maybe_upgrade() {
        $stored = get_option( 'neonews_membership_version', '0' );
        if ( version_compare( $stored, NEONEWS_MEMBERSHIP_VERSION, '>=' ) ) {
            return;
        }

        NeoNews_Activation_Keys::create_table();
        $existing = get_option( 'neonews_membership_settings', array() );
        update_option( 'neonews_membership_settings', wp_parse_args( $existing, neonews_membership_default_settings() ) );
        update_option( 'neonews_membership_version', NEONEWS_MEMBERSHIP_VERSION );
        neonews_membership_ensure_exclusive_page();
        if ( function_exists( 'neonews_sync_primary_nav_menus' ) ) {
            neonews_sync_primary_nav_menus();
        }
    }

    public function activate() {
        NeoNews_Membership_Levels::activate();
        NeoNews_Activation_Keys::create_table();
        $existing = get_option( 'neonews_membership_settings', array() );
        update_option( 'neonews_membership_settings', wp_parse_args( $existing, neonews_membership_default_settings() ) );
        update_option( 'neonews_membership_version', NEONEWS_MEMBERSHIP_VERSION );
        neonews_membership_ensure_exclusive_page();
        if ( function_exists( 'neonews_sync_primary_nav_menus' ) ) {
            neonews_sync_primary_nav_menus();
        }
        flush_rewrite_rules();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'neonews-membership',
            false,
            dirname( plugin_basename( NEONEWS_MEMBERSHIP_FILE ) ) . '/languages'
        );
    }

    public function init() {
        NeoNews_Premium_Content::get_instance();
        NeoNews_Membership_Levels::get_instance();
        NeoNews_Content_Restriction::get_instance();
        NeoNews_Membership_Frontend::get_instance();
        NeoNews_Membership_Ads::get_instance();

        if ( is_admin() ) {
            NeoNews_Membership_Admin::get_instance();
        }
    }

    public function enqueue_scripts() {
        if ( function_exists( 'neonews_needs_membership_assets' ) && ! neonews_needs_membership_assets() ) {
            return;
        }

        wp_enqueue_style(
            'neonews-membership',
            NEONEWS_MEMBERSHIP_URL . 'public/css/membership.css',
            array(),
            NEONEWS_MEMBERSHIP_VERSION
        );
    }

    public static function get_settings( $key = null ) {
        $settings = get_option( 'neonews_membership_settings', array() );
        $settings = wp_parse_args( $settings, neonews_membership_default_settings() );

        if ( $key ) {
            return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
        }

        return $settings;
    }
}

function neonews_is_user_premium( $user_id = null ) {
    return NeoNews_Membership_Levels::is_premium( $user_id );
}

function neonews_membership() {
    return NeoNews_Membership::get_instance();
}

neonews_membership();
