<?php
/**
 * NeoNews Theme Functions
 *
 * @package NeoNews
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NEONEWS_THEME_VERSION', '1.6.9' );
define( 'NEONEWS_THEME_DIR', get_template_directory() );
define( 'NEONEWS_THEME_URI', get_template_directory_uri() );

/**
 * Theme Setup
 */
function neonews_theme_setup() {
    load_theme_textdomain( 'neonews', NEONEWS_THEME_DIR . '/languages' );

    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ) );
    add_theme_support( 'custom-background', array(
        'default-color' => 'ffffff',
    ) );
    add_theme_support( 'editor-styles' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );

    add_image_size( 'neonews-featured', 1200, 675, true );
    add_image_size( 'neonews-card', 600, 400, true );
    add_image_size( 'neonews-thumbnail', 150, 150, true );

    register_nav_menus( array(
        'primary'   => esc_html__( 'Primary Menu', 'neonews' ),
        'footer'    => esc_html__( 'Footer Menu', 'neonews' ),
        'mobile'    => esc_html__( 'Mobile Menu', 'neonews' ),
    ) );
}
add_action( 'after_setup_theme', 'neonews_theme_setup' );

/**
 * Set content width
 */
function neonews_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'neonews_content_width', 800 );
}
add_action( 'after_setup_theme', 'neonews_content_width', 0 );

/**
 * Register widget areas
 */
