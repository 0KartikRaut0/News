<?php
/**
 * NewsPulse-style header, ads, scroll UX, engagement.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Extra widget areas (ads).
 */
function neonews_register_ad_zones() {
    register_sidebar( array(
        'name'          => esc_html__( 'Header Ad Banner', 'neonews' ),
        'id'            => 'ad-header',
        'description'   => esc_html__( '728x90 or responsive ad below navigation.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="nn-ad-widget widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="nn-ad-label">',
        'after_title'   => '</span>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'In-Content Ad', 'neonews' ),
        'id'            => 'ad-content',
        'description'   => esc_html__( 'Ad slot between homepage sections.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="nn-ad-widget nn-ad-content widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="nn-ad-label">',
        'after_title'   => '</span>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Mobile Sticky Ad', 'neonews' ),
        'id'            => 'ad-mobile',
        'description'   => esc_html__( 'Sticky bottom ad on mobile only.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="nn-ad-widget nn-ad-mobile widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="nn-ad-label">',
        'after_title'   => '</span>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Home — Below Hero', 'neonews' ),
        'id'            => 'ad-home-hero',
        'description'   => esc_html__( 'Wide banner directly below the featured carousel.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="nn-ad-widget nn-ad-home widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="nn-ad-label">',
        'after_title'   => '</span>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Home — Between Categories', 'neonews' ),
        'id'            => 'ad-home-mid',
        'description'   => esc_html__( 'Ad between category blocks on homepage.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="nn-ad-widget nn-ad-home widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="nn-ad-label">',
        'after_title'   => '</span>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Home — Before Videos', 'neonews' ),
        'id'            => 'ad-home-videos',
        'description'   => esc_html__( 'Ad slot above the Watch / Videos section.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="nn-ad-widget nn-ad-home widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="nn-ad-label">',
        'after_title'   => '</span>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Home — Sidebar / Rail', 'neonews' ),
        'id'            => 'ad-home-sidebar',
        'description'   => esc_html__( 'Right rail ad on homepage (desktop).', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="nn-ad-widget nn-ad-home widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="nn-ad-label">',
        'after_title'   => '</span>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Home — Footer Area', 'neonews' ),
        'id'            => 'ad-home-footer',
        'description'   => esc_html__( 'Ad above site footer on homepage.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="nn-ad-widget nn-ad-home widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="nn-ad-label">',
        'after_title'   => '</span>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Single Post Ad', 'neonews' ),
        'id'            => 'ad-single',
        'description'   => esc_html__( 'Ad after article content on single posts.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="nn-ad-widget nn-ad-single widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="nn-ad-label">',
        'after_title'   => '</span>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Archive Ad', 'neonews' ),
        'id'            => 'ad-archive',
        'description'   => esc_html__( 'Ad below archive title on category/tag pages.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="nn-ad-widget nn-ad-archive widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="nn-ad-label">',
        'after_title'   => '</span>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Sidebar Ad', 'neonews' ),
        'id'            => 'ad-sidebar',
        'description'   => esc_html__( 'Ad in the sidebar on archive and other pages.', 'neonews' ),
        'before_widget' => '<div id="%1$s" class="nn-ad-widget nn-ad-sidebar widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="nn-ad-label">',
        'after_title'   => '</span>',
    ) );
}
add_action( 'widgets_init', 'neonews_register_ad_zones', 20 );

/**
 * Enqueue NewsPulse styles.
 */
function neonews_newspulse_assets() {
    wp_enqueue_style(
        'neonews-newspulse',
        NEONEWS_THEME_URI . '/css/newspulse.css',
        array( 'neonews-main' ),
        NEONEWS_THEME_VERSION
    );
}
add_action( 'wp_enqueue_scripts', 'neonews_newspulse_assets', 25 );

/**
 * Render ad zone or placeholder.
 *
 * @param string $zone_id     Sidebar / zone ID.
 * @param string $placeholder Fallback text when empty.
 * @param array  $args        Optional render args.
 */
function neonews_ad_zone( $zone_id, $placeholder = '', $args = array() ) {
    if ( ! neonews_is_ad_zone_enabled( $zone_id ) ) {
        return;
    }

    if ( false === apply_filters( 'neonews_should_render_ad_zone', true, $zone_id ) ) {
        return;
    }

    $args = wp_parse_args(
        $args,
        array(
            'mobile_sticky' => false,
        )
    );

    $settings   = neonews_get_settings();
    $gate_ads   = neonews_cookie_banner_enabled() && ! empty( $settings['cookie_gate_ads'] );
    $show_ads   = neonews_should_show_marketing_content();
    $gate_class = $gate_ads ? ' nn-consent-marketing' : '';
    $hide_class = ( $gate_ads && ! $show_ads ) ? ' nn-consent-marketing-hidden' : '';

    $leaderboard_zones = function_exists( 'neonews_get_leaderboard_ad_zones' )
        ? neonews_get_leaderboard_ad_zones()
        : array( 'ad-header', 'ad-home-hero', 'ad-home-mid', 'ad-content', 'ad-home-footer' );
    $wrap_class        = in_array( $zone_id, $leaderboard_zones, true ) ? ' nn-ad-leaderboard' : '';

    $zone_markup = '';
    ob_start();

    if ( is_active_sidebar( $zone_id ) ) {
        dynamic_sidebar( $zone_id );
    } elseif ( function_exists( 'neonews_render_adsense_unit' ) && neonews_render_adsense_unit( $zone_id ) ) {
        // AdSense rendered.
    } else {
        if ( ! $placeholder ) {
            $placeholder = __( 'Advertisement — add your ad widget in Appearance → Widgets', 'neonews' );
        }
        echo '<div class="nn-ad-placeholder">';
        echo '<span class="nn-ad-tag">' . esc_html__( 'Ad', 'neonews' ) . ' · 728×90</span>';
        echo '<p>' . esc_html( $placeholder ) . '</p>';
        echo '</div>';
    }

    $zone_markup = ob_get_clean();
    if ( '' === $zone_markup ) {
        return;
    }

    if ( $args['mobile_sticky'] ) {
        echo '<div class="nn-ad-mobile-wrap' . esc_attr( $gate_class . $hide_class ) . '">';
    }

    echo '<div class="nn-ad-zone' . esc_attr( $wrap_class . $gate_class . $hide_class ) . '" data-zone="' . esc_attr( $zone_id ) . '">';
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped helpers above.
    echo $zone_markup;
    echo '</div>';

    if ( $args['mobile_sticky'] ) {
        echo '</div>';
    }
}

/**
 * Post engagement meta bar (views, comments, share).
 */
function neonews_post_engagement_bar( $post_id = null, $show_share = true ) {
    if ( ! neonews_is_feature_enabled( 'show_engagement_bar' ) ) {
        return;
    }

    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    $show_share = $show_share && neonews_post_allows_social_share( $post_id );
    $show_likes = neonews_is_feature_enabled( 'show_post_likes' );
    $show_views = neonews_is_feature_enabled( 'show_post_views' );

    $views    = function_exists( 'neonews_get_post_views' ) ? neonews_get_post_views( $post_id ) : 0;
    // Include pending comments in the count so it updates immediately on submit,
    // before the admin approves. Spam and trash are always excluded.
    $_cc      = wp_count_comments( $post_id );
    $comments = (int) ( $_cc->approved ?? 0 ) + (int) ( $_cc->awaiting_moderation ?? 0 );
    $likes    = (int) get_post_meta( $post_id, '_neonews_likes', true );
    $liked    = is_user_logged_in() && neonews_user_liked_post( $post_id );
    ?>
    <div class="nn-engage-bar">
        <?php if ( $show_views ) : ?>
        <span class="nn-engage-item" title="<?php esc_attr_e( 'Views', 'neonews' ); ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            <?php echo esc_html( neonews_format_views( $views ) ); ?>
        </span>
        <?php endif; ?>
        <a href="<?php echo esc_url( get_comments_link( $post_id ) ); ?>" class="nn-engage-item">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            <?php echo esc_html( number_format_i18n( $comments ) ); ?>
        </a>
        <?php if ( $show_likes ) : ?>
        <?php if ( is_user_logged_in() ) : ?>
            <button type="button" class="nn-engage-item nn-like-btn<?php echo $liked ? ' liked' : ''; ?>" data-post-id="<?php echo esc_attr( $post_id ); ?>" aria-pressed="<?php echo $liked ? 'true' : 'false'; ?>">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="<?php echo $liked ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                <span class="nn-like-count"><?php echo esc_html( number_format_i18n( $likes ) ); ?></span>
            </button>
        <?php else : ?>
            <span class="nn-engage-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                <?php echo esc_html( number_format_i18n( $likes ) ); ?>
            </span>
        <?php endif; ?>
        <?php endif; ?>
        <?php if ( $show_share ) : ?>
            <span class="nn-engage-share">
                <?php neonews_share_icons( $post_id ); ?>
            </span>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Compact share icons.
 */
function neonews_share_icons( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    if ( ! neonews_post_allows_social_share( $post_id ) ) {
        return;
    }

    $url   = rawurlencode( get_permalink( $post_id ) );
    $title = rawurlencode( get_the_title( $post_id ) );
    ?>
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $url ); ?>" class="nn-share-icon" target="_blank" rel="noopener noreferrer" data-share="facebook" data-post="<?php echo esc_attr( $post_id ); ?>" aria-label="Facebook">f</a>
    <a href="https://twitter.com/intent/tweet?url=<?php echo esc_attr( $url ); ?>&text=<?php echo esc_attr( $title ); ?>" class="nn-share-icon" target="_blank" rel="noopener noreferrer" data-share="twitter" data-post="<?php echo esc_attr( $post_id ); ?>" aria-label="X">𝕏</a>
    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_attr( $url ); ?>" class="nn-share-icon" target="_blank" rel="noopener noreferrer" data-share="linkedin" data-post="<?php echo esc_attr( $post_id ); ?>" aria-label="LinkedIn">in</a>
    <?php neonews_render_whatsapp_share_icon( $post_id ); ?>
    <button type="button" class="nn-share-icon nn-copy-link" data-url="<?php echo esc_url( get_permalink( $post_id ) ); ?>" data-post="<?php echo esc_attr( $post_id ); ?>" aria-label="<?php esc_attr_e( 'Copy link', 'neonews' ); ?>">⎘</button>
    <?php
}

/**
 * Mobile drawer account section (logged-in / guest).
 */
function neonews_render_mobile_account_section() {
    if ( is_user_logged_in() ) {
        $user = wp_get_current_user();
        ?>
        <div class="nn-mobile-user-card">
            <?php
            $mobile_avatar = get_avatar( $user->ID, 48, '', '', array( 'class' => 'nn-mobile-user-avatar' ) );
            if ( class_exists( 'NeoNews_Membership_Display' ) ) {
                $mobile_avatar = NeoNews_Membership_Display::wrap_avatar_with_badge( $mobile_avatar, $user->ID );
            }
            echo $mobile_avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
            <div class="nn-mobile-user-meta">
                <strong><?php echo esc_html( $user->display_name ); ?></strong>
                <span><?php echo esc_html( $user->user_email ); ?></span>
            </div>
        </div>
        <div class="nn-mobile-auth nn-mobile-auth-logged-in">
            <a href="<?php echo esc_url( neonews_get_account_url() ); ?>" class="nn-btn nn-btn-outline nn-btn-auth"><?php esc_html_e( 'My Activity', 'neonews' ); ?></a>
            <a href="<?php echo esc_url( neonews_get_profile_url() ); ?>" class="nn-btn nn-btn-signup nn-btn-auth"><?php esc_html_e( 'Profile', 'neonews' ); ?></a>
            <a href="<?php echo esc_url( neonews_get_logout_url() ); ?>" class="nn-btn nn-btn-text nn-mobile-logout"><?php esc_html_e( 'Log Out', 'neonews' ); ?></a>
        </div>
        <?php
        return;
    }
    ?>
    <div class="nn-mobile-auth nn-mobile-auth-guest">
        <button type="button" class="nn-btn nn-btn-outline nn-btn-auth nn-open-login"><?php esc_html_e( 'Sign In', 'neonews' ); ?></button>
        <?php if ( neonews_auth_registration_enabled() ) : ?>
        <button type="button" class="nn-btn nn-btn-signup nn-btn-auth nn-open-signup"><?php esc_html_e( 'Sign up', 'neonews' ); ?></button>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Header user menu.
 */
function neonews_header_user_menu() {
    if ( is_user_logged_in() ) {
        $user = wp_get_current_user();
        $account_url = neonews_get_account_url();
        $avatar = get_avatar( $user->ID, 32 );
        if ( class_exists( 'NeoNews_Membership_Display' ) ) {
            $avatar = NeoNews_Membership_Display::wrap_avatar_with_badge( $avatar, $user->ID );
        }
        ?>
        <div class="nn-user-menu">
            <button type="button" class="nn-user-toggle" aria-expanded="false" aria-haspopup="true">
                <?php echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <span class="nn-user-name"><?php echo esc_html( $user->display_name ); ?></span>
            </button>
            <ul class="nn-user-dropdown">
                <li><a href="<?php echo esc_url( $account_url ); ?>"><?php esc_html_e( 'My Activity', 'neonews' ); ?></a></li>
                <li><a href="<?php echo esc_url( neonews_get_profile_url() ); ?>"><?php esc_html_e( 'Profile', 'neonews' ); ?></a></li>
                <li><a href="<?php echo esc_url( neonews_get_logout_url() ); ?>" class="nn-logout-link"><?php esc_html_e( 'Log Out', 'neonews' ); ?></a></li>
            </ul>
        </div>
        <?php
    } else {
        ?>
        <div class="nn-auth-header">
        <button type="button" class="nn-btn nn-btn-outline nn-btn-auth nn-open-login"><?php esc_html_e( 'Sign In', 'neonews' ); ?></button>
        <?php if ( neonews_auth_registration_enabled() ) : ?>
        <button type="button" class="nn-btn nn-btn-signup nn-btn-auth nn-open-signup"><?php esc_html_e( 'Sign up', 'neonews' ); ?></button>
        <?php endif; ?>
        </div>
        <?php
    }
}

/**
 * Account page URL.
 */
function neonews_get_account_url() {
    $page = get_page_by_path( 'my-account' );
    return $page ? get_permalink( $page ) : wp_login_url();
}

/**
 * Profile page URL.
 */
function neonews_get_profile_url() {
    $page = get_page_by_path( 'my-profile' );
    return $page ? get_permalink( $page ) : neonews_get_account_url();
}

/**
 * Logout URL with success message.
 */
function neonews_get_logout_url() {
    return wp_logout_url( add_query_arg( 'logged_out', '1', home_url( '/' ) ) );
}

/**
 * Check if user liked post.
 */
function neonews_user_liked_post( $post_id, $user_id = null ) {
    if ( ! is_user_logged_in() && ! $user_id ) {
        return false;
    }
    $user_id = $user_id ? $user_id : get_current_user_id();
    $liked   = get_user_meta( $user_id, '_neonews_liked_posts', true );
    return is_array( $liked ) && in_array( (int) $post_id, $liked, true );
}

/**
 * AJAX like toggle.
 */
function neonews_ajax_toggle_like() {
    check_ajax_referer( 'neonews_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => __( 'Please sign in to like posts.', 'neonews' ) ) );
    }

    $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
        wp_send_json_error();
    }

    $user_id = get_current_user_id();
    $liked   = get_user_meta( $user_id, '_neonews_liked_posts', true );
    if ( ! is_array( $liked ) ) {
        $liked = array();
    }

    $count = (int) get_post_meta( $post_id, '_neonews_likes', true );
    $is_liked = in_array( $post_id, $liked, true );

    if ( $is_liked ) {
        $liked = array_values( array_diff( $liked, array( $post_id ) ) );
        $count = max( 0, $count - 1 );
    } else {
        $liked[] = $post_id;
        $count++;
    }

    update_user_meta( $user_id, '_neonews_liked_posts', $liked );
    update_post_meta( $post_id, '_neonews_likes', $count );

    if ( ! $is_liked && function_exists( 'neonews_log_user_activity' ) ) {
        neonews_log_user_activity(
            'post_like',
            sprintf(
                /* translators: %s: post title */
                __( 'Liked: %s', 'neonews' ),
                get_the_title( $post_id )
            ),
            $post_id,
            array(),
            $user_id
        );
    }

    wp_send_json_success( array( 'liked' => ! $is_liked, 'count' => $count ) );
}
add_action( 'wp_ajax_neonews_toggle_like', 'neonews_ajax_toggle_like' );

/**
 * Guest (non-logged-in) like attempt — record it and return a login prompt.
 */
function neonews_ajax_guest_like() {
    check_ajax_referer( 'neonews_nonce', 'nonce' );

    $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
        wp_send_json_error();
    }

    // Record the attempt: store last 50 guest like attempts in post meta.
    $ip       = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
    $attempts = get_post_meta( $post_id, '_neonews_guest_likes', true );
    if ( ! is_array( $attempts ) ) {
        $attempts = array();
    }
    $attempts[] = array(
        'ip'   => $ip,
        'time' => current_time( 'timestamp' ),
    );
    // Keep only the last 50.
    if ( count( $attempts ) > 50 ) {
        $attempts = array_slice( $attempts, -50 );
    }
    update_post_meta( $post_id, '_neonews_guest_likes', $attempts );

    // Also bump a simple counter for quick display.
    $count = (int) get_post_meta( $post_id, '_neonews_guest_like_count', true );
    update_post_meta( $post_id, '_neonews_guest_like_count', $count + 1 );

    wp_send_json_error( array(
        'code'    => 'login_required',
        'message' => __( 'Please sign in to like posts.', 'neonews' ),
    ) );
}
add_action( 'wp_ajax_nopriv_neonews_toggle_like', 'neonews_ajax_guest_like' );

