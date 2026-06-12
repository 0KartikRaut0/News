<?php
/**
 * Legal pages — dedicated URLs and auto-creation.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Legal page definitions.
 *
 * @return array
 */
function neonews_get_legal_pages_def() {
    return array(
        'privacy-policy' => array(
            'title'    => __( 'Privacy Policy', 'neonews' ),
            'template' => 'templates/template-legal.php',
            'content'  => neonews_get_privacy_page_content(),
        ),
        'terms-of-service' => array(
            'title'    => __( 'Terms of Service', 'neonews' ),
            'template' => 'templates/template-terms.php',
            'content'  => neonews_get_terms_page_content(),
        ),
        'cookies-policy' => array(
            'title'    => __( 'Cookies Policy', 'neonews' ),
            'template' => 'templates/template-cookies.php',
            'content'  => neonews_get_cookies_page_content(),
        ),
        'cache-storage' => array(
            'title'    => __( 'Cache & Storage', 'neonews' ),
            'template' => 'templates/template-cache.php',
            'content'  => neonews_get_cache_page_content(),
        ),
    );
}

/**
 * Legacy slug fallbacks.
 *
 * @return array
 */
function neonews_get_legal_slug_fallbacks() {
    return array(
        'privacy-policy'   => array( 'privacy-policy' ),
        'terms-of-service' => array( 'terms-of-service', 'terms' ),
        'cookies-policy'   => array( 'cookies-policy', 'cookies' ),
        'cache-storage'    => array( 'cache-storage', 'cache-policy' ),
    );
}

/**
 * Find legal page by canonical key.
 *
 * @param string $key Canonical slug key.
 * @return WP_Post|null
 */
function neonews_get_legal_page( $key ) {
    $fallbacks = neonews_get_legal_slug_fallbacks();
    if ( ! isset( $fallbacks[ $key ] ) ) {
        return null;
    }

    foreach ( $fallbacks[ $key ] as $slug ) {
        $page = get_page_by_path( $slug );
        if ( $page ) {
            return $page;
        }
    }

    $pages = get_posts(
        array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'meta_key'       => '_neonews_legal_page',
            'meta_value'     => $key,
            'posts_per_page' => 1,
            'no_found_rows'  => true,
        )
    );
    if ( ! empty( $pages[0] ) ) {
        return $pages[0];
    }

    if ( 'privacy-policy' === $key ) {
        $privacy_id = (int) get_option( 'wp_page_for_privacy_policy' );
        if ( $privacy_id ) {
            $privacy_page = get_post( $privacy_id );
            if ( $privacy_page && 'publish' === $privacy_page->post_status ) {
                return $privacy_page;
            }
        }
    }

    $definitions = neonews_get_legal_pages_def();
    if ( isset( $definitions[ $key ]['title'] ) ) {
        $title_query = new WP_Query(
            array(
                'post_type'              => 'page',
                'post_status'            => 'publish',
                'title'                  => $definitions[ $key ]['title'],
                'posts_per_page'         => 1,
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            )
        );
        if ( $title_query->have_posts() ) {
            return $title_query->posts[0];
        }
    }

    return null;
}

/**
 * Footer / nav legal links.
 *
 * @return array slug => label
 */
function neonews_get_legal_links() {
    return array(
        'privacy-policy'   => __( 'Privacy Policy', 'neonews' ),
        'terms-of-service' => __( 'Terms of Service', 'neonews' ),
        'cookies-policy'   => __( 'Cookies Policy', 'neonews' ),
        'cache-storage'    => __( 'Cache & Storage', 'neonews' ),
    );
}

/**
 * URL for a legal page.
 *
 * @param string $key Canonical key.
 * @return string
 */
function neonews_get_legal_page_url( $key ) {
    $page = neonews_get_legal_page( $key );
    if ( $page ) {
        return get_permalink( $page );
    }

    neonews_ensure_legal_pages();
    $page = neonews_get_legal_page( $key );
    if ( $page ) {
        return get_permalink( $page );
    }

    return home_url( '/' . $key . '/' );
}

/**
 * Create or update all legal pages.
 *
 * @return int Number of pages touched.
 */