function neonews_widgets_init() {
    register_sidebar( array(
        'name'          => esc_html__( 'Sidebar', 'neonews' ),
        'id'            => 'sidebar-1',
        'description'   => esc_html__( 'Add widgets here for the main sidebar.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="nn-widget widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="nn-widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Header Widget Area', 'neonews' ),
        'id'            => 'header-widget',
        'description'   => esc_html__( 'Add widgets here for the header area.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="header-widget widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="header-widget-title">',
        'after_title'   => '</h4>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Homepage Section 1', 'neonews' ),
        'id'            => 'homepage-1',
        'description'   => esc_html__( 'First homepage widget section.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="homepage-widget widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="nn-section-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Homepage Section 2', 'neonews' ),
        'id'            => 'homepage-2',
        'description'   => esc_html__( 'Second homepage widget section.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="homepage-widget widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="nn-section-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Homepage Section 3', 'neonews' ),
        'id'            => 'homepage-3',
        'description'   => esc_html__( 'Third homepage widget section.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="homepage-widget widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="nn-section-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Footer Column 1', 'neonews' ),
        'id'            => 'footer-1',
        'description'   => esc_html__( 'First footer widget column.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="footer-widget widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="nn-footer-widget-title">',
        'after_title'   => '</h4>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Footer Column 2', 'neonews' ),
        'id'            => 'footer-2',
        'description'   => esc_html__( 'Second footer widget column.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="footer-widget widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="nn-footer-widget-title">',
        'after_title'   => '</h4>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Footer Column 3', 'neonews' ),
        'id'            => 'footer-3',
        'description'   => esc_html__( 'Third footer widget column.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="footer-widget widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="nn-footer-widget-title">',
        'after_title'   => '</h4>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Footer Column 4', 'neonews' ),
        'id'            => 'footer-4',
        'description'   => esc_html__( 'Fourth footer widget column.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="footer-widget widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="nn-footer-widget-title">',
        'after_title'   => '</h4>',
    ) );
}
add_action( 'widgets_init', 'neonews_widgets_init' );

/**
 * Enqueue scripts and styles
 */
function neonews_scripts() {
    $body_font    = neonews_get_design_token( 'font_body' );
    $heading_font = neonews_get_design_token( 'font_heading' );
    $use_system   = function_exists( 'neonews_perf_enabled' ) && neonews_perf_enabled() && neonews_perf_option( 'perf_system_fonts', false );

    if ( ! $use_system ) {
        $fonts_url = ( function_exists( 'neonews_perf_enabled' ) && neonews_perf_enabled() )
            ? neonews_build_google_fonts_url_lite( $body_font, $heading_font )
            : neonews_build_google_fonts_url( $body_font, $heading_font );

        wp_enqueue_style(
            'neonews-fonts',
            $fonts_url,
            array(),
            null
        );
    }

    $style_deps = $use_system ? array() : array( 'neonews-fonts' );

    wp_enqueue_style(
        'neonews-style',
        get_stylesheet_uri(),
        $style_deps,
        NEONEWS_THEME_VERSION
    );

    wp_enqueue_style(
        'neonews-main',
        NEONEWS_THEME_URI . '/css/main.css',
        array( 'neonews-style' ),
        NEONEWS_THEME_VERSION
    );

    $main_deps = array();
    if ( class_exists( 'NeoNews_Captcha' ) && defined( 'NEONEWS_CORE_URL' ) && defined( 'NEONEWS_CORE_VERSION' ) ) {
        wp_enqueue_script(
            'neonews-captcha',
            NEONEWS_CORE_URL . 'public/js/captcha.js',
            array(),
            NEONEWS_CORE_VERSION,
            true
        );
        $main_deps[] = 'neonews-captcha';

        if ( ! function_exists( 'neonews_should_lazy_load_captcha' ) || ! neonews_should_lazy_load_captcha() ) {
            NeoNews_Captcha::enqueue_scripts();
        }
    }

    wp_enqueue_script(
        'neonews-main',
        NEONEWS_THEME_URI . '/js/main.js',
        $main_deps,
        NEONEWS_THEME_VERSION,
        true
    );

    $captcha_config = class_exists( 'NeoNews_Captcha' ) ? NeoNews_Captcha::get_js_config() : array( 'enabled' => false );
    if ( function_exists( 'neonews_should_lazy_load_captcha' ) && neonews_should_lazy_load_captcha() && ! empty( $captcha_config['enabled'] ) ) {
        $captcha_config['lazyLoad']  = true;
        $captcha_config['scriptUrl'] = neonews_get_captcha_script_url();
        $captcha_config['helperUrl'] = NEONEWS_CORE_URL . 'public/js/captcha.js';
    }

    wp_localize_script(
        'neonews-main',
        'neonewsData',
        array(
            'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'neonews_nonce' ),
            'homeUrl'  => home_url(),
            'themeUrl' => NEONEWS_THEME_URI,
            'captcha'  => $captcha_config,
            'perf'     => array(
                'lite'         => function_exists( 'neonews_perf_enabled' ) && neonews_perf_enabled(),
                'reduceMotion' => function_exists( 'neonews_perf_option' ) && neonews_perf_option( 'perf_reduce_animations', true ),
                'lazyCaptcha'  => function_exists( 'neonews_should_lazy_load_captcha' ) && neonews_should_lazy_load_captcha(),
            ),
            'i18n'     => array(
                'loading' => esc_html__( 'Loading...', 'neonews' ),
                'error'   => esc_html__( 'An error occurred.', 'neonews' ),
                'search'  => esc_html__( 'Search...', 'neonews' ),
                'captcha' => esc_html__( 'Please complete the CAPTCHA verification.', 'neonews' ),
                'signUp'  => esc_html__( 'Sign up', 'neonews' ),
                'logoutConfirm' => esc_html__( 'Are you sure you want to log out?', 'neonews' ),
            ),
            'consent'  => neonews_get_consent_js_config(),
        )
    );

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'neonews_scripts' );

/**
 * Add custom body classes
 */
function neonews_body_classes( $classes ) {
    if ( ! is_singular() ) {
        $classes[] = 'hfeed';
    }

    if ( is_singular() && ! has_post_thumbnail() ) {
        $classes[] = 'no-featured-image';
    }

    $theme_mode = neonews_get_theme_mode();
    $classes[] = 'theme-' . $theme_mode;

    if ( function_exists( 'neonews_is_ad_zone_enabled' ) && neonews_is_ad_zone_enabled( 'ad-mobile' ) && is_active_sidebar( 'ad-mobile' ) ) {
        $classes[] = 'nn-has-mobile-ad';
    }

    return $classes;
}
add_filter( 'body_class', 'neonews_body_classes' );

/**
 * Get theme mode (dark/light)
 */
function neonews_get_theme_mode() {
    $default_mode = get_theme_mod( 'neonews_default_theme_mode', 'light' );

    if ( is_user_logged_in() ) {
        $user_mode = get_user_meta( get_current_user_id(), 'neonews_theme_mode', true );
        if ( $user_mode ) {
            return sanitize_key( $user_mode );
        }
    }

    if ( isset( $_COOKIE['neonews_theme'] ) ) {
        return sanitize_key( wp_unslash( $_COOKIE['neonews_theme'] ) );
    }

    return sanitize_key( $default_mode );
}

/**
 * AJAX handler for theme mode toggle
 */
function neonews_toggle_theme_mode() {
    check_ajax_referer( 'neonews_nonce', 'nonce' );

    $mode = isset( $_POST['mode'] ) ? sanitize_key( $_POST['mode'] ) : 'light';
    
    if ( ! in_array( $mode, array( 'light', 'dark' ), true ) ) {
        $mode = 'light';
    }

    if ( is_user_logged_in() ) {
        update_user_meta( get_current_user_id(), 'neonews_theme_mode', $mode );
    }

    wp_send_json_success( array( 'mode' => $mode ) );
}
add_action( 'wp_ajax_neonews_toggle_theme', 'neonews_toggle_theme_mode' );
add_action( 'wp_ajax_nopriv_neonews_toggle_theme', 'neonews_toggle_theme_mode' );

/**
 * Get breaking news posts
 */
function neonews_get_breaking_news( $limit = 5 ) {
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => absint( $limit ),
        'meta_query'     => array(
            array(
                'key'     => '_neonews_breaking_news',
                'value'   => '1',
                'compare' => '=',
            ),
        ),
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    );

    return new WP_Query( $args );
}

