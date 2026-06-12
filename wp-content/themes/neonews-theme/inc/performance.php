<?php
/**
 * Frontend performance — lightweight loading defaults.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Is lightweight performance mode enabled?
 *
 * @return bool
 */
function neonews_perf_enabled() {
    return neonews_is_feature_enabled( 'perf_lightweight_mode' );
}

/**
 * Single performance toggle.
 *
 * @param string $key Setting key without perf_ prefix in logic — full key in options.
 * @param bool   $default Default when perf mode off.
 * @return bool
 */
function neonews_perf_option( $key, $default = true ) {
    if ( ! neonews_perf_enabled() ) {
        return false;
    }
    $settings = neonews_get_settings();
    if ( array_key_exists( $key, $settings ) ) {
        return ! empty( $settings[ $key ] );
    }
    return $default;
}

/**
 * Bootstrap performance hooks.
 */
function neonews_performance_init() {
    if ( ! neonews_perf_enabled() ) {
        return;
    }

    if ( neonews_perf_option( 'perf_disable_emojis', true ) ) {
        neonews_perf_disable_emojis();
    }

    if ( neonews_perf_option( 'perf_disable_embeds', true ) ) {
        add_action( 'wp_footer', 'neonews_perf_disable_embeds' );
    }

    if ( neonews_perf_option( 'perf_disable_dashicons', true ) ) {
        add_action( 'wp_enqueue_scripts', 'neonews_perf_disable_dashicons', 100 );
    }

    if ( neonews_perf_option( 'perf_disable_block_css', true ) ) {
        add_action( 'wp_enqueue_scripts', 'neonews_perf_dequeue_block_styles', 100 );
    }

    if ( neonews_perf_option( 'perf_disable_heartbeat', true ) ) {
        add_action( 'init', 'neonews_perf_disable_heartbeat', 1 );
    }

    add_action( 'init', 'neonews_perf_disable_xmlrpc' );
    add_filter( 'wp_resource_hints', 'neonews_perf_resource_hints', 10, 2 );
    add_filter( 'script_loader_tag', 'neonews_perf_script_loader_tag', 10, 3 );
    add_filter( 'style_loader_tag', 'neonews_perf_style_loader_tag', 10, 4 );
    add_filter( 'wp_get_attachment_image_attributes', 'neonews_perf_image_attributes', 10, 3 );
    add_filter( 'post_thumbnail_html', 'neonews_perf_lazy_thumbnail_html', 10, 5 );
    add_action( 'wp_head', 'neonews_perf_preload_assets', 2 );
    add_filter( 'body_class', 'neonews_perf_body_class' );
    remove_action( 'wp_head', 'neonews_pingback_header' );
}
add_action( 'after_setup_theme', 'neonews_performance_init', 20 );

/**
 * Disable emoji scripts/styles.
 */
function neonews_perf_disable_emojis() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    add_filter( 'emoji_svg_url', '__return_false' );
}

/**
 * Disable oEmbed / wp-embed.js.
 */
function neonews_perf_disable_embeds() {
    wp_deregister_script( 'wp-embed' );
}

/**
 * Drop dashicons for guests.
 */
function neonews_perf_disable_dashicons() {
    if ( ! is_user_logged_in() ) {
        wp_deregister_style( 'dashicons' );
    }
}

/**
 * Dequeue block library CSS on frontend when not needed.
 */
function neonews_perf_dequeue_block_styles() {
    if ( is_admin() ) {
        return;
    }
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'wc-blocks-style' );
    wp_dequeue_style( 'global-styles' );
}

/**
 * Disable Heartbeat on the front end.
 */
function neonews_perf_disable_heartbeat() {
    if ( ! is_admin() ) {
        wp_deregister_script( 'heartbeat' );
    }
}

/**
 * Disable XML-RPC.
 */
function neonews_perf_disable_xmlrpc() {
    add_filter( 'xmlrpc_enabled', '__return_false' );
}

/**
 * Extra resource hints.
 *
 * @param array  $urls          URLs.
 * @param string $relation_type Relation type.
 * @return array
 */
function neonews_perf_resource_hints( $urls, $relation_type ) {
    if ( 'preconnect' === $relation_type && ! neonews_perf_option( 'perf_system_fonts', false ) ) {
        $urls[] = array(
            'href' => 'https://fonts.googleapis.com',
        );
        $urls[] = array(
            'href'        => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous',
        );
    }
    return $urls;
}

