<?php
/**
 * Dynamic CSS from Customizer design tokens.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Output CSS variables on the frontend.
 */
function neonews_dynamic_styles() {
    $primary   = neonews_get_design_token( 'primary_color' );
    $secondary = neonews_get_design_token( 'secondary_color' );
    $accent    = neonews_get_design_token( 'accent_color' );
    $primary_dark = neonews_darken_color( $primary, 15 );

    $body_font    = neonews_get_design_token( 'font_body' );
    $heading_font = neonews_get_design_token( 'font_heading' );

    $css  = ':root {';
    $css .= '--nn-primary: ' . esc_attr( $primary ) . ';';
    $css .= '--nn-primary-dark: ' . esc_attr( $primary_dark ) . ';';
    $css .= '--nn-primary-light: ' . esc_attr( neonews_lighten_color( $primary, 92 ) ) . ';';
    $css .= '--nn-secondary: ' . esc_attr( $secondary ) . ';';
    $css .= '--nn-accent: ' . esc_attr( $accent ) . ';';
    $css .= '--nn-gradient: linear-gradient(135deg, ' . esc_attr( $primary ) . ' 0%, ' . esc_attr( $accent ) . ' 100%);';
    $css .= '--nn-bg-primary: ' . esc_attr( neonews_get_design_token( 'bg_primary' ) ) . ';';
    $css .= '--nn-bg-secondary: ' . esc_attr( neonews_get_design_token( 'bg_secondary' ) ) . ';';
    $css .= '--nn-bg-tertiary: ' . esc_attr( neonews_get_design_token( 'bg_tertiary' ) ) . ';';
    $css .= '--nn-text-primary: ' . esc_attr( neonews_get_design_token( 'text_primary' ) ) . ';';
    $css .= '--nn-text-secondary: ' . esc_attr( neonews_get_design_token( 'text_secondary' ) ) . ';';
    $css .= '--nn-text-muted: ' . esc_attr( neonews_get_design_token( 'text_muted' ) ) . ';';
    $css .= '--nn-border-color: ' . esc_attr( neonews_get_design_token( 'border_color' ) ) . ';';
    $css .= '--nn-font-primary: ' . esc_attr( neonews_css_font_stack( $body_font ) ) . ';';
    $css .= '--nn-font-heading: ' . esc_attr( neonews_css_font_stack( $heading_font ) ) . ';';
    $css .= '--nn-radius-lg: ' . esc_attr( absint( neonews_get_design_token( 'radius_lg' ) ) ) . 'px;';
    $css .= '--nn-radius-xl: ' . esc_attr( absint( neonews_get_design_token( 'radius_xl' ) ) ) . 'px;';
    $css .= '--nn-glow: 0 8px 32px ' . esc_attr( neonews_hex_to_rgba( $primary, 0.25 ) ) . ';';
    $css .= '--nn-posts-per-row: ' . esc_attr( neonews_get_posts_per_row() ) . ';';
    $css .= '}';

    $css .= '[data-theme="dark"] {';
    $css .= '--nn-bg-primary: ' . esc_attr( neonews_get_design_token( 'dark_bg_primary' ) ) . ';';
    $css .= '--nn-bg-secondary: ' . esc_attr( neonews_get_design_token( 'dark_bg_secondary' ) ) . ';';
    $css .= '--nn-bg-tertiary: ' . esc_attr( neonews_get_design_token( 'dark_bg_secondary' ) ) . ';';
    $css .= '--nn-text-primary: ' . esc_attr( neonews_get_design_token( 'dark_text_primary' ) ) . ';';
    $css .= '--nn-text-secondary: ' . esc_attr( neonews_get_design_token( 'dark_text_secondary' ) ) . ';';
    $css .= '--nn-border-color: #292524;';
    $css .= '}';

    $css .= '.nn-post-grid { grid-template-columns: repeat(var(--nn-posts-per-row), minmax(0, 1fr)); }';
    $css .= '@media (max-width: 991px) { .nn-post-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }';
    $css .= '@media (max-width: 575px) { .nn-post-grid { grid-template-columns: 1fr; } }';

    wp_add_inline_style( 'neonews-main', $css );
}
add_action( 'wp_enqueue_scripts', 'neonews_dynamic_styles', 25 );

/**
 * CSS font-family stack.
 *
 * @param string $family Font name.
 * @return string
 */
function neonews_css_font_stack( $family ) {
    $family = trim( $family );
    if ( false === strpos( $family, ',' ) ) {
        return "'" . esc_attr( $family ) . "', system-ui, -apple-system, sans-serif";
    }
    return esc_attr( $family );
}

/**
 * Lighten hex toward white (for primary-light tint).
 *
 * @param string $hex     Hex color.
 * @param int    $percent Percent toward white.
 * @return string
 */
function neonews_lighten_color( $hex, $percent ) {
    $hex = ltrim( $hex, '#' );
    if ( 3 === strlen( $hex ) ) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    $r = hexdec( substr( $hex, 0, 2 ) );
    $g = hexdec( substr( $hex, 2, 2 ) );
    $b = hexdec( substr( $hex, 4, 2 ) );
    $r = min( 255, $r + ( ( 255 - $r ) * $percent / 100 ) );
    $g = min( 255, $g + ( ( 255 - $g ) * $percent / 100 ) );
    $b = min( 255, $b + ( ( 255 - $b ) * $percent / 100 ) );
    return sprintf( '#%02x%02x%02x', $r, $g, $b );
}

/**
 * Hex to rgba string.
 *
 * @param string $hex   Hex color.
 * @param float  $alpha Alpha 0-1.
 * @return string
 */
function neonews_hex_to_rgba( $hex, $alpha = 1 ) {
    $hex = ltrim( $hex, '#' );
    if ( 3 === strlen( $hex ) ) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    $r = hexdec( substr( $hex, 0, 2 ) );
    $g = hexdec( substr( $hex, 2, 2 ) );
    $b = hexdec( substr( $hex, 4, 2 ) );
    return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $alpha . ')';
}

/**
 * Darken a hex color by percentage.
 *
 * @param string $hex     Hex color.
 * @param int    $percent Percent to darken.
 * @return string
 */
function neonews_darken_color( $hex, $percent ) {
    $hex = str_replace( '#', '', $hex );

    if ( strlen( $hex ) === 3 ) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    $r = hexdec( substr( $hex, 0, 2 ) );
    $g = hexdec( substr( $hex, 2, 2 ) );
    $b = hexdec( substr( $hex, 4, 2 ) );

    $r = max( 0, min( 255, $r - ( $r * $percent / 100 ) ) );
    $g = max( 0, min( 255, $g - ( $g * $percent / 100 ) ) );
    $b = max( 0, min( 255, $b - ( $b * $percent / 100 ) ) );

    return sprintf( '#%02x%02x%02x', $r, $g, $b );
}
