<?php
/**
 * NewsPulse admin settings — ads, homepage banner, display options.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Default settings.
 *
 * @return array
 */
function neonews_get_default_settings() {
    return array(
        'ads_master'          => true,
        'adsense_enabled'          => false,
        'adsense_publisher_id'     => '',
        'adsense_default_slot_id'  => '',
        'adsense_auto_ads'         => false,
        'adsense_slots'            => array(),
        'show_header_datetime'=> true,
        'home_banner_enabled' => true,
        'home_banner_type'    => 'image',
        'home_banner_image'   => 0,
        'home_banner_video'   => '',
        'home_banner_link'    => '',
        'ad_zones'            => array(
            'ad-header'       => true,
            'ad-content'      => true,
            'ad-mobile'       => true,
            'ad-home-hero'    => true,
            'ad-home-mid'     => true,
            'ad-home-videos'  => true,
            'ad-home-sidebar' => true,
            'ad-home-footer'  => true,
            'ad-single'       => true,
            'ad-archive'      => true,
            'ad-sidebar'      => true,
        ),
        'cookie_banner_enabled'   => true,
        'cookie_gate_ads'         => true,
        'cookie_gate_statistics'  => true,
        'analytics_enabled'       => false,
        'google_analytics_id'     => '',
        'cookie_custom_script'    => '',
        'weather_enabled'       => true,
        'weather_city'          => 'New York',
        'weather_lat'           => '',
        'weather_lon'           => '',
        // Homepage.
        'show_home_hero'          => true,
        'home_hero_title'         => __( "The day's most important stories,", 'neonews' ),
        'home_hero_title_accent'  => __( 'curated for you.', 'neonews' ),
        'home_hero_subtitle'      => __( 'Smart reporting from every beat. No noise, no filler — just signal.', 'neonews' ),
        'show_home_carousel'      => true,
        'home_carousel_count'     => 6,
        'show_home_categories'    => true,
        'home_category_slugs'     => 'business,technology,sports,entertainment',
        'home_category_posts'     => 3,
        'home_mid_ad_after_cat'   => 2,
        'show_home_videos'        => true,
        'home_video_category'     => 'videos',
        'home_video_count'        => 5,
        'home_video_title'        => __( 'Watch', 'neonews' ),
        'home_video_subtitle'     => __( 'Original video reporting', 'neonews' ),
        'show_home_tipline'       => true,
        'show_home_latest'        => true,
        'home_latest_count'       => 6,
        'show_home_newsletter'    => true,
        // Header.
        'show_breaking_news'      => true,
        'breaking_label_live'     => __( 'LIVE', 'neonews' ),
        'breaking_label_tag'      => __( 'Breaking', 'neonews' ),
        'breaking_count'          => 5,
        'logo_mark'               => '✦',
        'show_header_search'      => true,
        'show_header_auth'        => true,
        'search_placeholder_desktop' => __( 'Search stories, topics, authors…', 'neonews' ),
        'search_placeholder_mobile'  => __( 'Search stories…', 'neonews' ),
        'fallback_nav_categories' => 'politics,business,technology,sports,entertainment',
        'fallback_nav_pages'      => 'about,contact',
        // Footer.
        'footer_tagline'          => __( 'Independent news, written for people who want the full story without the noise.', 'neonews' ),
        'footer_copyright'        => '',
        'show_footer_social'      => true,
        'show_header_social'      => true,
        'show_contact_social'     => true,
        'show_post_whatsapp'       => true,
        'show_contact_whatsapp'    => true,
        'show_advertise_whatsapp'  => true,
        'show_careers_whatsapp'    => true,
        'whatsapp_number'          => '',
        'whatsapp_contact_message' => __( 'Hello, I would like to get in touch.', 'neonews' ),
        'whatsapp_advertise_message' => __( 'Hello, I am interested in advertising on your site.', 'neonews' ),
        'whatsapp_careers_message'   => __( 'Hello, I would like to apply for a role at your newsroom.', 'neonews' ),
        'social_x'                => '',
        'social_facebook'         => '',
        'social_linkedin'         => '',
        'social_instagram'        => '',
        'footer_section_categories' => 'politics,business,technology,sports,entertainment',
        'footer_company_pages'    => 'about,careers,advertise,contact',
        'show_footer_newsletter'  => true,
        'footer_newsletter_title' => __( 'Stay in the loop', 'neonews' ),
        'footer_newsletter_text'  => __( "The day's most important stories, in your inbox by 7am.", 'neonews' ),
        'newsletter_form_action'  => '',
        // Sidebar.
        'sidebar_weather'         => true,
        'sidebar_fresh_reads'     => true,
        'sidebar_fresh_reads_title' => __( 'Fresh Reads', 'neonews' ),
        'sidebar_fresh_reads_count' => 5,
        'sidebar_topics'          => true,
        'sidebar_topics_count'    => 8,
        // Features.
        'show_engagement_bar'     => true,
        'show_post_likes'         => true,
        'show_post_shares'        => true,
        'show_post_views'         => true,
        'excerpt_length'          => 25,
        'reading_wpm'             => 200,
        'category_colors'         => '',
        // CAPTCHA.
        'auth_allow_registration' => true,
        'auth_auto_username'      => true,
        'reg_fields'              => array(),
        'reg_custom_fields'       => '',
        'captcha_enabled'         => false,
        'captcha_provider'        => 'turnstile',
        'captcha_site_key'        => '',
        'captcha_secret_key'      => '',
        'captcha_on_login'        => true,
        'captcha_on_register'     => true,
        'captcha_v3_threshold'    => 0.5,
        // Performance (lightweight mode on by default).
        'perf_lightweight_mode'   => true,
        'perf_system_fonts'       => false,
        'perf_disable_emojis'     => true,
        'perf_disable_embeds'     => true,
        'perf_disable_dashicons'  => true,
        'perf_disable_block_css'  => true,
        'perf_disable_heartbeat'  => true,
        'perf_lazy_captcha'       => true,
        'perf_reduce_animations'  => true,
        'perf_conditional_core_assets' => true,
    );
}

/**
 * Get merged settings.
 *
 * @return array
 */
function neonews_get_settings() {
    $saved = get_option( 'neonews_platform_settings', array() );
    return wp_parse_args( $saved, neonews_get_default_settings() );
}