/**
 * Defer theme and plugin scripts.
 *
 * @param string $tag    Script tag.
 * @param string $handle Handle.
 * @param string $src    Source.
 * @return string
 */
function neonews_perf_script_loader_tag( $tag, $handle, $src ) {
    $defer = array(
        'neonews-main',
        'neonews-captcha',
        'neonews-core-public',
    );

    if ( in_array( $handle, $defer, true ) && false === strpos( $tag, ' defer' ) ) {
        return str_replace( ' src', ' defer src', $tag );
    }

    if ( neonews_perf_option( 'perf_lazy_captcha', true ) ) {
        $async = array( 'cloudflare-turnstile', 'google-recaptcha', 'google-recaptcha-v3', 'hcaptcha' );
        if ( in_array( $handle, $async, true ) && false === strpos( $tag, ' async' ) ) {
            return str_replace( ' src', ' async defer src', $tag );
        }
    }

    return $tag;
}

/**
 * Load non-critical CSS without blocking render.
 *
 * @param string $html   Link tag.
 * @param string $handle Handle.
 * @param string $href   Href.
 * @param string $media  Media.
 * @return string
 */
function neonews_perf_style_loader_tag( $html, $handle, $href, $media ) {
    $non_critical = array( 'neonews-newspulse', 'neonews-fonts' );

    if ( ! in_array( $handle, $non_critical, true ) ) {
        return $html;
    }

    $preload = str_replace(
        "rel='stylesheet'",
        "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"",
        $html
    );
    $preload = str_replace(
        'rel="stylesheet"',
        'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"',
        $preload
    );

    return $preload . '<noscript>' . $html . '</noscript>';
}

/**
 * Default lazy/async on attachment images.
 *
 * @param array        $attr       Attributes.
 * @param WP_Post      $attachment Attachment.
 * @param string|array $size       Size.
 * @return array
 */
function neonews_perf_image_attributes( $attr, $attachment, $size ) {
    if ( empty( $attr['loading'] ) ) {
        $attr['loading'] = 'lazy';
    }
    if ( empty( $attr['decoding'] ) ) {
        $attr['decoding'] = 'async';
    }
    return $attr;
}

/**
 * Lazy-load images inside post content.
 *
 * @param string $content Content HTML.
 * @return string
 */
function neonews_perf_lazy_content_images( $content ) {
    if ( is_feed() || is_preview() || empty( $content ) ) {
        return $content;
    }

    if ( false === strpos( $content, '<img' ) ) {
        return $content;
    }

    return preg_replace_callback(
        '/<img\b([^>]*?)>/i',
        function ( $matches ) {
            $attrs = $matches[1];
            if ( false !== stripos( $attrs, 'loading=' ) ) {
                return $matches[0];
            }
            if ( false === stripos( $attrs, 'decoding=' ) ) {
                $attrs .= ' decoding="async"';
            }
            return '<img loading="lazy"' . $attrs . '>';
        },
        $content
    );
}

/**
 * Lazy-load thumbnails except LCP candidates.
 *
 * @param string $html              Thumbnail HTML.
 * @param int    $post_id           Post ID.
 * @param int    $post_thumbnail_id Attachment ID.
 * @param string $size              Size.
 * @param array  $attr              Attributes.
 * @return string
 */
function neonews_perf_lazy_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
    if ( empty( $html ) || false !== stripos( $html, 'loading=' ) ) {
        return $html;
    }

    if ( is_singular() && (int) get_queried_object_id() === (int) $post_id && in_array( $size, array( 'neonews-featured', 'full', 'large' ), true ) ) {
        return $html;
    }

    if ( false === stripos( $html, 'decoding=' ) ) {
        $html = str_replace( '<img ', '<img decoding="async" ', $html );
    }

    return str_replace( '<img ', '<img loading="lazy" ', $html );
}

/**
 * Preload key assets for faster first paint.
 */
function neonews_perf_preload_assets() {
    echo '<link rel="preload" href="' . esc_url( get_stylesheet_uri() ) . '" as="style">' . "\n";
}

/**
 * Body classes for JS/CSS toggles.
 *
 * @param array $classes Classes.
 * @return array
 */