/**
 * Track share click.
 */
function neonews_ajax_track_share() {
    check_ajax_referer( 'neonews_nonce', 'nonce' );

    if ( function_exists( 'neonews_cookie_allows' ) && ! neonews_cookie_allows( 'statistics' ) ) {
        wp_send_json_error();
    }

    $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    if ( ! $post_id ) {
        wp_send_json_error();
    }

    $shares = (int) get_post_meta( $post_id, '_neonews_shares', true );
    update_post_meta( $post_id, '_neonews_shares', $shares + 1 );

    if ( is_user_logged_in() ) {
        $shared = get_user_meta( get_current_user_id(), '_neonews_shared_posts', true );
        if ( ! is_array( $shared ) ) {
            $shared = array();
        }
        if ( ! in_array( $post_id, $shared, true ) ) {
            $shared[] = $post_id;
            update_user_meta( get_current_user_id(), '_neonews_shared_posts', $shared );
        }

        if ( function_exists( 'neonews_log_user_activity' ) ) {
            neonews_log_user_activity(
                'post_share',
                sprintf(
                    /* translators: %s: post title */
                    __( 'Shared: %s', 'neonews' ),
                    get_the_title( $post_id )
                ),
                $post_id,
                array(),
                get_current_user_id()
            );
        }
    }

    wp_send_json_success( array( 'shares' => $shares + 1 ) );
}
add_action( 'wp_ajax_neonews_track_share', 'neonews_ajax_track_share' );
add_action( 'wp_ajax_nopriv_neonews_track_share', 'neonews_ajax_track_share' );