/**
 * Is a specific ad zone enabled?
 *
 * @param string $zone_id Sidebar ID.
 * @return bool
 */
function neonews_is_ad_zone_enabled( $zone_id ) {
    $settings = neonews_get_settings();
    if ( empty( $settings['ads_master'] ) ) {
        return false;
    }
    if ( ! isset( $settings['ad_zones'][ $zone_id ] ) ) {
        return true;
    }
    return (bool) $settings['ad_zones'][ $zone_id ];
}

/**
 * Register admin menu.
 */
function neonews_register_admin_settings_page() {
    add_menu_page(
        __( 'NewsPulse Settings', 'neonews' ),
        __( 'NewsPulse', 'neonews' ),
        'manage_options',
        'neonews-settings',
        'neonews_render_settings_page',
        'dashicons-admin-site-alt3',
        59
    );

    add_submenu_page(
        'neonews-settings',
        __( 'Platform Settings', 'neonews' ),
        __( 'Platform', 'neonews' ),
        'manage_options',
        'neonews-settings',
        'neonews_render_settings_page'
    );
}
add_action( 'admin_menu', 'neonews_register_admin_settings_page', 9 );

/**
 * Register settings.
 */
function neonews_register_settings() {
    register_setting( 'neonews_settings_group', 'neonews_platform_settings', 'neonews_sanitize_settings' );
}
add_action( 'admin_init', 'neonews_register_settings' );

/**
 * Sanitize settings.
 *
 * @param array $input Raw input.
 * @return array
 */
