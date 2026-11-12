<?php
/**
 * Plugin Name: NeoNews Role Manager
 * Description: Control which WordPress roles can access each NeoNews admin page and core WP capabilities. No third-party dependency.
 * Version: 1.0.2
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: NeoNews Team
 * License: GPL v2 or later
 * Text Domain: neonews-roles
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NN_ROLES_VERSION', '1.0.2' );
define( 'NN_ROLES_FILE',    __FILE__ );
define( 'NN_ROLES_DIR',     plugin_dir_path( __FILE__ ) );

require_once NN_ROLES_DIR . 'inc/class-roles-manager.php';
require_once NN_ROLES_DIR . 'inc/class-roles-admin.php';
require_once NN_ROLES_DIR . 'inc/class-roles-enforcer.php';

register_activation_hook( NN_ROLES_FILE, array( 'NeoNews_Roles_Manager', 'activate' ) );

add_action( 'plugins_loaded', function () {
    NeoNews_Roles_Admin::init();
    NeoNews_Roles_Enforcer::init();
} );
