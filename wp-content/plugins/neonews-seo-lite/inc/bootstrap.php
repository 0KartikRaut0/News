<?php
/**
 * SEO Lite bootstrap.
 *
 * @package NeoNews_SEO_Lite
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once NEONEWS_SEO_LITE_DIR . 'inc/defaults.php';

/**
 * Lightweight SEO plugin.
 */
final class NeoNews_SEO_Lite {

    /** @var self|null */
    private static $instance = null;

    /** @var array<string,mixed>|null */
    private static $settings = null;

    /**
     * @return self
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'sync_with_full_seo' ), 7 );
        add_action( 'admin_notices', array( $this, 'admin_conflict_notice' ) );

        if ( is_admin() ) {
            add_action( 'admin_init', array( $this, 'boot_admin' ), 9 );
            return;
        }

        add_action( 'wp', array( $this, 'boot_frontend' ), 1 );
    }

    /**
     * @return void
     */
    public function boot_admin() {
        require_once NEONEWS_SEO_LITE_DIR . 'admin/settings.php';
        NeoNews_SEO_Lite_Admin::instance();

        $page = isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '';
        if ( in_array( $page, array( 'post.php', 'post-new.php' ), true ) ) {
            require_once NEONEWS_SEO_LITE_DIR . 'admin/post-meta.php';
            NeoNews_SEO_Lite_Post_Meta::instance();
        }
    }

    /**
     * @return void
     */
    public function boot_frontend() {
        if ( ! self::is_enabled() ) {
            return;
        }

        require_once NEONEWS_SEO_LITE_DIR . 'inc/frontend.php';
        require_once NEONEWS_SEO_LITE_DIR . 'inc/schema.php';
        require_once NEONEWS_SEO_LITE_DIR . 'inc/breadcrumbs.php';
        require_once NEONEWS_SEO_LITE_DIR . 'inc/sitemap.php';

        neonews_seo_lite_register_frontend_hooks();
        neonews_seo_lite_register_schema_hooks();
        neonews_seo_lite_register_breadcrumb_hooks();
        neonews_seo_lite_register_sitemap_hooks();

        $this->disable_competing_seo_output();
    }

    /**
     * @return void
     */
    public function disable_competing_seo_output() {
        if ( ! self::get( 'replace_theme_seo' ) ) {
            return;
        }

        remove_action( 'wp_head', 'neonews_meta_description', 1 );
        remove_action( 'wp_head', 'neonews_open_graph_meta', 5 );
        remove_action( 'wp_head', 'neonews_schema_markup' );
    }

    /**
     * Keep full SEO turned off in the database when Lite is active.
     *
     * @return void
     */
    public function sync_with_full_seo() {
        if ( ! self::is_enabled() ) {
            return;
        }

        neonews_seo_lite_pause_full_seo();
    }

    /**
     * @return void
     */
    public function admin_conflict_notice() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( ! self::is_enabled() || ! class_exists( 'NeoNews_SEO', false ) ) {
            return;
        }

        if ( NeoNews_SEO::is_enabled() ) {
            echo '<div class="notice notice-warning"><p>';
            esc_html_e( 'NeoNews SEO Lite is active but NeoNews SEO (full) is also enabled in its settings. Save SEO Lite settings once to sync, or disable the full plugin.', 'neonews-seo-lite' );
            echo '</p></div>';
            return;
        }

        echo '<div class="notice notice-info"><p>';
        esc_html_e( 'NeoNews SEO Lite is active. You can deactivate the full NeoNews SEO plugin to reduce overhead.', 'neonews-seo-lite' );
        echo '</p></div>';
    }

    /**
     * @return bool
     */
    public static function is_enabled() {
        return ! empty( self::settings()['enabled'] );
    }

    /**
     * @param string|null $key Setting key.
     * @return mixed
     */
    public static function get( $key = null ) {
        $settings = self::settings();

        if ( null === $key ) {
            return $settings;
        }

        return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
    }

    /**
     * @return array<string,mixed>
     */
    public static function settings() {
        if ( null === self::$settings ) {
            self::$settings = wp_parse_args(
                neonews_seo_lite_normalize( get_option( NEONEWS_SEO_LITE_OPTION, array() ) ),
                neonews_seo_lite_defaults()
            );
        }

        return self::$settings;
    }

    /**
     * Clear cached settings after save.
     *
     * @return void
     */
    public static function flush_settings_cache() {
        self::$settings = null;
    }
}

NeoNews_SEO_Lite::instance();
