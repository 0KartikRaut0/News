<?php
/**
 * Minimal JSON-LD output.
 *
 * @package NeoNews_SEO_Lite
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @return void
 */
function neonews_seo_lite_register_schema_hooks() {
    if ( ! NeoNews_SEO_Lite::get( 'enable_schema' ) ) {
        return;
    }

    add_action( 'wp_head', 'neonews_seo_lite_output_schema', 6 );
}

/**
 * @return void
 */
function neonews_seo_lite_output_schema() {
    $graphs = array();

    $org_name = trim( (string) NeoNews_SEO_Lite::get( 'org_name' ) );
    if ( ! $org_name ) {
        $org_name = get_bloginfo( 'name' );
    }

    $graphs[] = array(
        '@type' => 'Organization',
        '@id'   => home_url( '/#organization' ),
        'name'  => $org_name,
        'url'   => home_url( '/' ),
    );

    if ( is_front_page() || is_home() ) {
        $graphs[] = array(
            '@type'     => 'WebSite',
            '@id'       => home_url( '/#website' ),
            'url'       => home_url( '/' ),
            'name'      => get_bloginfo( 'name' ),
            'publisher' => array( '@id' => home_url( '/#organization' ) ),
        );
    } elseif ( is_singular( 'post' ) ) {
        $post_id = get_queried_object_id();
        $graphs[] = array(
            '@type'            => 'NewsArticle',
            '@id'              => get_permalink( $post_id ) . '#article',
            'headline'         => get_the_title( $post_id ),
            'datePublished'    => get_the_date( 'c', $post_id ),
            'dateModified'     => get_the_modified_date( 'c', $post_id ),
            'mainEntityOfPage' => get_permalink( $post_id ),
            'author'           => array(
                '@type' => 'Person',
                'name'  => get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ),
            ),
            'publisher'        => array( '@id' => home_url( '/#organization' ) ),
        );
    }

    if ( NeoNews_SEO_Lite::get( 'enable_breadcrumb_schema' ) && function_exists( 'neonews_seo_lite_get_breadcrumbs' ) ) {
        $breadcrumb_schema = neonews_seo_lite_breadcrumb_schema();
        if ( $breadcrumb_schema ) {
            $graphs[] = $breadcrumb_schema;
        }
    }

    if ( empty( $graphs ) ) {
        return;
    }

    $payload = count( $graphs ) > 1
        ? array( '@context' => 'https://schema.org', '@graph' => $graphs )
        : array_merge( array( '@context' => 'https://schema.org' ), $graphs[0] );

    printf(
        '<script type="application/ld+json">%s</script>' . "\n",
        wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
    );
}

/**
 * @return array<string,mixed>|null
 */
function neonews_seo_lite_breadcrumb_schema() {
    $crumbs = neonews_seo_lite_get_breadcrumbs();
    if ( count( $crumbs ) < 2 ) {
        return null;
    }

    $items = array();
    $pos   = 1;
    foreach ( $crumbs as $crumb ) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => $crumb['name'],
            'item'     => esc_url( $crumb['url'] ),
        );
    }

    $canonical = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );

    return array(
        '@type'           => 'BreadcrumbList',
        '@id'             => esc_url( $canonical ) . '#breadcrumb',
        'itemListElement' => $items,
    );
}