function neonews_ensure_legal_pages() {
    $count = 0;
    foreach ( neonews_get_legal_pages_def() as $slug => $data ) {
        $existing = get_page_by_path( $slug );
        if ( $existing ) {
            wp_update_post( array(
                'ID'           => $existing->ID,
                'post_title'   => $data['title'],
                'post_content' => $data['content'],
            ) );
            update_post_meta( $existing->ID, '_wp_page_template', $data['template'] );
            update_post_meta( $existing->ID, '_neonews_legal_page', $slug );
            $count++;
            continue;
        }

        $page_id = wp_insert_post( array(
            'post_title'   => $data['title'],
            'post_name'    => $slug,
            'post_content' => $data['content'],
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => get_current_user_id() ?: 1,
        ), true );

        if ( ! is_wp_error( $page_id ) ) {
            update_post_meta( $page_id, '_wp_page_template', $data['template'] );
            update_post_meta( $page_id, '_neonews_legal_page', $slug );
            $count++;
        }
    }
    return $count;
}

/**
 * Create legal pages automatically when any are missing.
 */
function neonews_bootstrap_legal_pages() {
    $needs_pages = false;

    foreach ( array_keys( neonews_get_legal_pages_def() ) as $slug ) {
        if ( ! neonews_get_legal_page( $slug ) ) {
            $needs_pages = true;
            break;
        }
    }

    if ( ! $needs_pages ) {
        if ( get_option( 'neonews_legal_pages_ready', '' ) !== NEONEWS_THEME_VERSION ) {
            update_option( 'neonews_legal_pages_ready', NEONEWS_THEME_VERSION );
        }
        return;
    }

    neonews_ensure_legal_pages();

    if ( 'privacy-policy' === array_key_first( neonews_get_legal_pages_def() ) ) {
        $privacy = neonews_get_legal_page( 'privacy-policy' );
        if ( $privacy && ! get_option( 'wp_page_for_privacy_policy' ) ) {
            update_option( 'wp_page_for_privacy_policy', $privacy->ID );
        }
    }

    update_option( 'neonews_legal_pages_ready', NEONEWS_THEME_VERSION );
    flush_rewrite_rules( false );
}
add_action( 'init', 'neonews_bootstrap_legal_pages', 12 );
add_action( 'after_switch_theme', 'neonews_bootstrap_legal_pages' );

function neonews_get_privacy_page_content() {
    return '<p>We respect your privacy. This policy explains what personal data we collect, why we collect it, and your rights.</p><h2>Information we collect</h2><ul><li>Account details when you register</li><li>Reading activity for logged-in members</li><li>Analytics and cookie data</li></ul><h2>Your rights</h2><p>You may request access, correction, or deletion of your data by contacting us.</p>';
}

function neonews_get_terms_page_content() {
    return '<p>Welcome to our news platform. By accessing or using this website, you agree to these Terms of Service.</p><h2>Use of content</h2><p>All articles, images, and videos are protected by copyright. You may share links but may not republish full content without permission.</p><h2>User accounts</h2><p>You are responsible for safeguarding your login credentials. We may suspend accounts that violate these terms.</p><h2>Disclaimer</h2><p>News content is provided for informational purposes. We strive for accuracy but do not guarantee completeness.</p>';
}

function neonews_get_cookies_page_content() {
    return '<p>This Cookies Policy explains how we use cookies and similar technologies on our website.</p><h2>What are cookies?</h2><p>Cookies are small text files stored on your device that help us remember preferences and improve your experience.</p><h2>Types we use</h2><ul><li><strong>Essential</strong> — login sessions, security, cookie consent</li><li><strong>Functional</strong> — theme mode, language preferences</li><li><strong>Analytics</strong> — anonymous usage statistics (only with consent)</li></ul><h2>Managing cookies</h2><p>You can accept or reject non-essential cookies using the banner at the bottom of our site, or clear cookies in your browser settings.</p>';
}

function neonews_get_cache_page_content() {
    return '<p>This policy describes how we use caching and local storage to deliver a fast, reliable experience.</p><h2>Browser cache</h2><p>Static assets (CSS, JavaScript, images) may be stored locally so pages load faster on repeat visits.</p><h2>Server cache</h2><p>We cache rendered pages and API responses to reduce load times during high traffic.</p><h2>Local storage</h2><p>We store theme preferences and cookie consent choices in your browser local storage.</p><h2>Clearing cache</h2><p>You can clear cached data anytime through your browser settings. This may reset preferences such as dark mode.</p>';
}
