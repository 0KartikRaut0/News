<?php
/**
 * Plugin Name: NeoNews SEO Lite
 * Plugin URI: https://example.com/neonews
 * Description: Lightweight SEO for NeoNews — meta tags, social previews, schema, and per-post controls. Built for low memory use.
 * Version: 1.1.0
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: NeoNews Team
 * License: GPL v2 or later
 * Text Domain: neonews-seo-lite
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NEONEWS_SEO_LITE_VERSION', '1.1.0' );
define( 'NEONEWS_SEO_LITE_FILE', __FILE__ );
define( 'NEONEWS_SEO_LITE_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEONEWS_SEO_LITE_URL', plugin_dir_url( __FILE__ ) );
define( 'NEONEWS_SEO_LITE_OPTION', 'neonews_seo_lite_settings' );

/**
 * @return void
 */
function neonews_seo_lite_activate() {
    if ( false === get_option( NEONEWS_SEO_LITE_OPTION, false ) ) {
        require_once NEONEWS_SEO_LITE_DIR . 'inc/defaults.php';
        add_option( NEONEWS_SEO_LITE_OPTION, neonews_seo_lite_defaults(), '', 'no' );
    }
}
register_activation_hook( NEONEWS_SEO_LITE_FILE, 'neonews_seo_lite_activate' );

add_action(
    'plugins_loaded',
    static function () {
        require_once NEONEWS_SEO_LITE_DIR . 'inc/bootstrap.php';
    },
    2
);
