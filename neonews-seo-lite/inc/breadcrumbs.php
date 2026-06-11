<?php
/**
 * Lightweight breadcrumb navigation.
 *
 * @package NeoNews_SEO_Lite
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @return void
 */
function neonews_seo_lite_register_breadcrumb_hooks() {
    if ( ! NeoNews_SEO_Lite::get( 'enable_breadcrumbs' ) ) {
        return;
    }

    add_action( 'neonews_single_before_content', 'neonews_seo_lite_render_breadcrumbs', 3 );
    add_action( 'neonews_archive_after_header', 'neonews_seo_lite_render_breadcrumbs', 3 );
    add_action( 'wp_enqueue_scripts', 'neonews_seo_lite_enqueue_breadcrumb_styles', 40 );
}

/**
 * @return void
 */
function neonews_seo_lite_enqueue_breadcrumb_styles() {
    if ( ! is_singular() && ! is_archive() && ! is_search() && ! is_author() ) {
        return;
    }

    wp_enqueue_style(
        'neonews-seo-lite',
        NEONEWS_SEO_LITE_URL . 'public/css/seo-lite.css',
        array(),
        NEONEWS_SEO_LITE_VERSION
    );
}

/**
 * @return array<int,array{name:string,url:string}>
 */
function neonews_seo_lite_get_breadcrumbs() {
    $home_label = trim( (string) NeoNews_SEO_Lite::get( 'breadcrumb_home_label' ) );
    if ( ! $home_label ) {
        $home_label = __( 'Home', 'neonews-seo-lite' );
    }

    $crumbs = array(
        array(
            'name' => $home_label,
            'url'  => home_url( '/' ),
        ),
    );

    if ( is_singular( 'post' ) ) {
        $post_id = get_queried_object_id();
        $cats    = get_the_category( $post_id );
        if ( ! empty( $cats ) ) {
            $crumbs[] = array(
                'name' => $cats[0]->name,
                'url'  => get_category_link( $cats[0]->term_id ),
            );
        }
        $crumbs[] = array(
            'name' => get_the_title( $post_id ),
            'url'  => get_permalink( $post_id ),
        );
    } elseif ( is_page() ) {
        $post_id   = get_queried_object_id();
        $ancestors = array_reverse( get_post_ancestors( $post_id ) );
        foreach ( $ancestors as $ancestor_id ) {
            $crumbs[] = array(
                'name' => get_the_title( $ancestor_id ),
                'url'  => get_permalink( $ancestor_id ),
            );
        }
        $crumbs[] = array(
            'name' => get_the_title( $post_id ),
            'url'  => get_permalink( $post_id ),
        );
    } elseif ( is_category() || is_tag() || is_tax() ) {
        $term = get_queried_object();
        if ( $term instanceof WP_Term ) {
            $link = get_term_link( $term );
            $crumbs[] = array(
                'name' => $term->name,
                'url'  => is_wp_error( $link ) ? '' : $link,
            );
        }
    } elseif ( is_author() ) {
        $author_id = get_queried_object_id();
        $crumbs[]  = array(
            'name' => get_the_author_meta( 'display_name', $author_id ),
            'url'  => get_author_posts_url( $author_id ),
        );
    } elseif ( is_search() ) {
        $crumbs[] = array(
            'name' => sprintf(
                /* translators: %s: search query */
                __( 'Search: %s', 'neonews-seo-lite' ),
                get_search_query()
            ),
            'url'  => get_search_link(),
        );
    }

    return apply_filters( 'neonews_seo_lite_breadcrumbs', $crumbs );
}

/**
 * @param array<int,array{name:string,url:string}> $crumbs Crumbs.
 * @return string
 */
function neonews_seo_lite_build_breadcrumb_html( $crumbs ) {
    if ( count( $crumbs ) < 2 ) {
        return '';
    }

    $items = array();
    $last  = count( $crumbs ) - 1;

    foreach ( $crumbs as $index => $crumb ) {
        $name = esc_html( $crumb['name'] );
        if ( $index === $last ) {
            $items[] = '<span class="nn-seo-crumb-current" aria-current="page">' . $name . '</span>';
        } else {
            $items[] = '<a class="nn-seo-crumb-link" href="' . esc_url( $crumb['url'] ) . '">' . $name . '</a>';
        }
    }

    return '<nav class="nn-seo-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'neonews-seo-lite' ) . '">' .
        implode( '<span class="nn-seo-crumb-sep" aria-hidden="true">›</span>', $items ) .
        '</nav>';
}

/**
 * @return void
 */
function neonews_seo_lite_render_breadcrumbs() {
    $crumbs = neonews_seo_lite_get_breadcrumbs();
    $html   = neonews_seo_lite_build_breadcrumb_html( $crumbs );
    $html   = apply_filters( 'neonews_seo_lite_breadcrumbs_html', $html, $crumbs );

    if ( ! $html ) {
        return;
    }

    if ( is_archive() || is_search() || is_author() ) {
        echo '<div class="nn-container nn-seo-breadcrumbs-wrap">';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $html;
        echo '</div>';
        return;
    }

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo $html;
}