function neonews_sanitize_settings( $input ) {
    if ( ! is_array( $input ) ) {
        $input = array();
    }

    $defaults = neonews_get_default_settings();
    $previous = wp_parse_args( get_option( 'neonews_platform_settings', array() ), $defaults );
    $active   = sanitize_key( $input['_active_tab'] ?? 'platform' );
    unset( $input['_active_tab'] );
    $output   = $previous;

    if ( 'platform' === $active ) {
        $output['ads_master']           = ! empty( $input['ads_master'] );
        $output['show_header_datetime'] = ! empty( $input['show_header_datetime'] );
        $output['home_banner_enabled']  = ! empty( $input['home_banner_enabled'] );
        $output['home_banner_type']     = in_array( $input['home_banner_type'] ?? '', array( 'image', 'video' ), true ) ? $input['home_banner_type'] : 'image';
        $output['home_banner_image']    = absint( $input['home_banner_image'] ?? 0 );
        $output['home_banner_video']    = esc_url_raw( $input['home_banner_video'] ?? '' );
        $output['home_banner_link']     = esc_url_raw( $input['home_banner_link'] ?? '' );

        $output['ad_zones'] = array();
        foreach ( array_keys( $defaults['ad_zones'] ) as $zone ) {
            $output['ad_zones'][ $zone ] = ! empty( $input['ad_zones'][ $zone ] );
        }

        $output['adsense_enabled']           = ! empty( $input['adsense_enabled'] );
        $output['adsense_auto_ads']          = ! empty( $input['adsense_auto_ads'] );
        $output['adsense_publisher_id']      = sanitize_text_field( $input['adsense_publisher_id'] ?? '' );
        $output['adsense_default_slot_id']   = preg_replace( '/\D/', '', (string) ( $input['adsense_default_slot_id'] ?? '' ) );
        $output['adsense_slots']             = array();
        foreach ( array_keys( $defaults['ad_zones'] ) as $zone ) {
            $slot = isset( $input['adsense_slots'][ $zone ] ) ? (string) $input['adsense_slots'][ $zone ] : '';
            $slot = preg_replace( '/\D/', '', $slot );
            if ( $slot ) {
                $output['adsense_slots'][ $zone ] = $slot;
            }
        }

        $output['cookie_banner_enabled']  = ! empty( $input['cookie_banner_enabled'] );
        $output['cookie_gate_ads']        = ! empty( $input['cookie_gate_ads'] );
        $output['cookie_gate_statistics'] = ! empty( $input['cookie_gate_statistics'] );
        $output['analytics_enabled']      = ! empty( $input['analytics_enabled'] );
        $output['google_analytics_id']    = sanitize_text_field( $input['google_analytics_id'] ?? '' );
        $output['cookie_custom_script']   = isset( $input['cookie_custom_script'] ) ? wp_unslash( $input['cookie_custom_script'] ) : '';
        $output['weather_enabled']        = ! empty( $input['weather_enabled'] );
        $output['weather_city']           = sanitize_text_field( $input['weather_city'] ?? 'New York' );
        $output['weather_lat']            = sanitize_text_field( $input['weather_lat'] ?? '' );
        $output['weather_lon']            = sanitize_text_field( $input['weather_lon'] ?? '' );

        $output['auth_allow_registration'] = ! empty( $input['auth_allow_registration'] );
        $output['auth_auto_username']      = ! empty( $input['auth_auto_username'] );
        $output['reg_custom_fields']       = isset( $input['reg_custom_fields'] ) ? sanitize_textarea_field( wp_unslash( $input['reg_custom_fields'] ) ) : ( $previous['reg_custom_fields'] ?? '' );
        $output['reg_fields']              = array();
        foreach ( array_keys( neonews_get_builtin_registration_field_defs() ) as $field_key ) {
            $output['reg_fields'][ $field_key ] = array(
                'enabled'  => ! empty( $input['reg_fields'][ $field_key ]['enabled'] ),
                'required' => ! empty( $input['reg_fields'][ $field_key ]['required'] ),
            );
        }
        update_option( 'users_can_register', $output['auth_allow_registration'] ? 1 : 0 );

        $output['captcha_enabled']       = ! empty( $input['captcha_enabled'] );
        $output['captcha_on_login']      = ! empty( $input['captcha_on_login'] );
        $output['captcha_on_register']   = ! empty( $input['captcha_on_register'] );
        $output['captcha_site_key']      = sanitize_text_field( $input['captcha_site_key'] ?? '' );
        if ( ! empty( $input['captcha_secret_key'] ) ) {
            $output['captcha_secret_key'] = sanitize_text_field( $input['captcha_secret_key'] );
        } else {
            $output['captcha_secret_key'] = $previous['captcha_secret_key'] ?? '';
        }
        $providers                       = array( 'turnstile', 'recaptcha_v2', 'recaptcha_v3', 'hcaptcha' );
        $output['captcha_provider']      = in_array( $input['captcha_provider'] ?? '', $providers, true ) ? $input['captcha_provider'] : 'turnstile';
        $output['captcha_v3_threshold']  = max( 0.1, min( 1.0, (float) ( $input['captcha_v3_threshold'] ?? $previous['captcha_v3_threshold'] ) ) );

        $output['perf_lightweight_mode']        = ! empty( $input['perf_lightweight_mode'] );
        $output['perf_system_fonts']            = ! empty( $input['perf_system_fonts'] );
        $output['perf_disable_emojis']          = ! empty( $input['perf_disable_emojis'] );
        $output['perf_disable_embeds']          = ! empty( $input['perf_disable_embeds'] );
        $output['perf_disable_dashicons']       = ! empty( $input['perf_disable_dashicons'] );
        $output['perf_disable_block_css']       = ! empty( $input['perf_disable_block_css'] );
        $output['perf_disable_heartbeat']       = ! empty( $input['perf_disable_heartbeat'] );
        $output['perf_lazy_captcha']            = ! empty( $input['perf_lazy_captcha'] );
        $output['perf_reduce_animations']       = ! empty( $input['perf_reduce_animations'] );
        $output['perf_conditional_core_assets'] = ! empty( $input['perf_conditional_core_assets'] );
    }

    if ( 'homepage' === $active ) {
        $output['show_home_hero']       = ! empty( $input['show_home_hero'] );
        $output['show_home_carousel']  = ! empty( $input['show_home_carousel'] );
        $output['show_home_categories'] = ! empty( $input['show_home_categories'] );
        $output['show_home_videos']    = ! empty( $input['show_home_videos'] );
        $output['show_home_tipline']   = ! empty( $input['show_home_tipline'] );
        $output['show_home_latest']    = ! empty( $input['show_home_latest'] );
        $output['show_home_newsletter'] = ! empty( $input['show_home_newsletter'] );
        $output['home_hero_title']        = sanitize_text_field( wp_unslash( $input['home_hero_title'] ?? $previous['home_hero_title'] ) );
        $output['home_hero_title_accent'] = sanitize_text_field( wp_unslash( $input['home_hero_title_accent'] ?? $previous['home_hero_title_accent'] ) );
        $output['home_hero_subtitle']     = sanitize_text_field( wp_unslash( $input['home_hero_subtitle'] ?? $previous['home_hero_subtitle'] ) );
        $output['home_category_slugs']    = sanitize_text_field( wp_unslash( $input['home_category_slugs'] ?? $previous['home_category_slugs'] ) );
        $output['home_video_category']    = sanitize_title( wp_unslash( $input['home_video_category'] ?? $previous['home_video_category'] ) );
        $output['home_video_title']       = sanitize_text_field( wp_unslash( $input['home_video_title'] ?? $previous['home_video_title'] ) );
        $output['home_video_subtitle']    = sanitize_text_field( wp_unslash( $input['home_video_subtitle'] ?? $previous['home_video_subtitle'] ) );
        $output['home_carousel_count']    = max( 1, min( 12, absint( $input['home_carousel_count'] ?? $previous['home_carousel_count'] ) ) );
        $output['home_category_posts']    = max( 1, min( 10, absint( $input['home_category_posts'] ?? $previous['home_category_posts'] ) ) );
        $output['home_mid_ad_after_cat']  = max( 0, min( 10, absint( $input['home_mid_ad_after_cat'] ?? $previous['home_mid_ad_after_cat'] ) ) );
        $output['home_video_count']       = max( 1, min( 12, absint( $input['home_video_count'] ?? $previous['home_video_count'] ) ) );
        $output['home_latest_count']      = max( 1, min( 24, absint( $input['home_latest_count'] ?? $previous['home_latest_count'] ) ) );
    }

    if ( 'header' === $active ) {
        $output['show_breaking_news']   = ! empty( $input['show_breaking_news'] );
        $output['show_header_search']   = ! empty( $input['show_header_search'] );
        $output['show_header_auth']     = ! empty( $input['show_header_auth'] );
        $output['breaking_label_live']  = sanitize_text_field( wp_unslash( $input['breaking_label_live'] ?? $previous['breaking_label_live'] ) );
        $output['breaking_label_tag']   = sanitize_text_field( wp_unslash( $input['breaking_label_tag'] ?? $previous['breaking_label_tag'] ) );
        $output['logo_mark']            = sanitize_text_field( wp_unslash( $input['logo_mark'] ?? $previous['logo_mark'] ) );
        $output['search_placeholder_desktop'] = sanitize_text_field( wp_unslash( $input['search_placeholder_desktop'] ?? $previous['search_placeholder_desktop'] ) );
        $output['search_placeholder_mobile']  = sanitize_text_field( wp_unslash( $input['search_placeholder_mobile'] ?? $previous['search_placeholder_mobile'] ) );
        $output['fallback_nav_categories']    = sanitize_text_field( wp_unslash( $input['fallback_nav_categories'] ?? $previous['fallback_nav_categories'] ) );
        $output['fallback_nav_pages']         = sanitize_text_field( wp_unslash( $input['fallback_nav_pages'] ?? $previous['fallback_nav_pages'] ) );
        $output['breaking_count']       = max( 1, min( 10, absint( $input['breaking_count'] ?? $previous['breaking_count'] ) ) );
    }

    if ( 'footer' === $active ) {
        $output['show_footer_social']     = ! empty( $input['show_footer_social'] );
        $output['show_header_social']     = ! empty( $input['show_header_social'] );
        $output['show_contact_social']    = ! empty( $input['show_contact_social'] );
        $output['show_post_whatsapp']       = ! empty( $input['show_post_whatsapp'] );
        $output['show_contact_whatsapp']    = ! empty( $input['show_contact_whatsapp'] );
        $output['show_advertise_whatsapp']  = ! empty( $input['show_advertise_whatsapp'] );
        $output['show_careers_whatsapp']    = ! empty( $input['show_careers_whatsapp'] );
        $output['whatsapp_number']          = preg_replace( '/[^0-9]/', '', (string) ( $input['whatsapp_number'] ?? '' ) );
        $output['whatsapp_contact_message']   = sanitize_text_field( wp_unslash( $input['whatsapp_contact_message'] ?? $previous['whatsapp_contact_message'] ) );
        $output['whatsapp_advertise_message'] = sanitize_text_field( wp_unslash( $input['whatsapp_advertise_message'] ?? $previous['whatsapp_advertise_message'] ) );
        $output['whatsapp_careers_message']   = sanitize_text_field( wp_unslash( $input['whatsapp_careers_message'] ?? $previous['whatsapp_careers_message'] ) );
        $output['show_footer_newsletter'] = ! empty( $input['show_footer_newsletter'] );
        $output['footer_tagline']         = sanitize_text_field( wp_unslash( $input['footer_tagline'] ?? $previous['footer_tagline'] ) );
        $output['footer_copyright']       = isset( $input['footer_copyright'] ) ? wp_kses_post( wp_unslash( $input['footer_copyright'] ) ) : $previous['footer_copyright'];
        $output['footer_section_categories'] = sanitize_text_field( wp_unslash( $input['footer_section_categories'] ?? $previous['footer_section_categories'] ) );
        $output['footer_company_pages']   = sanitize_text_field( wp_unslash( $input['footer_company_pages'] ?? $previous['footer_company_pages'] ) );
        $output['footer_newsletter_title'] = sanitize_text_field( wp_unslash( $input['footer_newsletter_title'] ?? $previous['footer_newsletter_title'] ) );
        $output['footer_newsletter_text'] = sanitize_text_field( wp_unslash( $input['footer_newsletter_text'] ?? $previous['footer_newsletter_text'] ) );
        $output['social_x']         = esc_url_raw( $input['social_x'] ?? '' );
        $output['social_facebook']  = esc_url_raw( $input['social_facebook'] ?? '' );
        $output['social_linkedin']  = esc_url_raw( $input['social_linkedin'] ?? '' );
        $output['social_instagram'] = esc_url_raw( $input['social_instagram'] ?? '' );
        $output['newsletter_form_action'] = esc_url_raw( $input['newsletter_form_action'] ?? '' );
    }

    if ( 'sidebar' === $active ) {
        $output['sidebar_weather']     = ! empty( $input['sidebar_weather'] );
        $output['sidebar_fresh_reads'] = ! empty( $input['sidebar_fresh_reads'] );
        $output['sidebar_topics']      = ! empty( $input['sidebar_topics'] );
        $output['sidebar_fresh_reads_title'] = sanitize_text_field( wp_unslash( $input['sidebar_fresh_reads_title'] ?? $previous['sidebar_fresh_reads_title'] ) );
        $output['sidebar_fresh_reads_count'] = max( 1, min( 10, absint( $input['sidebar_fresh_reads_count'] ?? $previous['sidebar_fresh_reads_count'] ) ) );
        $output['sidebar_topics_count']      = max( 1, min( 20, absint( $input['sidebar_topics_count'] ?? $previous['sidebar_topics_count'] ) ) );
    }

    if ( 'features' === $active ) {
        $output['show_engagement_bar'] = ! empty( $input['show_engagement_bar'] );
        $output['show_post_likes']    = ! empty( $input['show_post_likes'] );
        $output['show_post_shares']   = ! empty( $input['show_post_shares'] );
        $output['show_post_views']    = ! empty( $input['show_post_views'] );
        $output['excerpt_length']     = max( 5, min( 80, absint( $input['excerpt_length'] ?? $previous['excerpt_length'] ) ) );
        $output['reading_wpm']        = max( 100, min( 400, absint( $input['reading_wpm'] ?? $previous['reading_wpm'] ) ) );
        $output['category_colors']    = isset( $input['category_colors'] ) ? sanitize_textarea_field( wp_unslash( $input['category_colors'] ) ) : $previous['category_colors'];
    }

    return wp_parse_args( $output, $defaults );
}

