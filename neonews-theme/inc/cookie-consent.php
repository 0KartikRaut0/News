<?php
/**
 * Cookie consent banner — full GDPR-style gating.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Is cookie banner enabled?
 *
 * @return bool
 */
function neonews_cookie_banner_enabled() {
    $settings = neonews_get_settings();
    return ! empty( $settings['cookie_banner_enabled'] );
}

/**
 * Current consent level: all, essential, or empty if not chosen.
 *
 * @return string
 */
function neonews_get_cookie_consent_level() {
    if ( ! neonews_cookie_banner_enabled() ) {
        return 'all';
    }

    if ( isset( $_COOKIE['neonews_cookie_consent'] ) ) {
        $value = sanitize_text_field( wp_unslash( $_COOKIE['neonews_cookie_consent'] ) );
        if ( in_array( $value, array( 'all', 'essential' ), true ) ) {
            return $value;
        }
    }

    if ( is_user_logged_in() ) {
        $stored = get_user_meta( get_current_user_id(), '_neonews_cookie_consent', true );
        if ( in_array( $stored, array( 'all', 'essential' ), true ) ) {
            return $stored;
        }
    }

    return '';
}

/**
 * Has user given any consent?
 *
 * @return bool
 */
function neonews_has_cookie_consent() {
    $level = neonews_get_cookie_consent_level();
    return in_array( $level, array( 'all', 'essential' ), true );
}

/**
 * May a non-essential category run?
 *
 * @param string $category analytics|marketing|statistics
 * @return bool
 */
function neonews_cookie_allows( $category ) {
    if ( ! neonews_cookie_banner_enabled() ) {
        return true;
    }

    $settings = neonews_get_settings();
    $level    = neonews_get_cookie_consent_level();

    if ( 'all' === $level ) {
        return true;
    }

    if ( 'analytics' === $category ) {
        if ( empty( $settings['analytics_enabled'] ) || empty( $settings['google_analytics_id'] ) ) {
            return true;
        }
        return false;
    }

    if ( 'marketing' === $category ) {
        if ( empty( $settings['cookie_gate_ads'] ) ) {
            return true;
        }
        return false;
    }

    if ( 'statistics' === $category ) {
        if ( empty( $settings['cookie_gate_statistics'] ) ) {
            return true;
        }
        return false;
    }

    return false;
}

/**
 * Should marketing ad zones render?
 *
 * @return bool
 */
function neonews_should_show_marketing_content() {
    return neonews_cookie_allows( 'marketing' );
}

/**
 * Consent data for JavaScript.
 *
 * @return array
 */
function neonews_get_consent_js_config() {
    $settings = neonews_get_settings();

    $config = array(
        'bannerEnabled'   => neonews_cookie_banner_enabled(),
        'level'           => neonews_get_cookie_consent_level(),
        'analyticsEnabled'=> ! empty( $settings['analytics_enabled'] ) && ! empty( $settings['google_analytics_id'] ),
        'gaId'            => ! empty( $settings['google_analytics_id'] ) ? sanitize_text_field( $settings['google_analytics_id'] ) : '',
        'gateAds'         => ! empty( $settings['cookie_gate_ads'] ),
        'gateStatistics'  => ! empty( $settings['cookie_gate_statistics'] ),
        'hasCustomScript' => ! empty( $settings['cookie_custom_script'] ),
    );

    return apply_filters( 'neonews_consent_js_config', $config );
}

/**
 * Body classes for consent state.
 *
 * @param array $classes Classes.
 * @return array
 */
function neonews_cookie_consent_body_classes( $classes ) {
    $level = neonews_get_cookie_consent_level();
    if ( $level ) {
        $classes[] = 'nn-consent-' . $level;
    } else {
        $classes[] = 'nn-consent-pending';
    }

    if ( ! neonews_cookie_allows( 'marketing' ) ) {
        $classes[] = 'nn-consent-no-marketing';
    }

    if ( ! neonews_cookie_allows( 'analytics' ) ) {
        $classes[] = 'nn-consent-no-analytics';
    }

    if ( ! neonews_cookie_allows( 'statistics' ) ) {
        $classes[] = 'nn-consent-no-statistics';
    }

    return $classes;
}
add_filter( 'body_class', 'neonews_cookie_consent_body_classes', 20 );

/**
 * Render cookie banner.
 */
