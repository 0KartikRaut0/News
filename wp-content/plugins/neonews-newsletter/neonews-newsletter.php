<?php
/**
 * Plugin Name: NeoNews Newsletter
 * Description: In-house subscriber collection with double opt-in, admin dashboard, and CSV export. No third-party dependency.
 * Version: 1.0.0
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: NeoNews Team
 * License: GPL v2 or later
 * Text Domain: neonews-newsletter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NN_NEWS_VERSION', '1.0.0' );
define( 'NN_NEWS_FILE',    __FILE__ );
define( 'NN_NEWS_DIR',     plugin_dir_path( __FILE__ ) );
define( 'NN_NEWS_URL',     plugin_dir_url( __FILE__ ) );

require_once NN_NEWS_DIR . 'inc/class-db.php';
require_once NN_NEWS_DIR . 'inc/class-mailer.php';
require_once NN_NEWS_DIR . 'inc/class-ajax.php';
require_once NN_NEWS_DIR . 'admin/class-admin.php';

register_activation_hook( NN_NEWS_FILE, array( 'NeoNews_Newsletter_DB', 'install' ) );

add_action( 'plugins_loaded', function () {
    NeoNews_Newsletter_Ajax::init();
    NeoNews_Newsletter_Admin::init();

    // Override theme newsletter form action to point to our AJAX handler.
    add_filter( 'neonews_newsletter_form_action', function () {
        return admin_url( 'admin-ajax.php' );
    } );
} );
