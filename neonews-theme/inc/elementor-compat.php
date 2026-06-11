<?php
/**
 * Elementor Compatibility
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check if Elementor is active
 */
function neonews_is_elementor_active() {
    return did_action( 'elementor/loaded' );
}

/**
 * Check if current page is built with Elementor
 */
function neonews_is_elementor_page() {
    if ( ! neonews_is_elementor_active() ) {
        return false;
    }

    if ( ! class_exists( '\Elementor\Plugin' ) ) {
        return false;
    }

    $post_id = get_the_ID();
    if ( ! $post_id ) {
        return false;
    }

    return \Elementor\Plugin::$instance->documents->get( $post_id )->is_built_with_elementor();
}

/**
 * Add Elementor-specific body class
 */
function neonews_elementor_body_class( $classes ) {
    if ( neonews_is_elementor_page() ) {
        $classes[] = 'elementor-page';
    }
    return $classes;
}
add_filter( 'body_class', 'neonews_elementor_body_class' );

/**
 * Register Elementor widget categories
 */
function neonews_elementor_categories( $elements_manager ) {
    $elements_manager->add_category(
        'neonews',
        array(
            'title' => esc_html__( 'NeoNews', 'neonews' ),
            'icon'  => 'fa fa-newspaper-o',
        )
    );
}
add_action( 'elementor/elements/categories_registered', 'neonews_elementor_categories' );

/**
 * Enqueue styles for Elementor editor
 */
function neonews_elementor_editor_styles() {
    wp_enqueue_style(
        'neonews-elementor-editor',
        NEONEWS_THEME_URI . '/css/elementor-editor.css',
        array(),
        NEONEWS_THEME_VERSION
    );
}
add_action( 'elementor/editor/after_enqueue_styles', 'neonews_elementor_editor_styles' );

/**
 * Add theme colors to Elementor
 */
function neonews_elementor_kit_settings( $kit ) {
    $kit_settings = $kit->get_settings();

    if ( empty( $kit_settings['custom_colors'] ) ) {
        $kit->update_settings( array(
            'custom_colors' => array(
                array(
                    '_id'   => 'neonews_primary',
                    'title' => esc_html__( 'NeoNews Primary', 'neonews' ),
                    'color' => '#e63946',
                ),
                array(
                    '_id'   => 'neonews_secondary',
                    'title' => esc_html__( 'NeoNews Secondary', 'neonews' ),
                    'color' => '#1d3557',
                ),
                array(
                    '_id'   => 'neonews_accent',
                    'title' => esc_html__( 'NeoNews Accent', 'neonews' ),
                    'color' => '#457b9d',
                ),
            ),
        ) );
    }
}

/**
 * Ensure proper content width for Elementor
 */
function neonews_elementor_content_width() {
    if ( neonews_is_elementor_page() ) {
        $GLOBALS['content_width'] = 1200;
    }
}
add_action( 'template_redirect', 'neonews_elementor_content_width' );

/**
 * Disable theme header/footer when Elementor Theme Builder is active
 */
function neonews_elementor_theme_do_location( $location ) {
    if ( ! neonews_is_elementor_active() ) {
        return false;
    }

    if ( ! class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
        return false;
    }

    return \ElementorPro\Modules\ThemeBuilder\Module::instance()->get_locations_manager()->do_location( $location );
}

/**
 * Add page template options
 */
function neonews_elementor_page_templates( $templates ) {
    $templates['templates/template-fullwidth.php'] = esc_html__( 'NeoNews Full Width', 'neonews' );
    return $templates;
}
add_filter( 'theme_page_templates', 'neonews_elementor_page_templates' );

/**
 * Add compatibility with Elementor Pro Theme Builder
 */
function neonews_register_elementor_locations( $elementor_theme_manager ) {
    $elementor_theme_manager->register_all_core_location();
    
    $elementor_theme_manager->register_location(
        'header',
        array(
            'label'          => esc_html__( 'Header', 'neonews' ),
            'multiple'       => false,
            'edit_in_content'=> true,
        )
    );
    
    $elementor_theme_manager->register_location(
        'footer',
        array(
            'label'          => esc_html__( 'Footer', 'neonews' ),
            'multiple'       => false,
            'edit_in_content'=> true,
        )
    );
    
    $elementor_theme_manager->register_location(
        'single',
        array(
            'label'          => esc_html__( 'Single Post', 'neonews' ),
            'multiple'       => false,
            'edit_in_content'=> true,
        )
    );
    
    $elementor_theme_manager->register_location(
        'archive',
        array(
            'label'          => esc_html__( 'Archive', 'neonews' ),
            'multiple'       => false,
            'edit_in_content'=> true,
        )
    );
}

/**
 * Support for Elementor in customizer
 */
function neonews_elementor_customizer_support() {
    if ( neonews_is_elementor_active() && is_customize_preview() ) {
        add_filter( 'elementor/frontend/builder_content_data', function( $data ) {
            return $data;
        }, 10, 2 );
    }
}
add_action( 'customize_preview_init', 'neonews_elementor_customizer_support' );