function neonews_render_cookie_banner() {
    if ( ! neonews_cookie_banner_enabled() ) {
        return;
    }

    $cookies_url = neonews_get_legal_page_url( 'cookies-policy' );
    $privacy_url = neonews_get_legal_page_url( 'privacy-policy' );
    $hidden      = neonews_has_cookie_consent() ? ' nn-cookie-hidden' : '';
    ?>
    <div id="nn-cookie-banner" class="nn-cookie-banner<?php echo esc_attr( $hidden ); ?>" role="dialog" aria-live="polite" aria-label="<?php esc_attr_e( 'Cookie consent', 'neonews' ); ?>" aria-hidden="<?php echo neonews_has_cookie_consent() ? 'true' : 'false'; ?>">
        <div class="nn-cookie-inner nn-container">
            <div class="nn-cookie-text">
                <strong><?php esc_html_e( 'We use cookies', 'neonews' ); ?></strong>
                <p><?php esc_html_e( 'Essential cookies run the site (login, preferences). Analytics, ads, and read statistics only load if you accept all cookies.', 'neonews' ); ?>
                    <a href="<?php echo esc_url( $cookies_url ); ?>"><?php esc_html_e( 'Cookies Policy', 'neonews' ); ?></a> ·
                    <a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy', 'neonews' ); ?></a>
                </p>
            </div>
            <div class="nn-cookie-actions">
                <button type="button" class="nn-btn nn-btn-outline nn-cookie-essential" data-consent="essential"><?php esc_html_e( 'Essential only', 'neonews' ); ?></button>
                <button type="button" class="nn-btn nn-cookie-accept" data-consent="all"><?php esc_html_e( 'Accept all cookies', 'neonews' ); ?></button>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'neonews_render_cookie_banner', 15 );

/**
 * Output Google Analytics when consent allows.
 */
function neonews_output_analytics_scripts() {
    if ( ! neonews_cookie_allows( 'analytics' ) ) {
        return;
    }

    $settings = neonews_get_settings();
    if ( empty( $settings['analytics_enabled'] ) || empty( $settings['google_analytics_id'] ) ) {
        neonews_output_custom_consent_scripts();
        return;
    }

    $ga_id = sanitize_text_field( $settings['google_analytics_id'] );
    ?>
    <script async src="<?php echo esc_url( 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $ga_id ) ); ?>"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', <?php echo wp_json_encode( $ga_id ); ?>, { anonymize_ip: true });
    window.nnGaLoaded = true;
    </script>
    <?php

    neonews_output_custom_consent_scripts();
}
add_action( 'wp_head', 'neonews_output_analytics_scripts', 99 );

/**
 * Optional third-party scripts (admin paste) — only after full consent.
 */
function neonews_output_custom_consent_scripts() {
    if ( 'all' !== neonews_get_cookie_consent_level() ) {
        return;
    }

    $settings = neonews_get_settings();
    if ( empty( $settings['cookie_custom_script'] ) ) {
        return;
    }

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted admin-only field.
    echo "\n" . $settings['cookie_custom_script'] . "\n";
}

/**
 * Gate view tracking in NeoNews Core.
 *
 * @param bool $allow Allow tracking.
 * @return bool
 */
function neonews_filter_allow_view_tracking( $allow ) {
    return $allow && neonews_cookie_allows( 'statistics' );
}
add_filter( 'neonews_allow_view_tracking', 'neonews_filter_allow_view_tracking' );

/**
 * AJAX save consent.
 */
function neonews_ajax_cookie_consent() {
    check_ajax_referer( 'neonews_nonce', 'nonce' );

    $consent = isset( $_POST['consent'] ) ? sanitize_text_field( wp_unslash( $_POST['consent'] ) ) : '';
    if ( ! in_array( $consent, array( 'all', 'essential', 'rejected' ), true ) ) {
        wp_send_json_error();
    }

    if ( is_user_logged_in() ) {
        update_user_meta( get_current_user_id(), '_neonews_cookie_consent', $consent );
        update_user_meta( get_current_user_id(), '_neonews_cookie_consent_date', current_time( 'mysql' ) );
    }

    wp_send_json_success(
        array(
            'consent' => $consent,
            'config'  => neonews_get_consent_js_config(),
        )
    );
}
add_action( 'wp_ajax_neonews_cookie_consent', 'neonews_ajax_cookie_consent' );
add_action( 'wp_ajax_nopriv_neonews_cookie_consent', 'neonews_ajax_cookie_consent' );
