<?php
/**
 * Plugin Name: NeoNews SEO
 * Plugin URI: https://example.com/neonews
 * Description: Complete on-site SEO — meta tags, Open Graph, Twitter Cards, schema.org, sitemaps, robots, breadcrumbs, and per-post controls. No external services.
 * Version: 1.0.2
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: NeoNews Team
 * License: GPL v2 or later
 * Text Domain: neonews-seo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NEONEWS_SEO_VERSION', '1.0.2' );
define( 'NEONEWS_SEO_FILE', __FILE__ );
define( 'NEONEWS_SEO_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEONEWS_SEO_URL', plugin_dir_url( __FILE__ ) );

/**
 * Activation must stay tiny — avoid loading the full plugin stack here.
 */
function neonews_seo_activate_plugin() {
    require_once NEONEWS_SEO_DIR . 'inc/activate.php';
}
register_activation_hook( NEONEWS_SEO_FILE, 'neonews_seo_activate_plugin' );

/**
 * Full plugin loads after WordPress core and other plugins bootstrap.
 */
add_action(
    'plugins_loaded',
    static function () {
        require_once NEONEWS_SEO_DIR . 'inc/bootstrap.php';
    },
    1
);