/**
 * Get featured posts
 */
function neonews_get_featured_posts( $limit = 5 ) {
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => absint( $limit ),
        'meta_query'     => array(
            array(
                'key'     => '_neonews_featured_post',
                'value'   => '1',
                'compare' => '=',
            ),
        ),
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    );

    return new WP_Query( $args );
}

/**
 * Get trending posts based on view count
 */
function neonews_get_trending_posts( $limit = 5, $days = 7 ) {
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => absint( $limit ),
        'meta_key'       => '_neonews_post_views',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'date_query'     => array(
            array(
                'after' => absint( $days ) . ' days ago',
            ),
        ),
        'no_found_rows'  => true,
    );

    return new WP_Query( $args );
}

/**
 * Get post views count
 */
function neonews_get_post_views( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    $views = get_post_meta( absint( $post_id ), '_neonews_post_views', true );
    return absint( $views );
}

/**
 * Format post views for display
 */
function neonews_format_views( $views ) {
    $views = absint( $views );
    if ( $views >= 1000000 ) {
        return round( $views / 1000000, 1 ) . 'M';
    } elseif ( $views >= 1000 ) {
        return round( $views / 1000, 1 ) . 'K';
    }
    return number_format_i18n( $views );
}

/**
 * Get reading time estimate
 */
function neonews_get_reading_time( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    
    $content = get_post_field( 'post_content', $post_id );
    $word_count = str_word_count( wp_strip_all_tags( $content ) );
    $wpm = absint( neonews_get_setting( 'reading_wpm', 200 ) );
    $wpm = $wpm > 0 ? $wpm : 200;
    $reading_time = ceil( $word_count / $wpm );

    return max( 1, (int) $reading_time );
}

/**
 * Add Schema.org NewsArticle markup
 */
function neonews_schema_markup() {
    if ( ! is_singular( 'post' ) ) {
        return;
    }

    global $post;

    $schema = array(
        '@context'         => 'https://schema.org',
        '@type'            => 'NewsArticle',
        'mainEntityOfPage' => array(
            '@type' => 'WebPage',
            '@id'   => get_permalink( $post->ID ),
        ),
        'headline'         => get_the_title( $post->ID ),
        'description'      => wp_trim_words( get_the_excerpt( $post->ID ), 30, '' ),
        'datePublished'    => get_the_date( 'c', $post->ID ),
        'dateModified'     => get_the_modified_date( 'c', $post->ID ),
        'author'           => array(
            '@type' => 'Person',
            'name'  => get_the_author_meta( 'display_name', $post->post_author ),
            'url'   => get_author_posts_url( $post->post_author ),
        ),
        'publisher'        => array(
            '@type' => 'Organization',
            'name'  => get_bloginfo( 'name' ),
            'logo'  => array(
                '@type' => 'ImageObject',
                'url'   => esc_url( get_site_icon_url( 512 ) ),
            ),
        ),
    );

    if ( has_post_thumbnail( $post->ID ) ) {
        $image_id = get_post_thumbnail_id( $post->ID );
        $image_data = wp_get_attachment_image_src( $image_id, 'full' );
        if ( $image_data ) {
            $schema['image'] = array(
                '@type'  => 'ImageObject',
                'url'    => esc_url( $image_data[0] ),
                'width'  => absint( $image_data[1] ),
                'height' => absint( $image_data[2] ),
            );
        }
    }

    printf(
        '<script type="application/ld+json">%s</script>' . "\n",
        wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
    );
}
add_action( 'wp_head', 'neonews_schema_markup' );

/**
 * Custom excerpt length
 */
function neonews_excerpt_length( $length ) {
    return absint( neonews_get_setting( 'excerpt_length', 25 ) );
}
add_filter( 'excerpt_length', 'neonews_excerpt_length' );

/**
 * Custom excerpt more
 */
function neonews_excerpt_more( $more ) {
    return '&hellip;';
}
add_filter( 'excerpt_more', 'neonews_excerpt_more' );

/**
 * Pagination helper
 */