function neonews_perf_body_class( $classes ) {
    $classes[] = 'nn-perf-lite';
    if ( neonews_perf_option( 'perf_reduce_animations', true ) ) {
        $classes[] = 'nn-reduce-motion';
    }
    if ( neonews_perf_option( 'perf_system_fonts', false ) ) {
        $classes[] = 'nn-system-fonts';
    }
    return $classes;
}

/**
 * Should NeoNews Core load public CSS/JS?
 *
 * @return bool
 */
function neonews_needs_core_frontend_assets() {
    if ( ! neonews_perf_enabled() || ! neonews_perf_option( 'perf_conditional_core_assets', true ) ) {
        return true;
    }

    if ( is_singular( 'post' ) ) {
        return true;
    }

    if ( is_page() ) {
        $post = get_post();
        if ( $post && has_shortcode( $post->post_content, 'neonews_submit_form' ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Should membership CSS load?
 *
 * @return bool
 */
function neonews_needs_membership_assets() {
    if ( ! neonews_perf_enabled() || ! neonews_perf_option( 'perf_conditional_core_assets', true ) ) {
        return true;
    }

    if ( is_page_template(
        array(
            'templates/template-profile.php',
            'templates/template-account.php',
            'templates/template-exclusive.php',
        )
    ) ) {
        return true;
    }

    return is_singular( 'post' ) || is_home() || is_front_page() || is_archive();
}

/**
 * Should captcha scripts load immediately?
 *
 * @return bool
 */
function neonews_should_lazy_load_captcha() {
    return neonews_perf_enabled() && neonews_perf_option( 'perf_lazy_captcha', true );
}

/**
 * Captcha provider script URL for lazy injection.
 *
 * @return string
 */
function neonews_get_captcha_script_url() {
    if ( ! class_exists( 'NeoNews_Captcha' ) || ! NeoNews_Captcha::is_configured() ) {
        return '';
    }

    $config = NeoNews_Captcha::get_config();
    switch ( $config['captcha_provider'] ) {
        case 'turnstile':
            return 'https://challenges.cloudflare.com/turnstile/v0/api.js';
        case 'recaptcha_v2':
            return 'https://www.google.com/recaptcha/api.js';
        case 'recaptcha_v3':
            return 'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $config['captcha_site_key'] );
        case 'hcaptcha':
            return 'https://js.hcaptcha.com/1/api.js';
        default:
            return '';
    }
}

/**
 * Lighter Google Fonts URL (fewer weights, no italics).
 *
 * @param string $body    Body font.
 * @param string $heading Heading font.
 * @return string
 */
function neonews_build_google_fonts_url_lite( $body, $heading ) {
    $families = array();
    foreach ( array_unique( array( $body, $heading ) ) as $font ) {
        $families[] = 'family=' . rawurlencode( $font ) . ':wght@400;600;700';
    }
    return 'https://fonts.googleapis.com/css2?' . implode( '&', $families ) . '&display=swap';
}

/**
 * Performance audit checks registry.
 *
 * @return array
 */
function neonews_get_performance_checks() {
    return array(
        'perf_lightweight_mode' => array(
            'label'            => __( 'Lightweight mode', 'neonews' ),
            'description'      => __( 'Enables defer JS, lazy images, and CSS preload.', 'neonews' ),
            'savings_kb'       => 0,
            'savings_requests' => 0,
            'score_weight'     => 18,
            'setting'          => 'perf_lightweight_mode',
        ),
        'perf_system_fonts' => array(
            'label'            => __( 'System fonts', 'neonews' ),
            'description'      => __( 'Skips Google Fonts network requests.', 'neonews' ),
            'savings_kb'       => 120,
            'savings_requests' => 2,
            'score_weight'     => 12,
            'setting'          => 'perf_system_fonts',
        ),
        'perf_disable_emojis' => array(
            'label'            => __( 'No emoji scripts', 'neonews' ),
            'description'      => __( 'Removes WordPress emoji JS and CSS.', 'neonews' ),
            'savings_kb'       => 18,
            'savings_requests' => 2,
            'score_weight'     => 6,
            'setting'          => 'perf_disable_emojis',
        ),
        'perf_disable_embeds' => array(
            'label'            => __( 'No wp-embed.js', 'neonews' ),
            'description'      => __( 'Disables oEmbed iframe helper on frontend.', 'neonews' ),
            'savings_kb'       => 6,
            'savings_requests' => 1,
            'score_weight'     => 4,
            'setting'          => 'perf_disable_embeds',
        ),
        'perf_disable_dashicons' => array(
            'label'            => __( 'No dashicons (guests)', 'neonews' ),
            'description'      => __( 'Removes admin icon font for visitors.', 'neonews' ),
            'savings_kb'       => 28,
            'savings_requests' => 1,
            'score_weight'     => 5,
            'setting'          => 'perf_disable_dashicons',
        ),
        'perf_disable_block_css' => array(
            'label'            => __( 'No block editor CSS', 'neonews' ),
            'description'      => __( 'Dequeues block library styles on frontend.', 'neonews' ),
            'savings_kb'       => 45,
            'savings_requests' => 2,
            'score_weight'     => 8,
            'setting'          => 'perf_disable_block_css',
        ),
        'perf_disable_heartbeat' => array(
            'label'            => __( 'No Heartbeat (frontend)', 'neonews' ),
            'description'      => __( 'Stops polling AJAX on public pages.', 'neonews' ),
            'savings_kb'       => 12,
            'savings_requests' => 1,
            'score_weight'     => 5,
            'setting'          => 'perf_disable_heartbeat',
        ),
        'perf_conditional_core_assets' => array(
            'label'            => __( 'Conditional plugin assets', 'neonews' ),
            'description'      => __( 'Core/membership CSS/JS only on posts, home, archives, profile, account, and exclusive pages.', 'neonews' ),
            'savings_kb'       => 14,
            'savings_requests' => 2,
            'score_weight'     => 8,
            'setting'          => 'perf_conditional_core_assets',
        ),
        'perf_lazy_captcha' => array(
            'label'            => __( 'Lazy CAPTCHA loading', 'neonews' ),
            'description'      => __( 'CAPTCHA provider loads on sign-in only.', 'neonews' ),
            'savings_kb'       => 150,
            'savings_requests' => 1,
            'score_weight'     => 10,
            'setting'          => 'perf_lazy_captcha',
            'requires'         => 'captcha_configured',
        ),
        'perf_reduce_animations' => array(
            'label'            => __( 'Reduced motion', 'neonews' ),
            'description'      => __( 'Less scroll animation work on the main thread.', 'neonews' ),
            'savings_kb'       => 0,
            'savings_requests' => 0,
            'score_weight'     => 4,
            'setting'          => 'perf_reduce_animations',
        ),
        'defer_scripts' => array(
            'label'            => __( 'Deferred JavaScript', 'neonews' ),
            'description'      => __( 'Theme scripts load without blocking HTML parse.', 'neonews' ),
            'savings_kb'       => 0,
            'savings_requests' => 0,
            'score_weight'     => 8,
            'requires'         => 'lite_mode',
        ),
        'lazy_images' => array(
            'label'            => __( 'Lazy-loaded images', 'neonews' ),
            'description'      => __( 'Below-fold images defer until needed.', 'neonews' ),
            'savings_kb'       => 200,
            'savings_requests' => 0,
            'score_weight'     => 10,
            'requires'         => 'lite_mode',
        ),
        'css_preload' => array(
            'label'            => __( 'Non-blocking CSS', 'neonews' ),
            'description'      => __( 'NewsPulse styles preload without render block.', 'neonews' ),
            'savings_kb'       => 0,
            'savings_requests' => 0,
            'score_weight'     => 6,
            'requires'         => 'lite_mode',
        ),
        'fonts_lite' => array(
            'label'            => __( 'Optimized Google Fonts', 'neonews' ),
            'description'      => __( 'Fewer font weights (400/600/700).', 'neonews' ),
            'savings_kb'       => 35,
            'savings_requests' => 0,
            'score_weight'     => 6,
            'requires'         => 'google_fonts',
        ),
    );
}

/**
 * Is a performance check currently active?
 *
 * @param string $id   Check ID.
 * @param array  $check Check config.
 * @return bool
 */
function neonews_is_performance_check_active( $id, $check ) {
    if ( ! empty( $check['setting'] ) ) {
        if ( 'perf_lazy_captcha' === $check['setting'] ) {
            if ( ! class_exists( 'NeoNews_Captcha' ) || ! NeoNews_Captcha::is_configured() ) {
                return true;
            }
        }
        return neonews_perf_option( $check['setting'], true );
    }

    switch ( $check['requires'] ?? '' ) {
        case 'lite_mode':
            return neonews_perf_enabled();
        case 'google_fonts':
            return neonews_perf_enabled() && ! neonews_perf_option( 'perf_system_fonts', false );
        case 'captcha_configured':
            return ! class_exists( 'NeoNews_Captcha' ) || ! NeoNews_Captcha::is_configured() || neonews_should_lazy_load_captcha();
        default:
            return false;
    }
}

/**
 * Build performance score report.
 *
 * @return array
 */
function neonews_get_performance_report() {
    $checks      = neonews_get_performance_checks();
    $settings    = neonews_get_settings();
    $score_total = 0;
    $score_max   = 0;
    $saved_kb    = 0;
    $saved_req   = 0;
    $active      = array();
    $recommend   = array();

    foreach ( $checks as $id => $check ) {
        if ( 'perf_lazy_captcha' === $id && ( ! class_exists( 'NeoNews_Captcha' ) || ! NeoNews_Captcha::is_configured() ) ) {
            continue;
        }
        if ( 'fonts_lite' === $id && ( ! neonews_perf_enabled() || neonews_perf_option( 'perf_system_fonts', false ) ) ) {
            continue;
        }

        $score_max += (int) $check['score_weight'];
        $is_active  = neonews_is_performance_check_active( $id, $check );

        if ( $is_active ) {
            $score_total += (int) $check['score_weight'];
            $saved_kb    += (int) $check['savings_kb'];
            $saved_req   += (int) $check['savings_requests'];
            $active[]    = array_merge( $check, array( 'id' => $id ) );
        } elseif ( ! empty( $check['setting'] ) ) {
            $recommend[] = array_merge(
                $check,
                array(
                    'id'          => $id,
                    'potential_kb' => (int) $check['savings_kb'],
                )
            );
        }
    }

    if ( ! empty( $settings['weather_enabled'] ) ) {
        $recommend[] = array(
            'id'          => 'weather_widget',
            'label'       => __( 'Weather widget', 'neonews' ),
            'description' => __( 'Header/sidebar weather triggers extra AJAX on page load.', 'neonews' ),
            'potential_kb' => 5,
            'setting'     => '',
            'manual'      => __( 'Disable under Weather widget below', 'neonews' ),
        );
    }

    if ( ! empty( $settings['analytics_enabled'] ) && ! empty( $settings['google_analytics_id'] ) ) {
        $recommend[] = array(
            'id'          => 'analytics',
            'label'       => __( 'Google Analytics', 'neonews' ),
            'description' => __( 'Analytics loads after consent — OK if required. Consider lighter tag.', 'neonews' ),
            'potential_kb' => 45,
            'setting'     => '',
            'manual'      => __( 'Review in Cookie consent section', 'neonews' ),
        );
    }

    if ( ! neonews_perf_enabled() ) {
        array_unshift(
            $recommend,
            array(
                'id'          => 'enable_lite',
                'label'       => __( 'Enable lightweight mode', 'neonews' ),
                'description' => __( 'Turn on the master performance switch first.', 'neonews' ),
                'potential_kb' => 250,
                'setting'     => 'perf_lightweight_mode',
            )
        );
    }

    if ( class_exists( 'NeoNews_Membership' ) && ! neonews_perf_option( 'perf_conditional_core_assets', true ) ) {
        $recommend[] = array(
            'id'           => 'membership_conditional_assets',
            'label'        => __( 'Membership conditional assets', 'neonews' ),
            'description'  => __( 'Membership CSS/JS (badges, popup, blur lock) loads on fewer pages when conditional assets is enabled.', 'neonews' ),
            'potential_kb' => 14,
            'setting'      => 'perf_conditional_core_assets',
        );
    }

    if ( class_exists( 'NeoNews_Membership' ) && ! empty( NeoNews_Membership::get_settings( 'enable_upgrade_popup' ) ) ) {
        $recommend[] = array(
            'id'           => 'membership_popup_timing',
            'label'        => __( 'Membership upgrade popup', 'neonews' ),
            'description'  => __( 'Popup JS is lightweight and deferred. Increase delay under NewsPulse → Membership → Popup & Home if engagement feels intrusive.', 'neonews' ),
            'potential_kb' => 2,
            'setting'      => '',
            'manual'       => __( 'Tune popup delay in Membership settings', 'neonews' ),
        );
    }

    $recommend[] = array(
        'id'          => 'page_cache',
        'label'       => __( 'Page cache plugin', 'neonews' ),
        'description' => __( 'Use LiteSpeed Cache, WP Rocket, or similar for HTML caching.', 'neonews' ),
        'potential_kb' => 500,
        'setting'     => '',
        'manual'      => __( 'Install at server / plugin level', 'neonews' ),
    );

    $score = $score_max > 0 ? (int) round( ( $score_total / $score_max ) * 100 ) : 0;
    $score = max( 0, min( 100, $score ) );

    $auto_fix_count = 0;
    foreach ( $checks as $id => $check ) {
        if ( empty( $check['setting'] ) ) {
            continue;
        }
        if ( 'perf_lazy_captcha' === $id && ( ! class_exists( 'NeoNews_Captcha' ) || ! NeoNews_Captcha::is_configured() ) ) {
            continue;
        }
        if ( ! neonews_is_performance_check_active( $id, $check ) ) {
            ++$auto_fix_count;
        }
    }

    return array(
        'score'           => $score,
        'grade'           => neonews_get_performance_grade( $score ),
        'grade_label'     => neonews_get_performance_grade_label( $score ),
        'active'          => $active,
        'recommendations' => $recommend,
        'totals'          => array(
            'saved_kb'       => $saved_kb,
            'saved_requests' => $saved_req,
            'tti_estimate'   => neonews_estimate_tti_improvement( $saved_kb, $saved_req ),
        ),
        'auto_fix_count'  => $auto_fix_count,
    );
}

/**
 * Letter grade from score.
 *
 * @param int $score Score 0-100.
 * @return string
 */
function neonews_get_performance_grade( $score ) {
    if ( $score >= 95 ) {
        return 'A+';
    }
    if ( $score >= 85 ) {
        return 'A';
    }
    if ( $score >= 75 ) {
        return 'B';
    }
    if ( $score >= 60 ) {
        return 'C';
    }
    if ( $score >= 45 ) {
        return 'D';
    }
    return 'F';
}

/**
 * Human label for grade.
 *
 * @param int $score Score.
 * @return string
 */
function neonews_get_performance_grade_label( $score ) {
    if ( $score >= 95 ) {
        return __( 'Excellent', 'neonews' );
    }
    if ( $score >= 85 ) {
        return __( 'Very good', 'neonews' );
    }
    if ( $score >= 75 ) {
        return __( 'Good', 'neonews' );
    }
    if ( $score >= 60 ) {
        return __( 'Fair', 'neonews' );
    }
    if ( $score >= 45 ) {
        return __( 'Needs work', 'neonews' );
    }
    return __( 'Poor', 'neonews' );
}

/**
 * Rough TTI improvement estimate in ms.
 *
 * @param int $saved_kb       KB saved.
 * @param int $saved_requests Requests saved.
 * @return int
 */
function neonews_estimate_tti_improvement( $saved_kb, $saved_requests ) {
    return (int) round( ( $saved_kb * 8 ) + ( $saved_requests * 120 ) );
}

/**
 * Apply one-click performance recommendations (toggleable settings only).
 *
 * @return int Number of settings enabled.
 */
function neonews_apply_performance_recommendations() {
    $before   = neonews_get_settings();
    $settings = $before;
    $count    = 0;

    $settings['perf_lightweight_mode'] = true;

    foreach ( neonews_get_performance_checks() as $id => $check ) {
        if ( empty( $check['setting'] ) ) {
            continue;
        }
        if ( 'perf_lazy_captcha' === $check['setting'] && ( ! class_exists( 'NeoNews_Captcha' ) || ! NeoNews_Captcha::is_configured() ) ) {
            continue;
        }
        if ( empty( $before[ $check['setting'] ] ) ) {
            $settings[ $check['setting'] ] = true;
            ++$count;
        }
    }

    update_option( 'neonews_platform_settings', wp_parse_args( $settings, neonews_get_default_settings() ) );

    return $count;
}
