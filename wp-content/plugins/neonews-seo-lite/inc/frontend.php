<?php
/**
 * Frontend meta and social tags.
 *
 * @package NeoNews_SEO_Lite
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @return void
 */
function neonews_seo_lite_register_frontend_hooks() {
    add_filter( 'pre_get_document_title', 'neonews_seo_lite_filter_title', 20 );
    add_filter( 'wp_robots', 'neonews_seo_lite_filter_robots', 20 );
    add_action( 'wp_head', 'neonews_seo_lite_output_head', 1 );
}

/**
 * @return array{title:string,description:string,canonical:string,noindex:bool}
 */
function neonews_seo_lite_context() {
    static $context = null;
    static $building = false;

    if ( null !== $context ) {
        return $context;
    }

    if ( $building ) {
        return array(
            'title'       => get_bloginfo( 'name' ),
            'description' => get_bloginfo( 'description' ),
            'canonical'   => home_url( '/' ),
            'noindex'     => false,
        );
    }

    $building = true;

    $title       = neonews_seo_lite_default_title();
    $description = get_bloginfo( 'description' );
    $canonical   = home_url( add_query_arg( array() ) );
    $noindex     = false;

    if ( is_singular() ) {
        $post_id = get_queried_object_id();
        $custom_title = get_post_meta( $post_id, '_nn_seo_lite_title', true );
        $custom_desc  = get_post_meta( $post_id, '_nn_seo_lite_description', true );
        $custom_robots = get_post_meta( $post_id, '_nn_seo_lite_robots', true );

        $title       = $custom_title ? $custom_title : ( get_the_title( $post_id ) . ' ' . neonews_seo_lite_sep() . ' ' . get_bloginfo( 'name' ) );
        $description = $custom_desc ? $custom_desc : neonews_seo_lite_post_description( $post_id );
        $canonical   = get_permalink( $post_id );

        if ( 'noindex' === $custom_robots || 'noindex,nofollow' === $custom_robots ) {
            $noindex = true;
        }
    } elseif ( is_front_page() || is_home() ) {
        $home_desc = trim( (string) NeoNews_SEO_Lite::get( 'home_description' ) );
        if ( $home_desc ) {
            $description = $home_desc;
        }
        $canonical = home_url( '/' );
    } elseif ( is_category() || is_tag() || is_tax() ) {
        $term = get_queried_object();
        if ( $term instanceof WP_Term ) {
            $title       = $term->name . ' ' . neonews_seo_lite_sep() . ' ' . get_bloginfo( 'name' );
            $description = wp_strip_all_tags( term_description( $term ) );
            $canonical   = get_term_link( $term );
        }
    } elseif ( is_author() ) {
        if ( NeoNews_SEO_Lite::get( 'noindex_author' ) ) {
            $noindex = true;
        }
        $author_id   = get_queried_object_id();
        $title       = get_the_author_meta( 'display_name', $author_id ) . ' ' . neonews_seo_lite_sep() . ' ' . get_bloginfo( 'name' );
        $description = get_the_author_meta( 'description', $author_id );
        $canonical   = get_author_posts_url( $author_id );
    } elseif ( is_search() ) {
        if ( NeoNews_SEO_Lite::get( 'noindex_search' ) ) {
            $noindex = true;
        }
        $title = sprintf(
            /* translators: %s: search query */
            __( 'Search results for "%s"', 'neonews-seo-lite' ),
            get_search_query()
        ) . ' ' . neonews_seo_lite_sep() . ' ' . get_bloginfo( 'name' );
    }

    $context = array(
        'title'       => $title,
        'description' => neonews_seo_lite_trim( $description ),
        'canonical'   => is_wp_error( $canonical ) ? home_url( '/' ) : $canonical,
        'noindex'     => $noindex,
    );

    $building = false;

    return $context;
}

/**
 * Build a document title without calling pre_get_document_title (avoids recursion).
 *
 * @return string
 */
