<?php
/**
 * Platform settings helpers — parse lists, feature flags, design getters.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get a single platform setting.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function neonews_get_setting( $key, $default = null ) {
    $settings = neonews_get_settings();
    return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

/**
 * Is a platform feature/section enabled?
 *
 * @param string $key Setting key (truthy checkbox).
 * @return bool
 */
function neonews_is_feature_enabled( $key ) {
    return ! empty( neonews_get_setting( $key, false ) );
}

/**
 * Parse comma-separated slugs.
 *
 * @param string $raw Raw string.
 * @return string[]
 */
function neonews_parse_slug_list( $raw ) {
    if ( empty( $raw ) ) {
        return array();
    }
    $parts = array_map( 'trim', explode( ',', (string) $raw ) );
    $parts = array_filter( $parts );
    return array_values( array_unique( array_map( 'sanitize_title', $parts ) ) );
}

/**
 * Homepage category slugs from settings.
 *
 * @return string[]
 */
function neonews_get_home_categories() {
    $raw = neonews_get_setting( 'home_category_slugs', 'business,technology,sports,entertainment' );
    $slugs = neonews_parse_slug_list( $raw );
    return ! empty( $slugs ) ? $slugs : array( 'business', 'technology', 'sports', 'entertainment' );
}

/**
 * Footer section category slugs.
 *
 * @return string[]
 */
function neonews_get_footer_categories() {
    $raw = neonews_get_setting( 'footer_section_categories', 'politics,business,technology,sports,entertainment' );
    return neonews_parse_slug_list( $raw );
}

/**
 * Fallback nav category slugs.
 *
 * @return string[]
 */
function neonews_get_fallback_nav_categories() {
    $raw = neonews_get_setting( 'fallback_nav_categories', 'politics,business,technology,sports,entertainment' );
    return neonews_parse_slug_list( $raw );
}

/**
 * Fallback nav page slugs.
 *
 * @return string[]
 */
function neonews_get_fallback_nav_pages() {
    $raw = neonews_get_setting( 'fallback_nav_pages', 'about,contact' );
    return neonews_parse_slug_list( $raw );
}

/**
 * Footer company page slugs.
 *
 * @return string[]
 */
function neonews_get_footer_company_slugs() {
    $raw = neonews_get_setting( 'footer_company_pages', 'about,careers,advertise,contact' );
    return neonews_parse_slug_list( $raw );
}

/**
 * Category accent color map (settings override defaults).
 *
 * @return array<string,string>
 */
function neonews_get_category_color_map() {
    $defaults = array(
        'business'      => '#16a34a',
        'technology'    => '#ca8a04',
        'sports'        => '#9333ea',
        'entertainment' => '#2563eb',
        'culture'       => '#2563eb',
        'politics'      => '#2563eb',
        'videos'        => '#ea580c',
    );

    $raw = neonews_get_setting( 'category_colors', '' );
    if ( empty( $raw ) ) {
        return $defaults;
    }

    $lines = preg_split( '/[\r\n,]+/', (string) $raw );
    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( '' === $line ) {
            continue;
        }
        if ( false !== strpos( $line, ':' ) ) {
            list( $slug, $color ) = array_map( 'trim', explode( ':', $line, 2 ) );
            $slug  = sanitize_title( $slug );
            $color = sanitize_hex_color( $color );
            if ( $slug && $color ) {
                $defaults[ $slug ] = $color;
            }
        } elseif ( preg_match( '/^[a-z0-9-]+$/', sanitize_title( $line ) ) ) {
            // Ignore bare slugs without colors.
            continue;
        }
    }

    return $defaults;
}

/**
 * Available Google Font choices for Customizer.
 *
 * @return array<string,string>
 */
function neonews_get_font_choices() {
    return array(
        'Inter'              => 'Inter',
        'Roboto'             => 'Roboto',
        'Open Sans'          => 'Open Sans',
        'Lato'               => 'Lato',
        'Poppins'            => 'Poppins',
        'Merriweather'       => 'Merriweather',
        'Playfair Display'   => 'Playfair Display',
        'DM Serif Display'   => 'DM Serif Display',
        'Source Sans 3'      => 'Source Sans 3',
        'Nunito'             => 'Nunito',
        'Oswald'             => 'Oswald',
        'Libre Baskerville'  => 'Libre Baskerville',
    );
}

/**
 * Build Google Fonts URL for body + heading.
 *
 * @param string $body    Body font family.
 * @param string $heading Heading font family.
 * @return string
 */
function neonews_build_google_fonts_url( $body, $heading ) {
    $families = array();
    foreach ( array_unique( array( $body, $heading ) ) as $font ) {
        $families[] = 'family=' . rawurlencode( $font ) . ':ital,wght@0,400;0,500;0,600;0,700;1,400';
    }
    return 'https://fonts.googleapis.com/css2?' . implode( '&', $families ) . '&display=swap';
}

/**
 * Default design token values (match newspulse.css).
 *
 * @return array<string,string>
 */
function neonews_get_design_defaults() {
    return array(
        'primary_color'       => '#ff7a18',
        'secondary_color'     => '#1c1917',
        'accent_color'        => '#ff7a18',
        'bg_primary'          => '#ffffff',
        'bg_secondary'        => '#fafafa',
        'bg_tertiary'         => '#f5f5f4',
        'text_primary'        => '#1c1917',
        'text_secondary'      => '#57534e',
        'text_muted'          => '#a8a29e',
        'border_color'        => '#e7e5e4',
        'font_body'           => 'Inter',
        'font_heading'        => 'DM Serif Display',
        'radius_lg'           => '16',
        'radius_xl'           => '20',
        'dark_bg_primary'     => '#0c0a09',
        'dark_bg_secondary'   => '#1c1917',
        'dark_text_primary'   => '#fafaf9',
        'dark_text_secondary' => '#d6d3d1',
    );
}

/**
 * Get design token — Customizer theme_mod with fallback.
 *
 * @param string $key Token key without prefix.
 * @return string
 */
function neonews_get_design_token( $key ) {
    $defaults = neonews_get_design_defaults();
    $default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
    $mod      = get_theme_mod( 'neonews_' . $key, $default );
    return is_string( $mod ) ? $mod : (string) $default;
}

/**
 * Should breaking news ticker show?
 *
 * @return bool
 */
function neonews_show_breaking_ticker() {
    if ( ! neonews_is_feature_enabled( 'show_breaking_news' ) ) {
        return false;
    }
    return (bool) get_theme_mod( 'neonews_show_breaking_news', true );
}

/**
 * Should featured carousel show on homepage?
 *
 * @return bool
 */
function neonews_show_featured_carousel() {
    if ( ! neonews_is_feature_enabled( 'show_home_carousel' ) ) {
        return false;
    }
    return (bool) get_theme_mod( 'neonews_show_featured_carousel', true );
}

/**
 * Posts per row for grids.
 *
 * @return int
 */
function neonews_get_posts_per_row() {
    $row = (int) get_theme_mod( 'neonews_posts_per_row', 3 );
    return in_array( $row, array( 2, 3, 4 ), true ) ? $row : 3;
}