/**
 * Premium / member badge on comments.
 */
function neonews_comment_member_badge( $author, $comment_id = 0 ) {
    $comment = get_comment( $comment_id );
    if ( ! $comment ) {
        return $author;
    }
    // Guest comment — no WordPress user attached.
    if ( ! $comment->user_id ) {
        return $author . ' <span class="nn-member-badge nn-badge-guest">' . esc_html__( 'Guest', 'neonews' ) . '</span>';
    }

    $badges = array();

    if ( function_exists( 'neonews_is_user_premium' ) && neonews_is_user_premium( $comment->user_id ) ) {
        if ( class_exists( 'NeoNews_Membership_Display' ) && NeoNews_Membership::get_settings( 'show_badge_comments' ) ) {
            ob_start();
            NeoNews_Membership_Display::render_plan_tag( 'comment', $comment->user_id );
            $badges[] = ob_get_clean();
        } else {
            $badges[] = '<span class="nn-member-badge nn-badge-premium">' . esc_html__( 'Premium', 'neonews' ) . '</span>';
        }
    } elseif ( class_exists( 'NeoNews_Membership_Display' ) && NeoNews_Membership::get_settings( 'show_badge_comments' ) && $comment->user_id ) {
        ob_start();
        NeoNews_Membership_Display::render_plan_tag( 'comment', $comment->user_id );
        $badges[] = ob_get_clean();
    }

    $user = get_userdata( $comment->user_id );
    if ( $user && in_array( 'nn_premium_subscriber', (array) $user->roles, true ) ) {
        if ( ! str_contains( $author, 'nn-badge-premium' ) ) {
            $badges[] = '<span class="nn-member-badge nn-badge-member">' . esc_html__( 'Member', 'neonews' ) . '</span>';
        }
    } elseif ( $user && in_array( 'subscriber', (array) $user->roles, true ) && empty( $badges ) ) {
        $badges[] = '<span class="nn-member-badge nn-badge-subscriber">' . esc_html__( 'Subscriber', 'neonews' ) . '</span>';
    }

    if ( empty( $badges ) ) {
        return $author;
    }

    return $author . ' ' . implode( ' ', $badges );
}
add_filter( 'get_comment_author', 'neonews_comment_member_badge', 10, 2 );

