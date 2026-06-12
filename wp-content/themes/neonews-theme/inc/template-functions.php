<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function neonews_pingback_header() {
    if ( is_singular() && pings_open() ) {
        printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
    }
}
add_action( 'wp_head', 'neonews_pingback_header' );

/**
 * Add preload for featured image on single posts
 */
function neonews_preload_featured_image() {
    if ( is_singular() && has_post_thumbnail() ) {
        $image_id = get_post_thumbnail_id();
        $image_data = wp_get_attachment_image_src( $image_id, 'neonews-featured' );
        
        if ( $image_data ) {
            printf(
                '<link rel="preload" as="image" href="%s">',
                esc_url( $image_data[0] )
            );
        }
    }
}
add_action( 'wp_head', 'neonews_preload_featured_image', 1 );

/**
 * Add custom classes to body based on page template
 */
function neonews_body_class_filter( $classes ) {
    if ( is_page_template( 'templates/template-fullwidth.php' ) ) {
        $classes[] = 'no-sidebar';
        $classes[] = 'full-width';
    }

    if ( is_singular( 'post' ) ) {
        $classes[] = 'single-article';
    }

    return $classes;
}
add_filter( 'body_class', 'neonews_body_class_filter' );

/**
 * Modify archive title to remove prefixes
 */
function neonews_archive_title( $title ) {
    if ( is_category() ) {
        $title = single_cat_title( '', false );
    } elseif ( is_tag() ) {
        $title = single_tag_title( '', false );
    } elseif ( is_author() ) {
        $title = get_the_author();
    } elseif ( is_post_type_archive() ) {
        $title = post_type_archive_title( '', false );
    } elseif ( is_tax() ) {
        $title = single_term_title( '', false );
    }

    return $title;
}
add_filter( 'get_the_archive_title', 'neonews_archive_title' );

/**
 * Add custom query vars
 */
function neonews_query_vars( $vars ) {
    $vars[] = 'nn_category';
    return $vars;
}
add_filter( 'query_vars', 'neonews_query_vars' );

/**
 * Optimize images by adding loading="lazy" to content images
 */
function neonews_lazy_load_content_images( $content ) {
    if ( function_exists( 'neonews_perf_lazy_content_images' ) && neonews_perf_enabled() ) {
        return neonews_perf_lazy_content_images( $content );
    }
    if ( is_feed() || is_preview() ) {
        return $content;
    }
    return $content;
}
add_filter( 'the_content', 'neonews_lazy_load_content_images' );

/**
 * Script defer handled in inc/performance.php when lightweight mode is on.
 */

/**
 * Modify excerpt for news style
 */
function neonews_custom_excerpt( $excerpt ) {
    if ( is_admin() ) {
        return $excerpt;
    }
    
    return wp_trim_words( $excerpt, 25, '&hellip;' );
}
add_filter( 'get_the_excerpt', 'neonews_custom_excerpt' );

/**
 * Add meta description for SEO
 */
function neonews_meta_description() {
    if ( is_singular() ) {
        global $post;
        $description = wp_trim_words( wp_strip_all_tags( $post->post_content ), 30, '' );
        if ( empty( $description ) ) {
            $description = get_bloginfo( 'description' );
        }
    } elseif ( is_category() ) {
        $description = category_description();
        if ( empty( $description ) ) {
            $description = sprintf(
                /* translators: %s: category name */
                __( 'Latest news and articles in %s', 'neonews' ),
                single_cat_title( '', false )
            );
        }
    } elseif ( is_tag() ) {
        $description = tag_description();
        if ( empty( $description ) ) {
            $description = sprintf(
                /* translators: %s: tag name */
                __( 'Articles tagged with %s', 'neonews' ),
                single_tag_title( '', false )
            );
        }
    } elseif ( is_author() ) {
        $description = get_the_author_meta( 'description' );
        if ( empty( $description ) ) {
            $description = sprintf(
                /* translators: %s: author name */
                __( 'Articles by %s', 'neonews' ),
                get_the_author()
            );
        }
    } else {
        $description = get_bloginfo( 'description' );
    }

    if ( ! empty( $description ) ) {
        printf(
            '<meta name="description" content="%s">' . "\n",
            esc_attr( wp_strip_all_tags( $description ) )
        );
    }
}
add_action( 'wp_head', 'neonews_meta_description', 1 );

/**
 * Add Open Graph meta tags for social sharing
 */
function neonews_open_graph_meta() {
    if ( ! is_singular( 'post' ) ) {
        return;
    }

    global $post;

    $og_title = get_the_title();
    $og_description = wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 30, '' );
    $og_url = get_permalink();
    $og_site_name = get_bloginfo( 'name' );

    printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $og_title ) );
    printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $og_description ) );
    printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $og_url ) );
    printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( $og_site_name ) );
    printf( '<meta property="og:type" content="article">' . "\n" );

    if ( has_post_thumbnail() ) {
        $image_data = wp_get_attachment_image_src( get_post_thumbnail_id(), 'neonews-featured' );
        if ( $image_data ) {
            printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image_data[0] ) );
            printf( '<meta property="og:image:width" content="%d">' . "\n", absint( $image_data[1] ) );
            printf( '<meta property="og:image:height" content="%d">' . "\n", absint( $image_data[2] ) );
        }
    }

    printf( '<meta name="twitter:card" content="summary_large_image">' . "\n" );
    printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $og_title ) );
    printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $og_description ) );
}
add_action( 'wp_head', 'neonews_open_graph_meta', 5 );

/**
 * Limit search to posts only
 */
function neonews_search_filter( $query ) {
    if ( $query->is_search && ! is_admin() && $query->is_main_query() ) {
        $query->set( 'post_type', 'post' );
    }
    return $query;
}
add_filter( 'pre_get_posts', 'neonews_search_filter' );

/**
 * Add SVG support
 */
function neonews_mime_types( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'neonews_mime_types' );

/**
 * Security: Disable file editing from admin
 */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
    define( 'DISALLOW_FILE_EDIT', true );
}

/**
 * Remove WordPress version from head and feeds
 */
remove_action( 'wp_head', 'wp_generator' );

/**
 * Disable XML-RPC for security
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Remove unnecessary header items
 */
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
