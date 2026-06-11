<?php
/**
 * Theme Customizer — design tokens, typography, layout.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add Customizer settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 */
function neonews_customize_register( $wp_customize ) {
    $defaults = neonews_get_design_defaults();
    $fonts    = neonews_get_font_choices();

    $wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
    if ( isset( $wp_customize->selective_refresh ) ) {
        $wp_customize->selective_refresh->add_partial( 'blogname', array(
            'selector'        => '.nn-logo-text',
            'render_callback' => 'neonews_customize_partial_blogname',
        ) );
    }

    $wp_customize->add_panel( 'neonews_panel', array(
        'title'    => esc_html__( 'NewsPulse Design', 'neonews' ),
        'priority' => 25,
    ) );

    $wp_customize->add_section( 'neonews_theme_options', array(
        'title'    => esc_html__( 'Layout & Features', 'neonews' ),
        'panel'    => 'neonews_panel',
        'priority' => 10,
    ) );

    $wp_customize->add_setting( 'neonews_default_theme_mode', array(
        'default'           => 'light',
        'sanitize_callback' => 'neonews_sanitize_theme_mode',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'neonews_default_theme_mode', array(
        'label'   => esc_html__( 'Default Theme Mode', 'neonews' ),
        'section' => 'neonews_theme_options',
        'type'    => 'select',
        'choices' => array(
            'light' => esc_html__( 'Light', 'neonews' ),
            'dark'  => esc_html__( 'Dark', 'neonews' ),
        ),
    ) );

    $wp_customize->add_setting( 'neonews_show_breaking_news', array(
        'default'           => true,
        'sanitize_callback' => 'neonews_sanitize_checkbox',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'neonews_show_breaking_news', array(
        'label'       => esc_html__( 'Breaking News Ticker', 'neonews' ),
        'description' => esc_html__( 'Also enable in NewsPulse → Header.', 'neonews' ),
        'section'     => 'neonews_theme_options',
        'type'        => 'checkbox',
    ) );

    $wp_customize->add_setting( 'neonews_show_featured_carousel', array(
        'default'           => true,
        'sanitize_callback' => 'neonews_sanitize_checkbox',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'neonews_show_featured_carousel', array(
        'label'   => esc_html__( 'Featured Carousel on Homepage', 'neonews' ),
        'section' => 'neonews_theme_options',
        'type'    => 'checkbox',
    ) );

    $wp_customize->add_setting( 'neonews_posts_per_row', array(
        'default'           => 3,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'neonews_posts_per_row', array(
        'label'   => esc_html__( 'Posts Per Row (Desktop)', 'neonews' ),
        'section' => 'neonews_theme_options',
        'type'    => 'select',
        'choices' => array( 2 => '2', 3 => '3', 4 => '4' ),
    ) );

    $wp_customize->add_section( 'neonews_typography', array(
        'title'    => esc_html__( 'Typography', 'neonews' ),
        'panel'    => 'neonews_panel',
        'priority' => 20,
    ) );

    $wp_customize->add_setting( 'neonews_font_body', array(
        'default'           => $defaults['font_body'],
        'sanitize_callback' => 'neonews_sanitize_font_choice',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'neonews_font_body', array(
        'label'   => esc_html__( 'Body Font', 'neonews' ),
        'section' => 'neonews_typography',
        'type'    => 'select',
        'choices' => $fonts,
    ) );

    $wp_customize->add_setting( 'neonews_font_heading', array(
        'default'           => $defaults['font_heading'],
        'sanitize_callback' => 'neonews_sanitize_font_choice',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'neonews_font_heading', array(
        'label'   => esc_html__( 'Heading Font', 'neonews' ),
        'section' => 'neonews_typography',
        'type'    => 'select',
        'choices' => $fonts,
    ) );

    $wp_customize->add_section( 'neonews_colors', array(
        'title'    => esc_html__( 'Colors (Light Mode)', 'neonews' ),
        'panel'    => 'neonews_panel',
        'priority' => 30,
    ) );

    $color_controls = array(
        'primary_color'   => __( 'Primary / Brand', 'neonews' ),
        'secondary_color' => __( 'Secondary / Dark text', 'neonews' ),
        'accent_color'    => __( 'Accent', 'neonews' ),
        'bg_primary'      => __( 'Background', 'neonews' ),
        'bg_secondary'    => __( 'Background secondary', 'neonews' ),
        'bg_tertiary'     => __( 'Background tertiary', 'neonews' ),
        'text_primary'    => __( 'Text primary', 'neonews' ),
        'text_secondary'  => __( 'Text secondary', 'neonews' ),
        'text_muted'      => __( 'Text muted', 'neonews' ),
        'border_color'    => __( 'Border color', 'neonews' ),
    );

    foreach ( $color_controls as $key => $label ) {
        $wp_customize->add_setting( 'neonews_' . $key, array(
            'default'           => $defaults[ $key ],
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'refresh',
        ) );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'neonews_' . $key, array(
            'label'   => $label,
            'section' => 'neonews_colors',
        ) ) );
    }

    $wp_customize->add_section( 'neonews_dark_colors', array(
        'title'    => esc_html__( 'Colors (Dark Mode)', 'neonews' ),
        'panel'    => 'neonews_panel',
        'priority' => 35,
    ) );

    $dark_controls = array(
        'dark_bg_primary'     => __( 'Background', 'neonews' ),
        'dark_bg_secondary'   => __( 'Background secondary', 'neonews' ),
        'dark_text_primary'   => __( 'Text primary', 'neonews' ),
        'dark_text_secondary' => __( 'Text secondary', 'neonews' ),
    );
    foreach ( $dark_controls as $key => $label ) {
        $wp_customize->add_setting( 'neonews_' . $key, array(
            'default'           => $defaults[ $key ],
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'refresh',
        ) );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'neonews_' . $key, array(
            'label'   => $label,
            'section' => 'neonews_dark_colors',
        ) ) );
    }

    $wp_customize->add_section( 'neonews_layout', array(
        'title'    => esc_html__( 'Layout', 'neonews' ),
        'panel'    => 'neonews_panel',
        'priority' => 40,
    ) );

    $wp_customize->add_setting( 'neonews_radius_lg', array(
        'default'           => $defaults['radius_lg'],
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'neonews_radius_lg', array(
        'label'   => esc_html__( 'Large border radius (px)', 'neonews' ),
        'section' => 'neonews_layout',
        'type'    => 'number',
        'input_attrs' => array( 'min' => 0, 'max' => 40 ),
    ) );

    $wp_customize->add_setting( 'neonews_radius_xl', array(
        'default'           => $defaults['radius_xl'],
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'neonews_radius_xl', array(
        'label'   => esc_html__( 'Extra-large border radius (px)', 'neonews' ),
        'section' => 'neonews_layout',
        'type'    => 'number',
        'input_attrs' => array( 'min' => 0, 'max' => 48 ),
    ) );

    $wp_customize->add_section( 'neonews_footer', array(
        'title'    => esc_html__( 'Footer Copyright', 'neonews' ),
        'panel'    => 'neonews_panel',
        'priority' => 50,
    ) );

    $wp_customize->add_setting( 'neonews_footer_text', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'neonews_footer_text', array(
        'label'       => esc_html__( 'Footer Copyright Text', 'neonews' ),
        'description' => esc_html__( 'Leave empty for default. HTML allowed.', 'neonews' ),
        'section'     => 'neonews_footer',
        'type'        => 'textarea',
    ) );
}
add_action( 'customize_register', 'neonews_customize_register' );

/**
 * Render the site title for selective refresh.
 */
function neonews_customize_partial_blogname() {
    bloginfo( 'name' );
}

/**
 * Sanitize checkbox.
 *
 * @param mixed $checked Value.
 * @return bool
 */
function neonews_sanitize_checkbox( $checked ) {
    return ( isset( $checked ) && true === $checked );
}

/**
 * Sanitize theme mode.
 *
 * @param string $mode Mode.
 * @return string
 */
function neonews_sanitize_theme_mode( $mode ) {
    return in_array( $mode, array( 'light', 'dark' ), true ) ? $mode : 'light';
}

/**
 * Sanitize font choice.
 *
 * @param string $font Font name.
 * @return string
 */
function neonews_sanitize_font_choice( $font ) {
    $choices = neonews_get_font_choices();
    return isset( $choices[ $font ] ) ? $font : 'Inter';
}

/**
 * Enqueue Customizer preview script.
 */
function neonews_customize_preview_js() {
    wp_enqueue_script(
        'neonews-customizer',
        NEONEWS_THEME_URI . '/js/customizer.js',
        array( 'customize-preview' ),
        NEONEWS_THEME_VERSION,
        true
    );
}
add_action( 'customize_preview_init', 'neonews_customize_preview_js' );