/**
 * Account dashboard shortcode.
 */
function neonews_account_shortcode() {
    if ( ! is_user_logged_in() ) {
        ob_start();
        ?>
        <div class="nn-account-login nn-page-card nn-auth-cta">
            <h2><?php esc_html_e( 'Sign in to view your account', 'neonews' ); ?></h2>
            <p><?php esc_html_e( 'Track your reading history, likes, comments, and shares.', 'neonews' ); ?></p>
            <div class="nn-auth-cta-buttons">
                <button type="button" class="nn-btn nn-open-login"><?php esc_html_e( 'Sign In', 'neonews' ); ?></button>
                <?php if ( neonews_auth_registration_enabled() ) : ?>
                <button type="button" class="nn-btn nn-btn-outline nn-open-signup"><?php esc_html_e( 'Create account', 'neonews' ); ?></button>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    $user_id = get_current_user_id();
    $read    = class_exists( 'NeoNews_Activity_Tracker' ) ? NeoNews_Activity_Tracker::get_posts_read( $user_id, 500 ) : array();
    $liked   = get_user_meta( $user_id, '_neonews_liked_posts', true );
    $shared  = get_user_meta( $user_id, '_neonews_shared_posts', true );
    $comments = get_comments( array( 'user_id' => $user_id, 'number' => 500, 'status' => 'approve' ) );

    if ( ! is_array( $liked ) ) {
        $liked = array();
    }
    if ( ! is_array( $shared ) ) {
        $shared = array();
    }

    ob_start();
    ?>
    <div class="nn-account-dashboard">
        <header class="nn-account-header nn-page-card">
            <?php
            $avatar = get_avatar( $user_id, 80 );
            if ( class_exists( 'NeoNews_Membership_Display' ) ) {
                echo NeoNews_Membership_Display::wrap_avatar_with_badge( $avatar, $user_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            } else {
                echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            ?>
            <div>
                <h2><?php echo esc_html( wp_get_current_user()->display_name ); ?></h2>
                <p><?php esc_html_e( 'Your reading activity', 'neonews' ); ?></p>
                <?php
                if ( function_exists( 'neonews_membership_render_plan_tag' ) ) {
                    neonews_membership_render_plan_tag( 'account', $user_id );
                } elseif ( function_exists( 'neonews_is_user_premium' ) && neonews_is_user_premium() ) {
                    echo '<span class="nn-member-badge nn-badge-premium">' . esc_html__( 'Premium Member', 'neonews' ) . '</span>';
                }
                ?>
            </div>
            <div class="nn-account-header-actions">
                <a href="<?php echo esc_url( neonews_get_logout_url() ); ?>" class="nn-btn nn-btn-outline nn-logout-link"><?php esc_html_e( 'Log Out', 'neonews' ); ?></a>
            </div>
        </header>

        <?php do_action( 'neonews_account_membership_panel' ); ?>

        <div class="nn-account-tabs">
            <div class="nn-account-grid nn-account-visual">
                <?php neonews_account_visual_section( __( 'Recently Read', 'neonews' ), $read, 'read' ); ?>
                <?php neonews_account_visual_section( __( 'Liked Posts', 'neonews' ), array_reverse( $liked ), 'like' ); ?>
                <?php neonews_account_visual_section( __( 'Shared Posts', 'neonews' ), array_reverse( $shared ), 'share' ); ?>
                <?php neonews_account_comments_section( $comments ); ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'neonews_account', 'neonews_account_shortcode' );

/**
 * Visual activity cards grid.
 *
 * @param string $title    Section title.
 * @param array  $post_ids Post IDs.
 * @param string $type     read|like|share.
 */
function neonews_account_visual_section( $title, $post_ids, $type ) {
    $icons = array(
        'read'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
        'like'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="#ef4444" stroke="#ef4444" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>',
        'share' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>',
    );
    ?>
    <section class="nn-activity-block nn-activity-<?php echo esc_attr( $type ); ?> nn-page-card">
        <header class="nn-activity-head">
            <span class="nn-activity-icon"><?php echo $icons[ $type ] ?? ''; // phpcs:ignore ?></span>
            <h3><?php echo esc_html( $title ); ?></h3>
        </header>
        <?php if ( empty( $post_ids ) ) : ?>
            <div class="nn-activity-empty">
                <span class="nn-activity-empty-icon">📭</span>
                <p><?php esc_html_e( 'Nothing here yet — start exploring!', 'neonews' ); ?></p>
            </div>
        <?php else :
            $initial = 6;
            $total   = count( $post_ids );
            $uid     = 'nn-act-' . $type . '-' . get_current_user_id();
        ?>
            <div class="nn-activity-cards" id="<?php echo esc_attr( $uid ); ?>">
                <?php foreach ( $post_ids as $i => $pid ) :
                    $post = get_post( $pid );
                    if ( ! $post ) { continue; }
                    $thumb = get_the_post_thumbnail_url( $pid, 'neonews-card' );
                    $hidden = $i >= $initial ? ' nn-act-hidden' : '';
                    ?>
                    <a href="<?php echo esc_url( get_permalink( $pid ) ); ?>" class="nn-activity-card<?php echo esc_attr( $hidden ); ?>">
                        <span class="nn-activity-card-thumb">
                            <?php if ( $thumb ) : ?>
                                <img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy" />
                            <?php else : ?>
                                <span class="nn-activity-card-fallback"></span>
                            <?php endif; ?>
                        </span>
                        <span class="nn-activity-card-body">
                            <span class="nn-activity-card-title"><?php echo esc_html( get_the_title( $pid ) ); ?></span>
                            <span class="nn-activity-card-meta">
                                <?php echo esc_html( get_the_date( '', $pid ) ); ?>
                                <?php if ( neonews_is_feature_enabled( 'show_post_views' ) ) : ?>
                                · <?php echo esc_html( neonews_format_views( neonews_get_post_views( $pid ) ) ); ?> <?php esc_html_e( 'views', 'neonews' ); ?>
                                <?php endif; ?>
                            </span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if ( $total > $initial ) : ?>
            <div class="nn-act-more-wrap">
                <button type="button" class="nn-act-more-btn" data-target="<?php echo esc_attr( $uid ); ?>" data-total="<?php echo esc_attr( $total ); ?>" data-initial="<?php echo esc_attr( $initial ); ?>">
                    <?php printf( esc_html__( 'View all %d →', 'neonews' ), $total ); ?>
                </button>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php
}

/**
 * Visual comments section.
 *
 * @param array $comments Comment objects.
 */
function neonews_account_comments_section( $comments ) {
    $initial_c = 6;
    $total_c   = count( $comments );
    $uid_c     = 'nn-act-comments-' . get_current_user_id();
    ?>
    <section class="nn-activity-block nn-activity-comments nn-page-card">
        <header class="nn-activity-head">
            <span class="nn-activity-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg></span>
            <h3><?php esc_html_e( 'Your Comments', 'neonews' ); ?></h3>
        </header>
        <?php if ( empty( $comments ) ) : ?>
            <div class="nn-activity-empty"><p><?php esc_html_e( 'No comments yet.', 'neonews' ); ?></p></div>
        <?php else : ?>
            <div class="nn-comment-cards" id="<?php echo esc_attr( $uid_c ); ?>">
                <?php foreach ( $comments as $ci => $c ) :
                    $hidden_c = $ci >= $initial_c ? ' nn-act-hidden' : '';
                ?>
                    <a href="<?php echo esc_url( get_comment_link( $c ) ); ?>" class="nn-comment-card<?php echo esc_attr( $hidden_c ); ?>">
                        <span class="nn-comment-quote">“</span>
                        <span class="nn-comment-text"><?php echo esc_html( wp_trim_words( $c->comment_content, 18 ) ); ?></span>
                        <span class="nn-comment-meta"><?php echo esc_html( get_the_title( $c->comment_post_ID ) ); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if ( $total_c > $initial_c ) : ?>
            <div class="nn-act-more-wrap">
                <button type="button" class="nn-act-more-btn" data-target="<?php echo esc_attr( $uid_c ); ?>" data-total="<?php echo esc_attr( $total_c ); ?>" data-initial="<?php echo esc_attr( $initial_c ); ?>">
                    <?php printf( esc_html__( 'View all %d →', 'neonews' ), $total_c ); ?>
                </button>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php
}

/**
 * Legacy list helper (unused).
 */
function neonews_account_section( $title, $post_ids, $type ) {
    neonews_account_visual_section( $title, $post_ids, $type );
}

/**
 * Videos section query.
 */
function neonews_get_video_posts( $limit = 4 ) {
    $slug = sanitize_title( neonews_get_setting( 'home_video_category', 'videos' ) );
    $cat  = get_category_by_slug( $slug );
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => absint( $limit ),
        'no_found_rows'  => true,
    );
    if ( $cat ) {
        $args['cat'] = $cat->term_id;
    } else {
        $args['tag'] = 'video';
    }
    return new WP_Query( $args );
}

/**
 * Back to top + scroll reveal in footer.
 */
function neonews_footer_extras() {
    ?>
    <button type="button" id="nn-back-to-top" class="nn-back-to-top" aria-label="<?php esc_attr_e( 'Back to top', 'neonews' ); ?>">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
    </button>
    <?php
    if ( neonews_is_ad_zone_enabled( 'ad-mobile' ) ) {
        neonews_ad_zone(
            'ad-mobile',
            __( '320×50 — Mobile sticky placement', 'neonews' ),
            array( 'mobile_sticky' => true )
        );
    }

    neonews_render_auth_modal();
    neonews_render_toast_container();
}
add_action( 'wp_footer', 'neonews_footer_extras', 5 );

/**
 * Toast container.
 */
function neonews_render_toast_container() {
    $message = '';
    if ( isset( $_GET['logged_out'] ) && '1' === $_GET['logged_out'] ) {
        $message = __( 'You have been logged out successfully.', 'neonews' );
    }
    ?>
    <div id="nn-toast" class="nn-toast<?php echo $message ? ' nn-toast-visible' : ''; ?>" role="status" aria-live="polite">
        <span class="nn-toast-text"><?php echo esc_html( $message ); ?></span>
    </div>
    <?php
}

/**
 * Contact form handler.
 */
function neonews_handle_contact_form() {
    if ( ! isset( $_POST['neonews_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['neonews_contact_nonce'] ) ), 'neonews_contact_form' ) ) {
        wp_die( esc_html__( 'Invalid request.', 'neonews' ) );
    }

    $name    = isset( $_POST['contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_name'] ) ) : '';
    $email   = isset( $_POST['contact_email'] ) ? sanitize_email( wp_unslash( $_POST['contact_email'] ) ) : '';
    $message = isset( $_POST['contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['contact_message'] ) ) : '';

    if ( $name && is_email( $email ) && $message ) {
        wp_mail(
            get_option( 'admin_email' ),
            sprintf( '[%s] %s', get_bloginfo( 'name' ), __( 'Contact form', 'neonews' ) ),
            sprintf( "From: %s <%s>\n\n%s", $name, $email, $message ),
            array( 'Reply-To: ' . $email )
        );
    }

    $contact_page = get_page_by_path( 'contact' );
    $redirect     = $contact_page ? get_permalink( $contact_page ) : home_url( '/' );
    wp_safe_redirect( add_query_arg( 'contact', 'sent', $redirect ) );
    exit;
}
add_action( 'admin_post_nopriv_neonews_contact_form', 'neonews_handle_contact_form' );
add_action( 'admin_post_neonews_contact_form', 'neonews_handle_contact_form' );

/**
 * YouTube URL meta box for posts.
 */
function neonews_youtube_meta_box() {
    add_meta_box(
        'neonews_youtube',
        __( 'YouTube Video', 'neonews' ),
        'neonews_youtube_meta_box_render',
        'post',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'neonews_youtube_meta_box' );

/**
 * Render YouTube meta box.
 *
 * @param WP_Post $post Post object.
 */
function neonews_youtube_meta_box_render( $post ) {
    wp_nonce_field( 'neonews_youtube_save', 'neonews_youtube_nonce' );
    $url      = get_post_meta( $post->ID, '_neonews_youtube_url', true );
    $duration = get_post_meta( $post->ID, '_neonews_video_duration', true );
    ?>
    <p>
        <label for="neonews_youtube_url"><strong><?php esc_html_e( 'YouTube URL', 'neonews' ); ?></strong></label>
        <input type="url" id="neonews_youtube_url" name="neonews_youtube_url" value="<?php echo esc_attr( $url ); ?>" class="widefat" placeholder="https://www.youtube.com/watch?v=..." />
    </p>
    <p>
        <label for="neonews_video_duration"><strong><?php esc_html_e( 'Duration', 'neonews' ); ?></strong></label>
        <input type="text" id="neonews_video_duration" name="neonews_video_duration" value="<?php echo esc_attr( $duration ); ?>" class="widefat" placeholder="12:48" />
    </p>
    <p class="description"><?php esc_html_e( 'Add a YouTube link for video posts. Thumbnail and embed are generated automatically.', 'neonews' ); ?></p>
    <?php
}

/**
 * Save YouTube meta.
 *
 * @param int $post_id Post ID.
 */
function neonews_save_youtube_meta( $post_id ) {
    if ( ! isset( $_POST['neonews_youtube_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['neonews_youtube_nonce'] ) ), 'neonews_youtube_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['neonews_youtube_url'] ) ) {
        $url = esc_url_raw( wp_unslash( $_POST['neonews_youtube_url'] ) );
        update_post_meta( $post_id, '_neonews_youtube_url', $url );
    }
    if ( isset( $_POST['neonews_video_duration'] ) ) {
        update_post_meta( $post_id, '_neonews_video_duration', sanitize_text_field( wp_unslash( $_POST['neonews_video_duration'] ) ) );
    }
}
add_action( 'save_post', 'neonews_save_youtube_meta' );

/* ============================================================
   GUEST ACTIVITY — admin columns + dashboard widget
   ============================================================ */

/**
 * Add "Guest Likes" column to posts list table.
 */
function neonews_guest_like_column( $columns ) {
    $columns['guest_likes'] = '<span title="' . esc_attr__( 'Guest Like Attempts', 'neonews' ) . '">👁 ' . esc_html__( 'Guest Likes', 'neonews' ) . '</span>';
    return $columns;
}
add_filter( 'manage_posts_columns', 'neonews_guest_like_column' );

function neonews_guest_like_column_value( $column, $post_id ) {
    if ( 'guest_likes' !== $column ) {
        return;
    }
    $count = (int) get_post_meta( $post_id, '_neonews_guest_like_count', true );
    if ( $count > 0 ) {
        printf( '<strong style="color:#d63638;">%d</strong>', $count );
    } else {
        echo '<span style="color:#999;">0</span>';
    }
}
add_action( 'manage_posts_custom_column', 'neonews_guest_like_column_value', 10, 2 );

function neonews_guest_like_column_sortable( $columns ) {
    $columns['guest_likes'] = 'guest_likes';
    return $columns;
}
add_filter( 'manage_edit-post_sortable_columns', 'neonews_guest_like_column_sortable' );

function neonews_guest_like_column_orderby( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( 'guest_likes' === $query->get( 'orderby' ) ) {
        $query->set( 'meta_key', '_neonews_guest_like_count' );
        $query->set( 'orderby', 'meta_value_num' );
    }
}
add_action( 'pre_get_posts', 'neonews_guest_like_column_orderby' );

/**
 * Dashboard widget — recent guest like attempts.
 */
function neonews_register_guest_activity_widget() {
    wp_add_dashboard_widget(
        'neonews_guest_activity',
        __( '👥 Guest Activity (Likes & Comments)', 'neonews' ),
        'neonews_render_guest_activity_widget'
    );
}
add_action( 'wp_dashboard_setup', 'neonews_register_guest_activity_widget' );

function neonews_render_guest_activity_widget() {
    // ── Guest likes ──────────────────────────────────────────
    $like_posts = get_posts( array(
        'post_type'      => 'post',
        'posts_per_page' => 10,
        'meta_key'       => '_neonews_guest_like_count',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'meta_query'     => array( array(
            'key'     => '_neonews_guest_like_count',
            'value'   => 0,
            'compare' => '>',
            'type'    => 'NUMERIC',
        ) ),
    ) );

    echo '<h4 style="margin:0 0 8px;">' . esc_html__( 'Posts with Guest Like Attempts', 'neonews' ) . '</h4>';
    if ( $like_posts ) {
        echo '<table style="width:100%;border-collapse:collapse;font-size:0.82rem;">';
        echo '<tr style="border-bottom:1px solid #eee;"><th style="text-align:left;padding:4px 6px;">' . esc_html__( 'Post', 'neonews' ) . '</th><th style="text-align:center;padding:4px 6px;">' . esc_html__( 'Attempts', 'neonews' ) . '</th><th style="text-align:left;padding:4px 6px;">' . esc_html__( 'Last IP', 'neonews' ) . '</th></tr>';
        foreach ( $like_posts as $p ) {
            $count    = (int) get_post_meta( $p->ID, '_neonews_guest_like_count', true );
            $attempts = get_post_meta( $p->ID, '_neonews_guest_likes', true );
            $last_ip  = '';
            if ( is_array( $attempts ) && ! empty( $attempts ) ) {
                $last     = end( $attempts );
                $last_ip  = esc_html( $last['ip'] ?? '' );
            }
            printf(
                '<tr style="border-bottom:1px solid #f5f5f5;"><td style="padding:4px 6px;"><a href="%s">%s</a></td><td style="text-align:center;padding:4px 6px;"><strong style="color:#d63638;">%d</strong></td><td style="padding:4px 6px;color:#888;">%s</td></tr>',
                esc_url( get_edit_post_link( $p->ID ) ),
                esc_html( get_the_title( $p->ID ) ),
                $count,
                $last_ip
            );
        }
        echo '</table>';
    } else {
        echo '<p style="color:#888;font-size:0.82rem;">' . esc_html__( 'No guest like attempts yet.', 'neonews' ) . '</p>';
    }

    // ── Guest comments ───────────────────────────────────────
    $guest_comments = get_comments( array(
        'user_id'  => 0,
        'status'   => 'approve',
        'number'   => 8,
        'orderby'  => 'comment_date',
        'order'    => 'DESC',
    ) );

    echo '<hr style="margin:14px 0;"><h4 style="margin:0 0 8px;">' . esc_html__( 'Recent Guest Comments', 'neonews' ) . '</h4>';
    if ( $guest_comments ) {
        echo '<table style="width:100%;border-collapse:collapse;font-size:0.82rem;">';
        echo '<tr style="border-bottom:1px solid #eee;"><th style="text-align:left;padding:4px 6px;">' . esc_html__( 'Name', 'neonews' ) . '</th><th style="text-align:left;padding:4px 6px;">' . esc_html__( 'Post', 'neonews' ) . '</th><th style="text-align:left;padding:4px 6px;">' . esc_html__( 'Date', 'neonews' ) . '</th></tr>';
        foreach ( $guest_comments as $c ) {
            printf(
                '<tr style="border-bottom:1px solid #f5f5f5;"><td style="padding:4px 6px;">%s <span style="color:#888;font-size:0.75rem;">(%s)</span></td><td style="padding:4px 6px;"><a href="%s">%s</a></td><td style="padding:4px 6px;color:#888;">%s</td></tr>',
                esc_html( $c->comment_author ),
                esc_html( $c->comment_author_email ?: __( 'no email', 'neonews' ) ),
                esc_url( get_edit_post_link( $c->comment_post_ID ) ),
                esc_html( get_the_title( $c->comment_post_ID ) ),
                esc_html( mysql2date( get_option( 'date_format' ), $c->comment_date ) )
            );
        }
        echo '</table>';
    } else {
        echo '<p style="color:#888;font-size:0.82rem;">' . esc_html__( 'No guest comments yet.', 'neonews' ) . '</p>';
    }
}
