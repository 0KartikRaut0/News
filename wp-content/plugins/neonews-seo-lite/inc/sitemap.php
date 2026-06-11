<?php
/**
 * WordPress core sitemap tuning.
 *
 * @package NeoNews_SEO_Lite
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @return void
 */
function neonews_seo_lite_register_sitemap_hooks() {
    add_filter( 'wp_sitemaps_post_types', 'neonews_seo_lite_filter_sitemap_post_types' );
    add_filter( 'wp_sitemaps_taxonomies', 'neonews_seo_lite_filter_sitemap_taxonomies' );
    add_filter( 'wp_sitemaps_users', 'neonews_seo_lite_filter_sitemap_users' );
    add_filter( 'wp_sitemaps_posts_query_args', 'neonews_seo_lite_filter_sitemap_posts', 10, 2 );
    add_filter( 'robots_txt', 'neonews_seo_lite_append_sitemap_to_robots', 20, 2 );
}

/**
 * @param array<string,WP_Post_Type> $post_types Post types.
 * @return array<string,WP_Post_Type>
 */
function neonews_seo_lite_filter_sitemap_post_types( $post_types ) {
    if ( empty( NeoNews_SEO_Lite::get( 'sitemap_posts' ) ) ) {
        unset( $post_types['post'] );
    }
    if ( empty( NeoNews_SEO_Lite::get( 'sitemap_pages' ) ) ) {
        unset( $post_types['page'] );
    }
    return $post_types;
}

/**
 * @param array<string,WP_Taxonomy> $taxonomies Taxonomies.
 * @return array<string,WP_Taxonomy>
 */
function neonews_seo_lite_filter_sitemap_taxonomies( $taxonomies ) {
    if ( empty( NeoNews_SEO_Lite::get( 'sitemap_categories' ) ) ) {
        unset( $taxonomies['category'] );
    }
    if ( empty( NeoNews_SEO_Lite::get( 'sitemap_tags' ) ) ) {
        unset( $taxonomies['post_tag'] );
    }
    return $taxonomies;
}

/**
 * @param bool $enabled Whether user sitemaps are enabled.
 * @return bool
 */
function neonews_seo_lite_filter_sitemap_users( $enabled ) {
    return ! empty( NeoNews_SEO_Lite::get( 'sitemap_authors' ) );
}

/**
 * @param array<string,mixed> $args      Query args.
 * @param string              $post_type Post type.
 * @return array<string,mixed>
 */
function neonews_seo_lite_filter_sitemap_posts( $args, $post_type ) {
    if ( empty( NeoNews_SEO_Lite::get( 'sitemap_exclude_noindex' ) ) ) {
        return $args;
    }

    if ( 'post' !== $post_type && 'page' !== $post_type ) {
        return $args;
    }

    $args['meta_query']   = isset( $args['meta_query'] ) ? $args['meta_query'] : array();
    $args['meta_query'][] = array(
        'relation' => 'OR',
        array(
            'key'     => '_nn_seo_lite_robots',
            'compare' => 'NOT EXISTS',
        ),
        array(
            'key'     => '_nn_seo_lite_robots',
            'value'   => 'noindex',
            'compare' => 'NOT LIKE',
        ),
    );

    return $args;
}

/**
 * @param string $output Robots.txt output.
 * @param bool   $public Whether site is public.
 * @return string
 */
function neonews_seo_lite_append_sitemap_to_robots( $output, $public ) {
    if ( ! $public || false !== strpos( $output, 'Sitemap:' ) ) {
        return $output;
    }

    return $output . "\nSitemap: " . home_url( '/wp-sitemap.xml' ) . "\n";
}
