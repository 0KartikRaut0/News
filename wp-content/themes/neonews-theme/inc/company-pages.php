<?php
/**
 * Company pages — About Us and related one-click setup.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Default About Us page body.
 *
 * @return string
 */
function neonews_get_about_page_content() {
    $site = get_bloginfo( 'name' );

    return '<h3>' . esc_html__( 'Our Story', 'neonews' ) . '</h3>'
        . '<p>' . sprintf(
            /* translators: %s: site name */
            esc_html__( '%s is a next-gen newsroom built for speed, clarity, and visual storytelling. Our mission is to make complex stories simple for readers worldwide.', 'neonews' ),
            esc_html( $site )
        ) . '</p>'
        . '<h3>' . esc_html__( 'Our Values', 'neonews' ) . '</h3>'
        . '<ul>'
        . '<li><strong>' . esc_html__( 'Truth first', 'neonews' ) . '</strong> — ' . esc_html__( 'verified reporting you can trust', 'neonews' ) . '</li>'
        . '<li><strong>' . esc_html__( 'Design matters', 'neonews' ) . '</strong> — ' . esc_html__( 'stories you actually want to read', 'neonews' ) . '</li>'
        . '<li><strong>' . esc_html__( 'Community', 'neonews' ) . '</strong> — ' . esc_html__( 'readers, members, and contributors together', 'neonews' ) . '</li>'
        . '</ul>';
}

/**
 * About page definition.
 *
 * @return array
 */
function neonews_get_about_page_def() {
    return array(
        'slug'     => 'about',
        'title'    => __( 'About Us', 'neonews' ),
        'template' => 'templates/template-about.php',
        'content'  => neonews_get_about_page_content(),
    );
}

/**
 * Get the About Us page object.
 *
 * @return WP_Post|null
 */
function neonews_get_about_page() {
    $page = get_page_by_path( 'about' );
    return $page instanceof WP_Post ? $page : null;
}

/**
 * URL for the About Us page.
 *
 * @return string
 */
function neonews_get_about_page_url() {
    $page = neonews_get_about_page();
    if ( $page ) {
        return get_permalink( $page );
    }

    neonews_ensure_about_page();
    $page = neonews_get_about_page();
    if ( $page ) {
        return get_permalink( $page );
    }

    return home_url( '/about/' );
}

/**
 * Create or update the About Us page.
 *
 * @return bool True when the page exists after the operation.
 */
function neonews_ensure_about_page() {
    $def      = neonews_get_about_page_def();
    $existing = neonews_get_about_page();

    if ( $existing ) {
        wp_update_post( array(
            'ID'           => $existing->ID,
            'post_title'   => $def['title'],
            'post_content' => $def['content'],
        ) );
        update_post_meta( $existing->ID, '_wp_page_template', $def['template'] );
        update_post_meta( $existing->ID, '_neonews_company_page', $def['slug'] );
        return true;
    }

    $page_id = wp_insert_post( array(
        'post_title'   => $def['title'],
        'post_name'    => $def['slug'],
        'post_content' => $def['content'],
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_author'  => get_current_user_id() ?: 1,
    ), true );

    if ( is_wp_error( $page_id ) ) {
        return false;
    }

    update_post_meta( $page_id, '_wp_page_template', $def['template'] );
    update_post_meta( $page_id, '_neonews_company_page', $def['slug'] );
    return true;
}

/**
 * Create About Us automatically when missing.
 */
function neonews_bootstrap_about_page() {
    if ( neonews_get_about_page() ) {
        if ( get_option( 'neonews_about_page_ready', '' ) !== NEONEWS_THEME_VERSION ) {
            update_option( 'neonews_about_page_ready', NEONEWS_THEME_VERSION );
        }
        return;
    }

    if ( neonews_ensure_about_page() ) {
        update_option( 'neonews_about_page_ready', NEONEWS_THEME_VERSION );
        flush_rewrite_rules( false );
    }
}
add_action( 'init', 'neonews_bootstrap_about_page', 12 );
add_action( 'after_switch_theme', 'neonews_bootstrap_about_page' );
