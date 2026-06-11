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

        if ( self::get( 'replace_theme_seo' ) ) {
            remove_action( 'wp_head', 'neonews_meta_description', 1 );
            remove_action( 'wp_head', 'neonews_open_graph_meta', 5 );
            remove_action( 'wp_head', 'neonews_schema_markup' );
        }
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
