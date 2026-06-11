<?php
/**
 * Plugin Name: NeoNews Smart Tools
 * Plugin URI: https://example.com/neonews
 * Description: Server-local editorial and reader tools — auto excerpts, story glance, TOC, smart related, recommendations, submission checks, and weekly digest. No external AI.
 * Version: 1.0.0
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: NeoNews Team
 * License: GPL v2 or later
 * Text Domain: neonews-smart
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NEONEWS_SMART_VERSION', '1.0.0' );
define( 'NEONEWS_SMART_FILE', __FILE__ );
define( 'NEONEWS_SMART_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEONEWS_SMART_URL', plugin_dir_url( __FILE__ ) );

/**
 * Raise PHP memory when the host allows it (Local WP defaults to 256M).
 */
function neonews_smart_ensure_memory_limit() {
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
neonews_smart_ensure_memory_limit();

require_once NEONEWS_SMART_DIR . 'inc/smart-settings.php';
require_once NEONEWS_SMART_DIR . 'inc/class-smart-helpers.php';

/**
 * Bootstrap Smart Tools plugin.
 */
final class NeoNews_Smart {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        require_once NEONEWS_SMART_DIR . 'inc/class-smart-excerpt.php';
        require_once NEONEWS_SMART_DIR . 'inc/class-smart-submission.php';
        require_once NEONEWS_SMART_DIR . 'inc/class-smart-digest.php';

        if ( is_admin() ) {
            require_once NEONEWS_SMART_DIR . 'admin/class-smart-admin.php';
            NeoNews_Smart_Admin::get_instance();
        } else {
            require_once NEONEWS_SMART_DIR . 'inc/class-smart-reader.php';
            require_once NEONEWS_SMART_DIR . 'inc/class-smart-related.php';
            require_once NEONEWS_SMART_DIR . 'inc/class-smart-personalize.php';

            NeoNews_Smart_Reader::get_instance();
            NeoNews_Smart_Related::get_instance();
            NeoNews_Smart_Personalize::get_instance();

            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 36 );
        }

        NeoNews_Smart_Excerpt::get_instance();
        NeoNews_Smart_Submission::get_instance();
        NeoNews_Smart_Digest::get_instance();

        add_action( 'updated_option', array( __CLASS__, 'keep_settings_off_autoload' ), 10, 1 );
        add_action( 'init', array( __CLASS__, 'maybe_migrate_storage' ), 1 );
    }

    public static function activate() {
        neonews_smart_save_settings(
            wp_parse_args( get_option( 'neonews_smart_settings', array() ), neonews_smart_default_settings() )
        );
        neonews_smart_ensure_settings_not_autoloaded();
        update_option( 'neonews_smart_db_version', '1.0.1', false );
        require_once NEONEWS_SMART_DIR . 'inc/class-smart-digest.php';
        NeoNews_Smart_Digest::schedule_cron();
    }

    /**
     * One-time: move settings off autoload for existing sites.
     */
    public static function maybe_migrate_storage() {
        if ( '1.0.1' === get_option( 'neonews_smart_db_version' ) ) {
            return;
        }
        neonews_smart_ensure_settings_not_autoloaded();
        update_option( 'neonews_smart_db_version', '1.0.1', false );
    }

    /**
     * Keep plugin settings out of wp_load_alloptions (reduces admin memory use).
     *
     * @param string $option Option name.
     */
    public static function keep_settings_off_autoload( $option ) {
        if ( 'neonews_smart_settings' !== $option ) {
            return;
        }
        neonews_smart_ensure_settings_not_autoloaded();
    }

    public static function deactivate() {
        require_once NEONEWS_SMART_DIR . 'inc/class-smart-digest.php';
        NeoNews_Smart_Digest::unschedule_cron();
    }

    public function enqueue() {
        if ( ! self::needs_assets() ) {
            return;
        }
        wp_enqueue_style( 'neonews-smart', NEONEWS_SMART_URL . 'public/css/smart.css', array(), NEONEWS_SMART_VERSION );
    }

    public static function needs_assets() {
        return is_singular( 'post' ) || is_front_page();
    }

    public static function get_settings( $key = null ) {
        static $settings = null;

        if ( null === $settings ) {
            $settings = wp_parse_args(
                get_option( 'neonews_smart_settings', array() ),
                neonews_smart_default_settings()
            );
        }

        if ( null !== $key ) {
            return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
        }
        return $settings;
    }

    public static function is_enabled( $feature ) {
        $map = array(
            'auto_excerpt'   => 'enable_auto_excerpt',
            'story_glance'   => 'enable_story_glance',
            'key_points'     => 'enable_key_points',
            'toc'            => 'enable_toc',
            'smart_related'  => 'enable_smart_related',
            'personalized'   => 'enable_personalized_home',
            'submission'     => 'enable_submission_checks',
            'digest'         => 'enable_weekly_digest',
        );
        if ( ! isset( $map[ $feature ] ) ) {
            return false;
        }
        return ! empty( self::get_settings( $map[ $feature ] ) );
    }
}

register_activation_hook( NEONEWS_SMART_FILE, array( 'NeoNews_Smart', 'activate' ) );
register_deactivation_hook( NEONEWS_SMART_FILE, array( 'NeoNews_Smart', 'deactivate' ) );

NeoNews_Smart::get_instance();
