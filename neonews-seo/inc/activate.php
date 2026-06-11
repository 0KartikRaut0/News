<?php
/**
 * Minimal SEO plugin activation (no heavy bootstrap).
 *
 * @package NeoNews_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once NEONEWS_SEO_DIR . 'inc/seo-settings.php';

$stored = neonews_seo_normalize_settings( get_option( 'neonews_seo_settings', array() ) );

if ( empty( $stored ) ) {
    add_option( 'neonews_seo_settings', neonews_seo_default_settings(), '', 'no' );
} else {
    neonews_seo_save_settings(
        wp_parse_args( $stored, neonews_seo_default_settings() )
    );
}

neonews_seo_ensure_settings_not_autoloaded();
update_option( 'neonews_seo_db_version', '1.0.2', false );
