<?php
/**
 * NeoNews SEO bootstrap (loaded after plugins_loaded).
 *
 * @package NeoNews_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Raise PHP memory when the host allows it.
 */
function neonews_seo_ensure_memory_limit() {
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
neonews_seo_ensure_memory_limit();

require_once NEONEWS_SEO_DIR . 'inc/seo-settings.php';
require_once NEONEWS_SEO_DIR . 'inc/class-seo-meta.php';

/**
 * NeoNews SEO bootstrap.
 */
final class NeoNews_SEO {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init_plugin' ), 8 );
        add_action( 'admin_init', array( __CLASS__, 'maybe_migrate_storage' ), 1 );
    }

    /**
     * Load admin or frontend modules only when needed.
     */
    public function init_plugin() {
        if ( is_admin() ) {
            add_action( 'admin_init', array( $this, 'init_admin' ), 8 );
            return;
        }

        $this->init_frontend();
    }

    /**
     * Admin: settings page always; meta boxes only on relevant screens.
     */
    public function init_admin() {
        require_once NEONEWS_SEO_DIR . 'admin/class-seo-admin.php';
        NeoNews_SEO_Admin::get_instance();

        $pagenow = isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '';

        if ( in_array( $pagenow, array( 'post.php', 'post-new.php', 'edit.php' ), true ) ) {
            require_once NEONEWS_SEO_DIR . 'admin/class-seo-post-meta.php';
            NeoNews_SEO_Post_Meta::get_instance();
        }

        if ( in_array( $pagenow, array( 'edit-tags.php', 'term.php' ), true ) ) {
            require_once NEONEWS_SEO_DIR . 'admin/class-seo-term-meta.php';
            NeoNews_SEO_Term_Meta::get_instance();
        }
    }

    /**
     * Frontend SEO output modules.
     */
    private function init_frontend() {
        if ( class_exists( 'NeoNews_SEO_Lite', false ) && NeoNews_SEO_Lite::is_enabled() ) {
            return;
        }

        require_once NEONEWS_SEO_DIR . 'inc/class-seo-head.php';
        require_once NEONEWS_SEO_DIR . 'inc/class-seo-schema.php';
        require_once NEONEWS_SEO_DIR . 'inc/class-seo-sitemap.php';
        require_once NEONEWS_SEO_DIR . 'inc/class-seo-robots.php';
        require_once NEONEWS_SEO_DIR . 'inc/class-seo-breadcrumbs.php';

        NeoNews_SEO_Head::get_instance();
        NeoNews_SEO_Schema::get_instance();
        NeoNews_SEO_Sitemap::get_instance();
        NeoNews_SEO_Robots::get_instance();
        NeoNews_SEO_Breadcrumbs::get_instance();

        add_action( 'init', array( $this, 'disable_theme_seo' ), 20 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public' ), 40 );
    }

    /**
     * One-time: move settings off autoload for existing sites.
     */
    public static function maybe_migrate_storage() {
        if ( '1.0.2' === get_option( 'neonews_seo_db_version' ) ) {
            return;
        }

        $stored = neonews_seo_normalize_settings( get_option( 'neonews_seo_settings', array() ) );
        neonews_seo_save_settings(
            wp_parse_args( $stored, neonews_seo_default_settings() )
        );
        neonews_seo_ensure_settings_not_autoloaded();
        update_option( 'neonews_seo_db_version', '1.0.2', false );
    }

    /**
     * Remove legacy theme SEO when plugin is active.
     */
    public function disable_theme_seo() {
        if ( ! self::is_enabled() || ! self::get_settings( 'replace_theme_seo' ) ) {
            return;
        }

        remove_action( 'wp_head', 'neonews_meta_description', 1 );
        remove_action( 'wp_head', 'neonews_open_graph_meta', 5 );
        remove_action( 'wp_head', 'neonews_schema_markup' );
    }

    public function enqueue_public() {
        if ( ! self::get_settings( 'enable_breadcrumbs' ) ) {
            return;
        }
        wp_enqueue_style( 'neonews-seo', NEONEWS_SEO_URL . 'public/css/seo.css', array(), NEONEWS_SEO_VERSION );
    }

    /**
     * @param string|null $key Setting key.
     * @return mixed
     */
    public static function get_settings( $key = null ) {
        static $settings = null;

        if ( null === $settings ) {
            $settings = wp_parse_args(
                neonews_seo_normalize_settings( get_option( 'neonews_seo_settings', array() ) ),
                neonews_seo_default_settings()
            );
        }

        if ( null !== $key ) {
            return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
        }
        return $settings;
    }

    /**
     * @return bool
     */
    public static function is_enabled() {
        return ! empty( self::get_settings( 'enabled' ) );
    }

    /**
     * @return bool
     */
    public static function third_party_active() {
        return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' );
    }

    /**
     * @return bool
     */
    public static function should_output() {
        if ( ! self::is_enabled() || is_admin() ) {
            return false;
        }

        if ( self::third_party_active() && ! empty( self::get_settings( 'defer_third_party' ) ) ) {
            return false;
        }

        return true;
    }
}

NeoNews_SEO::get_instance();