function neonews_pagination( $query = null ) {
    global $wp_query;
    
    $pagination_query = $query ? $query : $wp_query;
    
    $big = 999999999;
    
    $pages = paginate_links( array(
        'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
        'format'    => '?paged=%#%',
        'current'   => max( 1, get_query_var( 'paged' ) ),
        'total'     => $pagination_query->max_num_pages,
        'type'      => 'array',
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
    ) );

    if ( is_array( $pages ) ) {
        echo '<nav class="nn-pagination" aria-label="' . esc_attr__( 'Posts navigation', 'neonews' ) . '">';
        foreach ( $pages as $page ) {
            echo wp_kses_post( $page );
        }
        echo '</nav>';
    }
}

/**
 * Custom comment callback
 */
function neonews_comment_callback( $comment, $args, $depth ) {
    $tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
    ?>
    <<?php echo esc_attr( $tag ); ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( 'nn-comment' ); ?>>
        <article class="nn-comment-body">
            <header class="nn-comment-header">
                <div class="nn-comment-avatar">
                    <?php echo get_avatar( $comment, 48, '', '', array( 'class' => 'avatar' ) ); ?>
                </div>
                <div class="nn-comment-meta">
                    <span class="nn-comment-author"><?php echo esc_html( get_comment_author() ); ?></span>
                    <time class="nn-comment-date" datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>">
                        <?php
                        printf(
                            /* translators: 1: comment date, 2: comment time */
                            esc_html__( '%1$s at %2$s', 'neonews' ),
                            esc_html( get_comment_date() ),
                            esc_html( get_comment_time() )
                        );
                        ?>
                    </time>
                </div>
            </header>

            <div class="nn-comment-content">
                <?php if ( '0' === $comment->comment_approved ) : ?>
                    <p class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'neonews' ); ?></p>
                <?php endif; ?>
                <?php comment_text(); ?>
            </div>

            <footer class="nn-comment-footer">
                <?php
                comment_reply_link( array_merge( $args, array(
                    'depth'     => $depth,
                    'max_depth' => $args['max_depth'],
                    'class'     => 'nn-comment-reply-link',
                ) ) );

                edit_comment_link( esc_html__( 'Edit', 'neonews' ), ' <span class="nn-comment-edit">', '</span>' );
                ?>
            </footer>
        </article>
    <?php
}

/**
 * Add Elementor support
 */
function neonews_elementor_support() {
    if ( did_action( 'elementor/loaded' ) ) {
        add_theme_support( 'elementor' );
        add_post_type_support( 'page', 'elementor' );
    }
}
add_action( 'after_setup_theme', 'neonews_elementor_support' );

/**
 * Register Elementor locations
 */
function neonews_register_elementor_locations( $elementor_theme_manager ) {
    $elementor_theme_manager->register_location( 'header' );
    $elementor_theme_manager->register_location( 'footer' );
    $elementor_theme_manager->register_location( 'single' );
    $elementor_theme_manager->register_location( 'archive' );
}
add_action( 'elementor/theme/register_locations', 'neonews_register_elementor_locations' );

/**
 * Include required files
 */
require_once NEONEWS_THEME_DIR . '/inc/settings-helpers.php';
require_once NEONEWS_THEME_DIR . '/inc/performance.php';
require_once NEONEWS_THEME_DIR . '/inc/customizer.php';
require_once NEONEWS_THEME_DIR . '/inc/dynamic-styles.php';
require_once NEONEWS_THEME_DIR . '/inc/template-tags.php';
require_once NEONEWS_THEME_DIR . '/inc/template-functions.php';
require_once NEONEWS_THEME_DIR . '/inc/home-helpers.php';
require_once NEONEWS_THEME_DIR . '/inc/social-links.php';
require_once NEONEWS_THEME_DIR . '/inc/auth-registration.php';
require_once NEONEWS_THEME_DIR . '/inc/admin-settings-tabs.php';
require_once NEONEWS_THEME_DIR . '/inc/admin-settings.php';
require_once NEONEWS_THEME_DIR . '/inc/admin-performance-panel.php';
require_once NEONEWS_THEME_DIR . '/inc/legal-pages.php';
require_once NEONEWS_THEME_DIR . '/inc/company-pages.php';
require_once NEONEWS_THEME_DIR . '/inc/nav-menus.php';
require_once NEONEWS_THEME_DIR . '/inc/cookie-consent.php';
require_once NEONEWS_THEME_DIR . '/inc/adsense.php';
require_once NEONEWS_THEME_DIR . '/inc/weather.php';
require_once NEONEWS_THEME_DIR . '/inc/user-avatar.php';
require_once NEONEWS_THEME_DIR . '/inc/author-photo.php';
require_once NEONEWS_THEME_DIR . '/inc/theme-activation.php';
require_once NEONEWS_THEME_DIR . '/inc/newspulse-features.php';

/**
 * Conditionally include Elementor compatibility
 */
if ( did_action( 'elementor/loaded' ) ) {
    require_once NEONEWS_THEME_DIR . '/inc/elementor-compat.php';
}
