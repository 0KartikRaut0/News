<?php
/**
 * XML sitemap tuning for WordPress core sitemaps.
 *
 * @package NeoNews_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_SEO_Sitemap {

    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        add_filter( 'wp_sitemaps_post_types', array( $this, 'filter_post_types' ) );
        add_filter( 'wp_sitemaps_taxonomies', array( $this, 'filter_taxonomies' ) );
        add_filter( 'wp_sitemaps_users', array( $this, 'filter_users' ) );
        add_filter( 'wp_sitemaps_posts_query_args', array( $this, 'filter_posts_query' ), 10, 2 );
        add_filter( 'wp_sitemaps_max_urls', array( $this, 'max_urls' ) );
    }

    /**
     * @param array $post_types Post types.
     * @return array
     */
    public function filter_post_types( $post_types ) {
        if ( ! NeoNews_SEO::is_enabled() ) {
            return $post_types;
        }

        $settings = NeoNews_SEO::get_settings();
        if ( empty( $settings['sitemap_posts'] ) ) {
            unset( $post_types['post'] );
        }
        if ( empty( $settings['sitemap_pages'] ) ) {
            unset( $post_types['page'] );
        }

        return $post_types;
    }

    /**
     * @param array $taxonomies Taxonomies.
     * @return array
     */
    public function filter_taxonomies( $taxonomies ) {
        if ( ! NeoNews_SEO::is_enabled() ) {
            return $taxonomies;
        }

        $settings = NeoNews_SEO::get_settings();
        if ( empty( $settings['sitemap_categories'] ) ) {
            unset( $taxonomies['category'] );
        }
        if ( empty( $settings['sitemap_tags'] ) ) {
            unset( $taxonomies['post_tag'] );
        }

        return $taxonomies;
    }

    /**
     * @param bool $enabled Enabled.
     * @return bool
     */
    public function filter_users( $enabled ) {
        if ( ! NeoNews_SEO::is_enabled() ) {
            return $enabled;
        }

        return ! empty( NeoNews_SEO::get_settings( 'sitemap_authors' ) );
    }

    /**
     * @param array  $args Query args.
     * @param string $post_type Post type.
     * @return array
     */
    public function filter_posts_query( $args, $post_type ) {
        if ( ! NeoNews_SEO::is_enabled() || empty( NeoNews_SEO::get_settings( 'sitemap_exclude_noindex' ) ) ) {
            return $args;
        }

        if ( 'post' !== $post_type && 'page' !== $post_type ) {
            return $args;
        }

        $args['meta_query'] = isset( $args['meta_query'] ) ? $args['meta_query'] : array();
        $args['meta_query'][] = array(
            'relation' => 'OR',
            array(
                'key'     => NeoNews_SEO_Meta::POST_ROBOTS,
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key'     => NeoNews_SEO_Meta::POST_ROBOTS,
                'value'   => 'noindex',
                'compare' => 'NOT LIKE',
            ),
        );

        return $args;
    }

    /**
     * @param int $max Max URLs.
     * @return int
     */
    public function max_urls( $max ) {
        return 2000;
    }
}