function neonews_seo_lite_default_title() {
    $sep  = neonews_seo_lite_sep();
    $name = get_bloginfo( 'name' );

    if ( is_singular() ) {
        return get_the_title( get_queried_object_id() ) . ' ' . $sep . ' ' . $name;
    }

    if ( is_front_page() || is_home() ) {
        return $name;
    }

    if ( is_category() || is_tag() || is_tax() ) {
        $term = get_queried_object();
        if ( $term instanceof WP_Term ) {
            return $term->name . ' ' . $sep . ' ' . $name;
        }
    }

    if ( is_author() ) {
        return get_the_author_meta( 'display_name', get_queried_object_id() ) . ' ' . $sep . ' ' . $name;
    }

    if ( is_search() ) {
        return sprintf(
            /* translators: %s: search query */
            __( 'Search results for "%s"', 'neonews-seo-lite' ),
            get_search_query()
        ) . ' ' . $sep . ' ' . $name;
    }

    if ( is_archive() ) {
        return wp_strip_all_tags( get_the_archive_title() ) . ' ' . $sep . ' ' . $name;
    }

    return $name;
}

/**
 * @param int $post_id Post ID.
 * @return string
 */
function neonews_seo_lite_post_description( $post_id ) {
    $excerpt = get_post_field( 'post_excerpt', $post_id );
    if ( $excerpt ) {
        return wp_trim_words( wp_strip_all_tags( $excerpt ), 30, '' );
    }

    $glance = get_post_meta( $post_id, '_neonews_smart_glance', true );
    if ( $glance ) {
        return wp_trim_words( wp_strip_all_tags( $glance ), 30, '' );
    }

    return wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 30, '' );
}

/**
 * @return string
 */
function neonews_seo_lite_sep() {
    $sep = NeoNews_SEO_Lite::get( 'title_separator' );
    return $sep ? trim( (string) $sep ) : '|';
}

/**
 * @param string $text Text.
 * @return string
 */
function neonews_seo_lite_trim( $text ) {
    $text = wp_strip_all_tags( (string) $text );
    $text = preg_replace( '/\s+/', ' ', $text );
    return function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 160 ) : substr( $text, 0, 160 );
}

/**
 * @param string $title Document title.
 * @return string
 */
function neonews_seo_lite_filter_title( $title ) {
    $context = neonews_seo_lite_context();
    return $context['title'] ? $context['title'] : $title;
}

/**
 * @param array<string,bool> $robots Robots directives.
 * @return array<string,bool>
 */
function neonews_seo_lite_filter_robots( $robots ) {
    if ( ! empty( neonews_seo_lite_context()['noindex'] ) ) {
        $robots['noindex'] = true;
    }
    return $robots;
}

/**
 * @return void
 */
function neonews_seo_lite_output_head() {
    $context = neonews_seo_lite_context();

    if ( ! empty( $context['description'] ) ) {
        printf(
            '<meta name="description" content="%s">' . "\n",
            esc_attr( $context['description'] )
        );
    }

    if ( ! empty( $context['canonical'] ) ) {
        printf(
            '<link rel="canonical" href="%s">' . "\n",
            esc_url( $context['canonical'] )
        );
    }

    neonews_seo_lite_output_social( $context );
}

/**
 * @param array{title:string,description:string,canonical:string} $context Context.
 * @return void
 */
function neonews_seo_lite_output_social( $context ) {
    $title = $context['title'];
    $desc  = $context['description'];
    $url   = $context['canonical'];
    $type  = is_singular( 'post' ) ? 'article' : 'website';
    $image = neonews_seo_lite_social_image();

    printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
    printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
    printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
    printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
    printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );

    if ( $image ) {
        printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
    }

    printf( '<meta name="twitter:card" content="summary_large_image">' . "\n" );
    printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
    printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $desc ) );

    $twitter_site = trim( (string) NeoNews_SEO_Lite::get( 'twitter_site' ) );
    if ( $twitter_site ) {
        printf( '<meta name="twitter:site" content="%s">' . "\n", esc_attr( $twitter_site ) );
    }
}

/**
 * @return string
 */
function neonews_seo_lite_social_image() {
    if ( is_singular() && has_post_thumbnail() ) {
        $src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'neonews-featured' );
        if ( ! $src ) {
            $src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
        }
        if ( $src ) {
            return $src[0];
        }
    }

    $default = trim( (string) NeoNews_SEO_Lite::get( 'default_og_image' ) );
    if ( $default ) {
        return esc_url_raw( $default );
    }

    $icon = get_site_icon_url( 512 );
    return $icon ? $icon : '';
}
