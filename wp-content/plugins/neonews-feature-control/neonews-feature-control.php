<?php
/**
 * Plugin Name: NeoNews Feature Control
 * Description: One simple dashboard to turn site features on or off — weather, search, accounts, dark mode, homepage blocks, ads, and more.
 * Version: 1.0.3
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: NeoNews Team
 * License: GPL v2 or later
 * Text Domain: neonews-feature-control
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NEONEWS_FC_VERSION', '1.0.3' );
define( 'NEONEWS_FC_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEONEWS_FC_URL', plugin_dir_url( __FILE__ ) );

require_once NEONEWS_FC_DIR . 'includes/class-feature-overrides.php';
require_once NEONEWS_FC_DIR . 'includes/class-feature-store.php';
require_once NEONEWS_FC_DIR . 'includes/class-feature-registry.php';
require_once NEONEWS_FC_DIR . 'includes/class-admin-dashboard.php';

/**
 * Bootstrap Feature Control.
 */
final class NeoNews_Feature_Control {

	/** @var self|null */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ( is_admin() ) {
			NeoNews_FC_Admin_Dashboard::instance();
		}
	}
}

add_action( 'plugins_loaded', array( 'NeoNews_Feature_Control', 'instance' ) );
