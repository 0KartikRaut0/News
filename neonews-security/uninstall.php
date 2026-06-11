<?php
/**
 * Uninstall NeoNews Security.
 *
 * @package NeoNews_Security
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

$table = $wpdb->prefix . 'neonews_security_log';
$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

delete_option( 'neonews_security_settings' );
delete_option( 'neonews_security_enc_key' );
delete_option( 'neonews_security_last_scan' );
delete_option( 'neonews_security_db_version' );

$mu_file = WPMU_PLUGIN_DIR . '/neonews-security-hardening.php';
if ( file_exists( $mu_file ) ) {
    unlink( $mu_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
}
