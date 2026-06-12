<?php
/**
 * Default settings for SEO Lite.
 *
 * @package NeoNews_SEO_Lite
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @return array<string,mixed>
 */
function neonews_seo_lite_defaults() {
    return array(
        'enabled'            => true,
        'replace_theme_seo'  => true,
        'home_description'   => '',
        'title_separator'    => '|',
        'default_og_image'   => '',
        'twitter_site'       => '',
        'noindex_search'     => true,
        'noindex_author'     => false,
        'enable_schema'            => true,
        'enable_breadcrumb_schema' => true,
        'org_name'                 => '',
        'enable_breadcrumbs'       => true,
        'breadcrumb_home_label'    => '',
        'sitemap_posts'            => true,
        'sitemap_pages'            => true,
        'sitemap_categories'       => true,
        'sitemap_tags'             => false,
        'sitemap_authors'          => false,
        'sitemap_exclude_noindex'  => true,
    );
}

/**
 * @param mixed $value Raw option.
 * @return array<string,mixed>
 */
function neonews_seo_lite_normalize( $value ) {
    if ( ! is_array( $value ) ) {
        return array();
    }

    if ( strlen( maybe_serialize( $value ) ) > 50000 ) {
        return array();
    }

    return $value;
}

/**
 * @param array<string,mixed> $settings Settings.
 * @return bool
 */
function neonews_seo_lite_save( $settings ) {
    return update_option( NEONEWS_SEO_LITE_OPTION, $settings, false );
}

/**
 * Turn off the full NeoNews SEO plugin so Lite can run.
 *
 * @return bool Whether full SEO was disabled.
 */
function neonews_seo_lite_pause_full_seo() {
    $settings = get_option( 'neonews_seo_settings', array() );
    if ( ! is_array( $settings ) || empty( $settings['enabled'] ) ) {
        return false;
    }

    $settings['enabled'] = false;
    update_option( 'neonews_seo_settings', $settings, false );

    return true;
}
