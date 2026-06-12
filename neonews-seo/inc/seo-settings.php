<?php
/**
 * SEO plugin default settings.
 *
 * @package NeoNews_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load SEO context class only when needed (saves memory on admin screens).
 */
function neonews_seo_load_context() {
    if ( ! class_exists( 'NeoNews_SEO_Context', false ) ) {
        require_once NEONEWS_SEO_DIR . 'inc/class-seo-context.php';
    }
}

/**
 * Normalize stored settings and drop corrupt/oversized values.
 *
 * @param mixed $settings Raw option value.
 * @return array
 */
function neonews_seo_normalize_settings( $settings ) {
    if ( ! is_array( $settings ) ) {
        return array();
    }

    $serialized = maybe_serialize( $settings );
    if ( strlen( $serialized ) > 100000 ) {
        return array();
    }

    return $settings;
}

/**
 * Persist SEO settings without autoloading into every request.
 *
 * @param array $settings Settings.
 * @return bool
 */
function neonews_seo_save_settings( $settings ) {
    return update_option( 'neonews_seo_settings', $settings, false );
}

/**
 * Ensure SEO settings are not in wp_load_alloptions.
 */
function neonews_seo_ensure_settings_not_autoloaded() {
    global $wpdb;

    $autoload = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT autoload FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
            'neonews_seo_settings'
        )
    );

    if ( ! $autoload || 'no' === $autoload ) {
        return;
    }

    $wpdb->update(
        $wpdb->options,
        array( 'autoload' => 'no' ),
        array( 'option_name' => 'neonews_seo_settings' ),
        array( '%s' ),
        array( '%s' )
    );

    wp_cache_delete( 'neonews_seo_settings', 'options' );
}

/**
 * Default SEO settings.
 *
 * @return array
 */
function neonews_seo_default_settings() {
    return array(
        'enabled'                   => true,
        'replace_theme_seo'         => true,
        'defer_third_party'         => true,
        'home_title'                => '',
        'home_description'          => '',
        'title_separator'           => '|',
        'org_name'                  => '',
        'org_logo'                  => 0,
        'default_og_image'          => 0,
        'twitter_site'              => '',
        'twitter_card'              => 'summary_large_image',
        'enable_meta_description'   => true,
        'enable_canonical'          => true,
        'enable_open_graph'         => true,
        'enable_twitter_cards'      => true,
        'enable_schema'             => true,
        'enable_website_schema'     => true,
        'enable_organization_schema'=> true,
        'enable_breadcrumbs'        => true,
        'enable_breadcrumb_schema'  => true,
        'breadcrumb_home_label'     => '',
        'noindex_search'            => true,
        'noindex_author'            => false,
        'noindex_date'              => true,
        'noindex_paged'             => false,
        'noindex_tags'              => false,
        'noindex_categories'        => false,
        'noindex_account_pages'     => true,
        'sitemap_posts'             => true,
        'sitemap_pages'             => true,
        'sitemap_categories'        => true,
        'sitemap_tags'              => false,
        'sitemap_authors'           => false,
        'sitemap_exclude_noindex'   => true,
        'robots_txt_extra'          => '',
        'article_section_category'  => true,
        'premium_paywall_schema'    => true,
    );
}