/**
 * Render settings page.
 */
function neonews_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( isset( $_POST['neonews_create_legal_pages'] ) && check_admin_referer( 'neonews_create_legal' ) ) {
        $created = neonews_ensure_legal_pages();
        echo '<div class="notice notice-success"><p>' . esc_html( sprintf( __( 'Updated %d legal pages.', 'neonews' ), $created ) ) . '</p></div>';
    }

    if ( isset( $_POST['neonews_create_about_page'] ) && check_admin_referer( 'neonews_create_about' ) ) {
        $ok = neonews_ensure_about_page();
        if ( $ok ) {
            echo '<div class="notice notice-success"><p>' . esc_html__( 'About Us page created or updated.', 'neonews' ) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Could not create the About Us page. Check your permissions and try again.', 'neonews' ) . '</p></div>';
        }
    }

    $settings = neonews_get_settings();
    $tab      = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'platform';
    $tabs     = array(
        'platform'  => __( 'Ads & Integrations', 'neonews' ),
        'homepage'  => __( 'Homepage', 'neonews' ),
        'header'    => __( 'Header', 'neonews' ),
        'footer'    => __( 'Footer', 'neonews' ),
        'sidebar'   => __( 'Sidebar', 'neonews' ),
        'features'  => __( 'Features & Colors', 'neonews' ),
    );
    if ( ! isset( $tabs[ $tab ] ) ) {
        $tab = 'platform';
    }

    $zones = array(
        'ad-header'       => __( 'Header banner (728×90)', 'neonews' ),
        'ad-home-hero'    => __( 'Home — below hero carousel', 'neonews' ),
        'ad-home-mid'     => __( 'Home — between categories', 'neonews' ),
        'ad-home-videos'  => __( 'Home — before videos', 'neonews' ),
        'ad-content'      => __( 'Home — in-content', 'neonews' ),
        'ad-home-sidebar' => __( 'Home — sidebar rail', 'neonews' ),
        'ad-home-footer'  => __( 'Home — footer area', 'neonews' ),
        'ad-mobile'       => __( 'Mobile sticky bottom', 'neonews' ),
        'ad-single'       => __( 'Single post — after article', 'neonews' ),
        'ad-archive'      => __( 'Archive — below title', 'neonews' ),
        'ad-sidebar'      => __( 'Sidebar — all pages with sidebar', 'neonews' ),
    );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'NewsPulse Settings', 'neonews' ); ?></h1>
        <p class="description">
            <?php esc_html_e( 'Control every feature from here. For fonts, colors, and live preview go to', 'neonews' ); ?>
            <a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Appearance → Customize → NewsPulse Design', 'neonews' ); ?></a>.
            <?php esc_html_e( 'Categories: Posts → Categories. Pages: Pages → Add New. Menus: Appearance → Menus.', 'neonews' ); ?>
        </p>

        <nav class="nav-tab-wrapper">
            <?php foreach ( $tabs as $slug => $label ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-settings&tab=' . $slug ) ); ?>" class="nav-tab<?php echo $tab === $slug ? ' nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
            <?php endforeach; ?>
        </nav>

        <form method="post" action="options.php" style="margin-top:1rem;">
            <?php settings_fields( 'neonews_settings_group' ); ?>
            <input type="hidden" name="neonews_platform_settings[_active_tab]" value="<?php echo esc_attr( $tab ); ?>" />

            <?php if ( 'platform' === $tab ) : ?>
            <?php neonews_render_performance_panel(); ?>
            <h2><?php esc_html_e( 'Advertisements', 'neonews' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Enable all ads', 'neonews' ); ?></th>
                    <td>
                        <label><input type="checkbox" name="neonews_platform_settings[ads_master]" value="1" <?php checked( $settings['ads_master'] ); ?> />
                        <?php esc_html_e( 'Show ad zones site-wide (uncheck to hide ALL ads)', 'neonews' ); ?></label>
                    </td>
                </tr>
            </table>

            <h3><?php esc_html_e( 'Individual ad zones', 'neonews' ); ?></h3>
            <table class="form-table">
                <?php foreach ( $zones as $id => $label ) : ?>
                <tr>
                    <th><?php echo esc_html( $label ); ?></th>
                    <td>
                        <label><input type="checkbox" name="neonews_platform_settings[ad_zones][<?php echo esc_attr( $id ); ?>]" value="1" <?php checked( ! empty( $settings['ad_zones'][ $id ] ) ); ?> /> <?php esc_html_e( 'Enabled', 'neonews' ); ?></label>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <h3><?php esc_html_e( 'Google AdSense', 'neonews' ); ?></h3>
            <p class="description">
                <?php esc_html_e( 'Connect your AdSense account to fill all ad zones automatically. Widgets in Appearance → Widgets still take priority over AdSense when assigned.', 'neonews' ); ?>
            </p>
            <?php
            if ( function_exists( 'neonews_adsense_is_active' ) && neonews_adsense_is_active() ) {
                $coverage = neonews_get_adsense_zone_coverage();
                $ads_txt  = function_exists( 'neonews_get_ads_txt_url' ) ? neonews_get_ads_txt_url() : '';
                ?>
                <div class="notice notice-info inline" style="margin:0 0 1rem;padding:10px 12px;">
                    <p style="margin:0;">
                        <?php
                        printf(
                            /* translators: 1: ready zones, 2: total enabled zones */
                            esc_html__( 'AdSense is active — %1$d of %2$d enabled ad zones have a slot (zone-specific or default).', 'neonews' ),
                            (int) $coverage['ready'],
                            (int) $coverage['total']
                        );
                        ?>
                        <?php if ( $ads_txt ) : ?>
                            <?php esc_html_e( 'ads.txt:', 'neonews' ); ?>
                            <a href="<?php echo esc_url( $ads_txt ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $ads_txt ); ?></a>
                        <?php endif; ?>
                    </p>
                </div>
                <?php
            } elseif ( ! empty( $settings['adsense_enabled'] ) ) {
                ?>
                <div class="notice notice-warning inline" style="margin:0 0 1rem;padding:10px 12px;">
                    <p style="margin:0;"><?php esc_html_e( 'AdSense is enabled but missing a valid Publisher ID (ca-pub-…). Add it below to serve ads in all zones.', 'neonews' ); ?></p>
                </div>
                <?php
            }
            ?>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Enable AdSense', 'neonews' ); ?></th>
                    <td>
                        <label><input type="checkbox" name="neonews_platform_settings[adsense_enabled]" value="1" <?php checked( $settings['adsense_enabled'] ); ?> />
                        <?php esc_html_e( 'Use Google AdSense for ad zones', 'neonews' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th><label for="neonews-adsense-publisher"><?php esc_html_e( 'Publisher ID', 'neonews' ); ?></label></th>
                    <td>
                        <input type="text" id="neonews-adsense-publisher" name="neonews_platform_settings[adsense_publisher_id]" value="<?php echo esc_attr( $settings['adsense_publisher_id'] ); ?>" class="regular-text" placeholder="ca-pub-XXXXXXXXXXXXXXXX" />
                        <p class="description"><?php esc_html_e( 'From AdSense → Account → Account information.', 'neonews' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="neonews-adsense-default-slot"><?php esc_html_e( 'Default slot ID', 'neonews' ); ?></label></th>
                    <td>
                        <input type="text" id="neonews-adsense-default-slot" name="neonews_platform_settings[adsense_default_slot_id]" value="<?php echo esc_attr( $settings['adsense_default_slot_id'] ?? '' ); ?>" class="regular-text" placeholder="1234567890" />
                        <p class="description"><?php esc_html_e( 'One display ad unit can fill every zone — paste a single slot ID here. Zone-specific IDs below override this default.', 'neonews' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Auto ads', 'neonews' ); ?></th>
                    <td>
                        <label><input type="checkbox" name="neonews_platform_settings[adsense_auto_ads]" value="1" <?php checked( $settings['adsense_auto_ads'] ); ?> />
                        <?php esc_html_e( 'Load AdSense auto ads site-wide (enable Auto ads in your AdSense dashboard too)', 'neonews' ); ?></label>
                    </td>
                </tr>
            </table>

            <h4><?php esc_html_e( 'AdSense slot IDs per zone (optional)', 'neonews' ); ?></h4>
            <p class="description"><?php esc_html_e( 'Override the default slot for individual placements. Leave blank to use the default slot ID for that zone.', 'neonews' ); ?></p>
            <table class="form-table">
                <?php
                $adsense_slots = isset( $settings['adsense_slots'] ) && is_array( $settings['adsense_slots'] ) ? $settings['adsense_slots'] : array();
                foreach ( $zones as $id => $label ) :
                ?>
                <tr>
                    <th><label for="neonews-adsense-slot-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label></th>
                    <td>
                        <input type="text" id="neonews-adsense-slot-<?php echo esc_attr( $id ); ?>" name="neonews_platform_settings[adsense_slots][<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $adsense_slots[ $id ] ?? '' ); ?>" class="regular-text" placeholder="1234567890" />
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <h2><?php esc_html_e( 'Homepage full-width banner', 'neonews' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Enable banner', 'neonews' ); ?></th>
                    <td><label><input type="checkbox" name="neonews_platform_settings[home_banner_enabled]" value="1" <?php checked( $settings['home_banner_enabled'] ); ?> /> <?php esc_html_e( 'Show full-width banner on homepage', 'neonews' ); ?></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Banner type', 'neonews' ); ?></th>
                    <td>
                        <select name="neonews_platform_settings[home_banner_type]">
                            <option value="image" <?php selected( $settings['home_banner_type'], 'image' ); ?>><?php esc_html_e( 'Image', 'neonews' ); ?></option>
                            <option value="video" <?php selected( $settings['home_banner_type'], 'video' ); ?>><?php esc_html_e( 'YouTube video', 'neonews' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Banner image ID', 'neonews' ); ?></th>
                    <td>
                        <input type="number" name="neonews_platform_settings[home_banner_image]" value="<?php echo esc_attr( $settings['home_banner_image'] ); ?>" class="small-text" />
                        <p class="description"><?php esc_html_e( 'Upload in Media Library and paste attachment ID here.', 'neonews' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'YouTube URL', 'neonews' ); ?></th>
                    <td><input type="url" name="neonews_platform_settings[home_banner_video]" value="<?php echo esc_attr( $settings['home_banner_video'] ); ?>" class="large-text" placeholder="https://www.youtube.com/watch?v=..." /></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Banner link (optional)', 'neonews' ); ?></th>
                    <td><input type="url" name="neonews_platform_settings[home_banner_link]" value="<?php echo esc_attr( $settings['home_banner_link'] ); ?>" class="large-text" /></td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Header bar', 'neonews' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Date & time bar', 'neonews' ); ?></th>
                    <td><label><input type="checkbox" name="neonews_platform_settings[show_header_datetime]" value="1" <?php checked( $settings['show_header_datetime'] ); ?> /> <?php esc_html_e( 'Show live date and time in header', 'neonews' ); ?></label></td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Cookie consent banner', 'neonews' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Enable banner', 'neonews' ); ?></th>
                    <td><label><input type="checkbox" name="neonews_platform_settings[cookie_banner_enabled]" value="1" <?php checked( ! empty( $settings['cookie_banner_enabled'] ) ); ?> /> <?php esc_html_e( 'Show bottom cookie consent bar', 'neonews' ); ?></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Gate ad zones', 'neonews' ); ?></th>
                    <td><label><input type="checkbox" name="neonews_platform_settings[cookie_gate_ads]" value="1" <?php checked( ! empty( $settings['cookie_gate_ads'] ) ); ?> /> <?php esc_html_e( 'Hide advertisements until user accepts all cookies', 'neonews' ); ?></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Gate statistics', 'neonews' ); ?></th>
                    <td><label><input type="checkbox" name="neonews_platform_settings[cookie_gate_statistics]" value="1" <?php checked( ! empty( $settings['cookie_gate_statistics'] ) ); ?> /> <?php esc_html_e( 'Pause view/share counting until user accepts all cookies', 'neonews' ); ?></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Google Analytics', 'neonews' ); ?></th>
                    <td><label><input type="checkbox" name="neonews_platform_settings[analytics_enabled]" value="1" <?php checked( ! empty( $settings['analytics_enabled'] ) ); ?> /> <?php esc_html_e( 'Load Google Analytics only after “Accept all cookies”', 'neonews' ); ?></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'GA Measurement ID', 'neonews' ); ?></th>
                    <td>
                        <input type="text" name="neonews_platform_settings[google_analytics_id]" value="<?php echo esc_attr( $settings['google_analytics_id'] ?? '' ); ?>" class="regular-text" placeholder="G-XXXXXXXXXX" />
                        <p class="description"><?php esc_html_e( 'Your Google Analytics 4 ID (starts with G-). Leave empty to skip analytics.', 'neonews' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Custom scripts (optional)', 'neonews' ); ?></th>
                    <td>
                        <textarea name="neonews_platform_settings[cookie_custom_script]" rows="4" class="large-text code"><?php echo esc_textarea( $settings['cookie_custom_script'] ?? '' ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Extra tracking snippets (e.g. Meta Pixel) injected only after full consent. Admin only.', 'neonews' ); ?></p>
                    </td>
                </tr>
            </table>

            <?php neonews_admin_render_registration_settings( $settings ); ?>

            <h2><?php esc_html_e( 'Login & signup CAPTCHA', 'neonews' ); ?></h2>
            <p class="description"><?php esc_html_e( 'Protect the sign-in modal, AJAX registration, and wp-login.php from bots. Get free keys from Cloudflare Turnstile, Google reCAPTCHA, or hCaptcha.', 'neonews' ); ?></p>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Enable CAPTCHA', 'neonews' ); ?></th>
                    <td><label><input type="checkbox" name="neonews_platform_settings[captcha_enabled]" value="1" <?php checked( ! empty( $settings['captcha_enabled'] ) ); ?> /> <?php esc_html_e( 'Require verification on login and/or registration', 'neonews' ); ?></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Provider', 'neonews' ); ?></th>
                    <td>
                        <select name="neonews_platform_settings[captcha_provider]">
                            <option value="turnstile" <?php selected( $settings['captcha_provider'], 'turnstile' ); ?>><?php esc_html_e( 'Cloudflare Turnstile (recommended)', 'neonews' ); ?></option>
                            <option value="recaptcha_v2" <?php selected( $settings['captcha_provider'], 'recaptcha_v2' ); ?>><?php esc_html_e( 'Google reCAPTCHA v2 (checkbox)', 'neonews' ); ?></option>
                            <option value="recaptcha_v3" <?php selected( $settings['captcha_provider'], 'recaptcha_v3' ); ?>><?php esc_html_e( 'Google reCAPTCHA v3 (invisible score)', 'neonews' ); ?></option>
                            <option value="hcaptcha" <?php selected( $settings['captcha_provider'], 'hcaptcha' ); ?>><?php esc_html_e( 'hCaptcha', 'neonews' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Site key', 'neonews' ); ?></th>
                    <td><input type="text" name="neonews_platform_settings[captcha_site_key]" value="<?php echo esc_attr( $settings['captcha_site_key'] ); ?>" class="large-text" autocomplete="off" /></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Secret key', 'neonews' ); ?></th>
                    <td>
                        <input type="password" name="neonews_platform_settings[captcha_secret_key]" value="<?php echo esc_attr( $settings['captcha_secret_key'] ); ?>" class="large-text" autocomplete="new-password" />
                        <p class="description"><?php esc_html_e( 'Stored encrypted when NeoNews Security encryption is enabled.', 'neonews' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Protect login', 'neonews' ); ?></th>
                    <td><label><input type="checkbox" name="neonews_platform_settings[captcha_on_login]" value="1" <?php checked( ! empty( $settings['captcha_on_login'] ) ); ?> /> <?php esc_html_e( 'Sign-in modal and wp-login.php', 'neonews' ); ?></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Protect registration', 'neonews' ); ?></th>
                    <td><label><input type="checkbox" name="neonews_platform_settings[captcha_on_register]" value="1" <?php checked( ! empty( $settings['captcha_on_register'] ) ); ?> /> <?php esc_html_e( 'Sign-up modal and wp-login.php register', 'neonews' ); ?></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'reCAPTCHA v3 threshold', 'neonews' ); ?></th>
                    <td>
                        <input type="number" name="neonews_platform_settings[captcha_v3_threshold]" value="<?php echo esc_attr( $settings['captcha_v3_threshold'] ); ?>" min="0.1" max="1" step="0.1" class="small-text" />
                        <p class="description"><?php esc_html_e( 'Minimum score (0.1–1.0). Default 0.5. Only used for reCAPTCHA v3.', 'neonews' ); ?></p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Performance (lightweight mode)', 'neonews' ); ?></h2>
            <p class="description"><?php esc_html_e( 'Enabled by default. Reduces scripts, CSS, and third-party requests for faster page loads.', 'neonews' ); ?></p>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Lightweight mode', 'neonews' ); ?></th>
                    <td>
                        <span id="nn-perf-setting-perf_lightweight_mode" class="nn-perf-setting-anchor"></span>
                        <label><input type="checkbox" name="neonews_platform_settings[perf_lightweight_mode]" value="1" <?php checked( ! empty( $settings['perf_lightweight_mode'] ) ); ?> /> <?php esc_html_e( 'Enable performance optimizations site-wide', 'neonews' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'System fonts', 'neonews' ); ?></th>
                    <td>
                        <span id="nn-perf-setting-perf_system_fonts" class="nn-perf-setting-anchor"></span>
                        <label><input type="checkbox" name="neonews_platform_settings[perf_system_fonts]" value="1" <?php checked( ! empty( $settings['perf_system_fonts'] ) ); ?> /> <?php esc_html_e( 'Skip Google Fonts (fastest first paint)', 'neonews' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Trim WordPress bloat', 'neonews' ); ?></th>
                    <td>
                        <span id="nn-perf-setting-perf_disable_emojis" class="nn-perf-setting-anchor"></span>
                        <label><input type="checkbox" name="neonews_platform_settings[perf_disable_emojis]" value="1" <?php checked( ! empty( $settings['perf_disable_emojis'] ) ); ?> /> <?php esc_html_e( 'Disable emoji scripts', 'neonews' ); ?></label><br />
                        <span id="nn-perf-setting-perf_disable_embeds" class="nn-perf-setting-anchor"></span>
                        <label><input type="checkbox" name="neonews_platform_settings[perf_disable_embeds]" value="1" <?php checked( ! empty( $settings['perf_disable_embeds'] ) ); ?> /> <?php esc_html_e( 'Disable wp-embed.js', 'neonews' ); ?></label><br />
                        <span id="nn-perf-setting-perf_disable_dashicons" class="nn-perf-setting-anchor"></span>
                        <label><input type="checkbox" name="neonews_platform_settings[perf_disable_dashicons]" value="1" <?php checked( ! empty( $settings['perf_disable_dashicons'] ) ); ?> /> <?php esc_html_e( 'Disable dashicons for visitors', 'neonews' ); ?></label><br />
                        <span id="nn-perf-setting-perf_disable_block_css" class="nn-perf-setting-anchor"></span>
                        <label><input type="checkbox" name="neonews_platform_settings[perf_disable_block_css]" value="1" <?php checked( ! empty( $settings['perf_disable_block_css'] ) ); ?> /> <?php esc_html_e( 'Remove block editor CSS on frontend', 'neonews' ); ?></label><br />
                        <span id="nn-perf-setting-perf_disable_heartbeat" class="nn-perf-setting-anchor"></span>
                        <label><input type="checkbox" name="neonews_platform_settings[perf_disable_heartbeat]" value="1" <?php checked( ! empty( $settings['perf_disable_heartbeat'] ) ); ?> /> <?php esc_html_e( 'Disable Heartbeat on frontend', 'neonews' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Smart asset loading', 'neonews' ); ?></th>
                    <td>
                        <span id="nn-perf-setting-perf_conditional_core_assets" class="nn-perf-setting-anchor"></span>
                        <label><input type="checkbox" name="neonews_platform_settings[perf_conditional_core_assets]" value="1" <?php checked( ! empty( $settings['perf_conditional_core_assets'] ) ); ?> /> <?php esc_html_e( 'Load core plugin JS/CSS only on posts and submission pages', 'neonews' ); ?></label><br />
                        <span id="nn-perf-setting-perf_lazy_captcha" class="nn-perf-setting-anchor"></span>
                        <label><input type="checkbox" name="neonews_platform_settings[perf_lazy_captcha]" value="1" <?php checked( ! empty( $settings['perf_lazy_captcha'] ) ); ?> /> <?php esc_html_e( 'Load CAPTCHA scripts only when sign-in modal opens', 'neonews' ); ?></label><br />
                        <span id="nn-perf-setting-perf_reduce_animations" class="nn-perf-setting-anchor"></span>
                        <label><input type="checkbox" name="neonews_platform_settings[perf_reduce_animations]" value="1" <?php checked( ! empty( $settings['perf_reduce_animations'] ) ); ?> /> <?php esc_html_e( 'Reduce scroll/motion animations', 'neonews' ); ?></label>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Weather widget', 'neonews' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Enable weather', 'neonews' ); ?></th>
                    <td><label><input type="checkbox" name="neonews_platform_settings[weather_enabled]" value="1" <?php checked( ! empty( $settings['weather_enabled'] ) ); ?> /> <?php esc_html_e( 'Show weather in header bar and sidebar', 'neonews' ); ?></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Default city', 'neonews' ); ?></th>
                    <td><input type="text" name="neonews_platform_settings[weather_city]" value="<?php echo esc_attr( $settings['weather_city'] ); ?>" class="regular-text" placeholder="New York" /></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Latitude (optional)', 'neonews' ); ?></th>
                    <td><input type="text" name="neonews_platform_settings[weather_lat]" value="<?php echo esc_attr( $settings['weather_lat'] ); ?>" class="small-text" /></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Longitude (optional)', 'neonews' ); ?></th>
                    <td><input type="text" name="neonews_platform_settings[weather_lon]" value="<?php echo esc_attr( $settings['weather_lon'] ); ?>" class="small-text" /></td>
                </tr>
            </table>

            <?php elseif ( 'homepage' === $tab ) : ?>
                <?php neonews_admin_tab_homepage( $settings ); ?>
            <?php elseif ( 'header' === $tab ) : ?>
                <?php neonews_admin_tab_header( $settings ); ?>
            <?php elseif ( 'footer' === $tab ) : ?>
                <?php neonews_admin_tab_footer( $settings ); ?>
            <?php elseif ( 'sidebar' === $tab ) : ?>
                <?php neonews_admin_tab_sidebar( $settings ); ?>
            <?php elseif ( 'features' === $tab ) : ?>
                <?php neonews_admin_tab_features( $settings ); ?>
            <?php endif; ?>

            <?php submit_button(); ?>
        </form>

        <?php if ( 'platform' === $tab ) : ?>
        <hr />
        <h2><?php esc_html_e( 'Author photos', 'neonews' ); ?></h2>
        <p><?php esc_html_e( 'Upload a display photo for each author (e.g. Alex Rivera) from the WordPress user profile.', 'neonews' ); ?></p>
        <p><a href="<?php echo esc_url( admin_url( 'users.php' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Manage Users', 'neonews' ); ?></a></p>
        <ol style="margin-top:0.75rem;">
            <li><?php esc_html_e( 'Go to Users → All Users', 'neonews' ); ?></li>
            <li><?php esc_html_e( 'Click the author name to edit', 'neonews' ); ?></li>
            <li><?php esc_html_e( 'Scroll to “Author Photo” and upload or select an image', 'neonews' ); ?></li>
            <li><?php esc_html_e( 'Click Update User — the photo shows on all posts by that author', 'neonews' ); ?></li>
        </ol>

        <hr />
        <h2><?php esc_html_e( 'About Us page', 'neonews' ); ?></h2>
        <p><?php esc_html_e( 'Creates or updates the About Us page with the fancy layout template, default story content, and stats hero.', 'neonews' ); ?></p>
        <form method="post">
            <?php wp_nonce_field( 'neonews_create_about' ); ?>
            <button type="submit" name="neonews_create_about_page" class="button button-secondary"><?php esc_html_e( 'Create / Update About Us Page', 'neonews' ); ?></button>
        </form>
        <p style="margin-top:1rem;">
            <a href="<?php echo esc_url( neonews_get_about_page_url() ); ?>" target="_blank"><?php esc_html_e( 'View About Us page', 'neonews' ); ?></a>
            <?php
            $about_page = neonews_get_about_page();
            if ( $about_page ) :
                ?>
                &nbsp;|&nbsp;
                <a href="<?php echo esc_url( get_edit_post_link( $about_page->ID, 'raw' ) ); ?>"><?php esc_html_e( 'Edit page', 'neonews' ); ?></a>
            <?php endif; ?>
        </p>

        <hr />
        <h2><?php esc_html_e( 'Legal pages', 'neonews' ); ?></h2>
        <p><?php esc_html_e( 'Creates or updates dedicated pages for Privacy, Terms of Service, Cookies Policy, and Cache & Storage.', 'neonews' ); ?></p>
        <form method="post">
            <?php wp_nonce_field( 'neonews_create_legal' ); ?>
            <button type="submit" name="neonews_create_legal_pages" class="button button-secondary"><?php esc_html_e( 'Create / Update Legal Pages', 'neonews' ); ?></button>
        </form>
        <ul style="margin-top:1rem;">
            <?php foreach ( neonews_get_legal_links() as $slug => $label ) : ?>
                <li><a href="<?php echo esc_url( neonews_get_legal_page_url( $slug ) ); ?>" target="_blank"><?php echo esc_html( $label ); ?></a></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Render homepage hero banner from settings.
 */
function neonews_render_home_banner() {
    $settings = neonews_get_settings();
    if ( empty( $settings['home_banner_enabled'] ) ) {
        return;
    }

    $link = ! empty( $settings['home_banner_link'] ) ? $settings['home_banner_link'] : '';
    echo '<section class="nn-home-banner nn-container nn-reveal">';
    if ( $link ) {
        echo '<a href="' . esc_url( $link ) . '" class="nn-home-banner-link">';
    } else {
        echo '<div class="nn-home-banner-inner">';
    }

    if ( 'video' === $settings['home_banner_type'] && ! empty( $settings['home_banner_video'] ) ) {
        $id = neonews_parse_youtube_id( $settings['home_banner_video'] );
        if ( $id ) {
            echo '<div class="nn-home-banner-video"><iframe src="https://www.youtube.com/embed/' . esc_attr( $id ) . '?rel=0" title="' . esc_attr__( 'Featured video', 'neonews' ) . '" allowfullscreen loading="lazy"></iframe></div>';
        }
    } elseif ( ! empty( $settings['home_banner_image'] ) ) {
        echo wp_get_attachment_image( (int) $settings['home_banner_image'], 'full', false, array( 'class' => 'nn-home-banner-img' ) );
    } else {
        echo '<div class="nn-home-banner-placeholder"><span>' . esc_html__( 'Set banner in NewsPulse → Settings', 'neonews' ) . '</span></div>';
    }

    echo $link ? '</a>' : '</div>';
    echo '</section>';
}
