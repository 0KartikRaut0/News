<?php
/**
 * Post and term SEO meta helpers.
 *
 * @package NeoNews_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_SEO_Meta {

    const POST_TITLE       = '_neonews_seo_title';
    const POST_DESCRIPTION = '_neonews_seo_description';
    const POST_CANONICAL   = '_neonews_seo_canonical';
    const POST_ROBOTS      = '_neonews_seo_robots';
    const POST_OG_IMAGE    = '_neonews_seo_og_image';
    const POST_FOCUS       = '_neonews_seo_focus_keyword';

    const TERM_TITLE       = '_neonews_seo_title';
    const TERM_DESCRIPTION = '_neonews_seo_description';
    const TERM_ROBOTS      = '_neonews_seo_robots';

    /**
     * @param int    $post_id Post ID.
     * @param string $key     Meta key constant.
     * @return string
     */
    public static function get_post( $post_id, $key ) {
        return (string) get_post_meta( $post_id, $key, true );
    }

    /**
     * @param int    $term_id Term ID.
     * @param string $taxonomy Taxonomy.
     * @param string $key Meta key constant.
     * @return string
     */
    public static function get_term( $term_id, $taxonomy, $key ) {
        return (string) get_term_meta( $term_id, $key, true );
    }

    /**
     * Parse robots string into directives.
     *
     * @param string $robots Robots value.
     * @return array{noindex:bool,nofollow:bool}
     */
    public static function parse_robots( $robots ) {
        $robots   = strtolower( trim( (string) $robots ) );
        $parts    = array_filter( array_map( 'trim', explode( ',', $robots ) ) );
        $noindex  = in_array( 'noindex', $parts, true );
        $nofollow = in_array( 'nofollow', $parts, true );

        return array(
            'noindex'  => $noindex,
            'nofollow' => $nofollow,
        );
    }

    /**
     * SEO score for admin list column.
     *
     * @param int $post_id Post ID.
     * @return string good|warn|noindex
     */
    public static function get_post_score( $post_id ) {
        $robots = self::parse_robots( self::get_post( $post_id, self::POST_ROBOTS ) );
        if ( $robots['noindex'] ) {
            return 'noindex';
        }

        $title = self::get_post( $post_id, self::POST_TITLE );
        $desc  = self::get_post( $post_id, self::POST_DESCRIPTION );

        if ( $title && $desc ) {
            return 'good';
        }

        if ( has_post_thumbnail( $post_id ) || get_the_excerpt( $post_id ) ) {
            return 'warn';
        }

        return 'warn';
    }
}
