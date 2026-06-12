<?php
/**
 * Plugin Name: NeoNews Core
 * Plugin URI: https://example.com/neonews
 * Description: Core functionality for NeoNews theme including user roles, news submission, activity tracking, and view counter.
 * Version: 1.2.4
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: NeoNews Team
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: neonews-core
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NEONEWS_CORE_VERSION', '1.2.4' );
define( 'NEONEWS_CORE_FILE', __FILE__ );
define( 'NEONEWS_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEONEWS_CORE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Bump memory when the host allows it (Local WP defaults to 256M).
 */
function neonews_core_ensure_memory_limit() {
    $raw = ini_get( 'memory_limit' );
    if ( ! $raw || '-1' === $raw ) {
        return;
    }

    $bytes = function_exists( 'wp_convert_hr_to_bytes' )
        ? (int) wp_convert_hr_to_bytes( $raw )
        : (int) $raw;

    if ( $bytes > 0 && $bytes < 536870912 ) {
        if ( function_exists( 'wp_raise_memory_limit' ) ) {
            wp_raise_memory_limit( 'admin' );
        }
        @ini_set( 'memory_limit', '512M' );
    }
}
neonews_core_ensure_memory_limit();

/**
 * Main Plugin Class
 */
final class NeoNews_Core {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once NEONEWS_CORE_DIR . 'inc/class-user-roles.php';
        require_once NEONEWS_CORE_DIR . 'inc/class-news-submission.php';
        require_once NEONEWS_CORE_DIR . 'inc/class-activity-tracker.php';
        require_once NEONEWS_CORE_DIR . 'inc/class-user-activity-log.php';
        require_once NEONEWS_CORE_DIR . 'inc/class-activity-anomaly-detector.php';
        require_once NEONEWS_CORE_DIR . 'inc/class-activity-rest-api.php';
        require_once NEONEWS_CORE_DIR . 'inc/class-captcha.php';
        require_once NEONEWS_CORE_DIR . 'inc/class-view-counter.php';
        require_once NEONEWS_CORE_DIR . 'inc/class-breaking-news.php';
        require_once NEONEWS_CORE_DIR . 'inc/class-onesignal-integration.php';

        if ( is_admin() ) {
            require_once NEONEWS_CORE_DIR . 'admin/class-admin-settings.php';
            require_once NEONEWS_CORE_DIR . 'admin/class-post-meta.php';
            require_once NEONEWS_CORE_DIR . 'admin/class-activity-log-admin.php';
            require_once NEONEWS_CORE_DIR . 'admin/class-activity-dashboard-widget.php';
        }
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook( NEONEWS_CORE_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( NEONEWS_CORE_FILE, array( $this, 'deactivate' ) );

        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    }

    /**
     * Plugin activation
     */
    public function activate() {
        NeoNews_User_Roles::activate();
        NeoNews_User_Activity_Log::create_table();
        
        if ( ! get_option( 'neonews_core_version' ) ) {
            add_option( 'neonews_core_version', NEONEWS_CORE_VERSION );
            add_option( 'neonews_core_settings', array(
                'enable_submission'          => true,
                'enable_view_counter'        => true,
                'enable_activity_tracking'   => true,
                'enable_activity_alerts'     => false,
                'activity_alert_email'       => get_option( 'admin_email' ),
                'activity_alert_admin_login' => false,
                'enable_activity_webhook'    => false,
                'activity_webhook_type'      => 'generic',
                'activity_webhook_url'       => '',
                'onesignal_app_id'           => '',
                'onesignal_rest_key'         => '',
            ) );
        }

        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Load textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'neonews-core',
            false,
            dirname( plugin_basename( NEONEWS_CORE_FILE ) ) . '/languages'
        );
    }

    /**
     * Initialize plugin
     */
    public function init() {
        NeoNews_User_Roles::get_instance();
        NeoNews_News_Submission::get_instance();
        NeoNews_Activity_Tracker::get_instance();
        NeoNews_User_Activity_Log::get_instance();
        NeoNews_Activity_Anomaly_Detector::get_instance();
        NeoNews_Activity_Rest_Api::get_instance();
        NeoNews_Captcha::get_instance();
        NeoNews_View_Counter::get_instance();
        NeoNews_Breaking_News::get_instance();
        NeoNews_OneSignal_Integration::get_instance();

        if ( is_admin() ) {
            NeoNews_Admin_Settings::get_instance();
            NeoNews_Post_Meta::get_instance();
            NeoNews_Activity_Log_Admin::get_instance();
            NeoNews_Activity_Dashboard_Widget::get_instance();
        }
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts() {
        if ( function_exists( 'neonews_needs_core_frontend_assets' ) && ! neonews_needs_core_frontend_assets() ) {
            return;
        }

        $needs_js = is_singular( 'post' ) || ( is_page() && $this->page_has_submission_form() );

        wp_enqueue_style(
            'neonews-core-public',
            NEONEWS_CORE_URL . 'public/css/public.css',
            array(),
            NEONEWS_CORE_VERSION
        );

        if ( ! $needs_js ) {
            return;
        }

        wp_enqueue_script(
            'neonews-core-public',
            NEONEWS_CORE_URL . 'public/js/public.js',
            array(),
            NEONEWS_CORE_VERSION,
            true
        );

        wp_localize_script( 'neonews-core-public', 'neonewsCoreData', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'neonews_core_nonce' ),
            'i18n'    => array(
                'submitting' => esc_html__( 'Submitting...', 'neonews-core' ),
                'success'    => esc_html__( 'Article submitted successfully!', 'neonews-core' ),
                'error'      => esc_html__( 'An error occurred. Please try again.', 'neonews-core' ),
            ),
        ) );
    }

    /**
     * Does the current page contain a submission shortcode?
     *
     * @return bool
     */
    private function page_has_submission_form() {
        if ( ! is_page() ) {
            return false;
        }
        $post = get_post();
        return $post && has_shortcode( $post->post_content, 'neonews_submit_form' );
    }

    /**
     * Enqueue admin scripts
     */
    public function admin_enqueue_scripts( $hook ) {
        $allowed_pages = array( 'toplevel_page_neonews-settings', 'neonews_page_neonews-core-settings', 'post.php', 'post-new.php' );
        
        if ( ! in_array( $hook, $allowed_pages, true ) ) {
            return;
        }

        wp_enqueue_style(
            'neonews-core-admin',
            NEONEWS_CORE_URL . 'admin/css/admin.css',
            array(),
            NEONEWS_CORE_VERSION
        );

        wp_enqueue_script(
            'neonews-core-admin',
            NEONEWS_CORE_URL . 'admin/js/admin.js',
            array( 'jquery' ),
            NEONEWS_CORE_VERSION,
            true
        );
    }

    /**
     * Get plugin settings
     */
    public static function get_settings( $key = null ) {
        $settings = get_option( 'neonews_core_settings', array() );
        
        $defaults = array(
            'enable_submission'        => true,
            'enable_view_counter'      => true,
            'enable_activity_tracking' => true,
            'enable_activity_alerts'   => false,
            'activity_alert_email'     => get_option( 'admin_email' ),
            'activity_alert_admin_login' => false,
            'enable_activity_webhook'    => false,
            'activity_webhook_type'      => 'generic',
            'activity_webhook_url'       => '',
            'onesignal_app_id'         => '',
            'onesignal_rest_key'       => '',
        );

        $settings = wp_parse_args( $settings, $defaults );

        if ( $key ) {
            return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
        }

        return $settings;
    }
}

/**
 * Initialize plugin
 */
function neonews_core() {
    return NeoNews_Core::get_instance();
}

neonews_core();
