<?php
/**
 * Google AdSense integration for NeoNews ad zones.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * All registered ad zone IDs.
 *
 * @return string[]
 */
function neonews_get_ad_zone_ids() {
    return array(
        'ad-header',
        'ad-content',
        'ad-mobile',
        'ad-home-hero',
        'ad-home-mid',
        'ad-home-videos',
        'ad-home-sidebar',
        'ad-home-footer',
        'ad-single',
        'ad-archive',
        'ad-sidebar',
    );
}

/**
 * Leaderboard-style zones (wide horizontal placements).
 *
 * @return string[]
 */
function neonews_get_leaderboard_ad_zones() {
    return array(
        'ad-header',
        'ad-home-hero',
        'ad-home-mid',
        'ad-content',
        'ad-home-footer',
        'ad-single',
        'ad-archive',
    );
}

/**
 * Is AdSense enabled with a valid publisher ID?
 *
 * @return bool
 */
function neonews_adsense_is_active() {
    $settings = neonews_get_settings();

    if ( empty( $settings['ads_master'] ) || empty( $settings['adsense_enabled'] ) ) {
        return false;
    }

    return (bool) neonews_get_adsense_publisher_id();
}

/**
 * Sanitized AdSense publisher ID (ca-pub-…).
 *
 * @return string
 */
function neonews_get_adsense_publisher_id() {
    $settings = neonews_get_settings();
    $raw      = isset( $settings['adsense_publisher_id'] ) ? (string) $settings['adsense_publisher_id'] : '';
    $raw      = trim( $raw );

    if ( preg_match( '/^ca-pub-\d+$/i', $raw ) ) {
        return strtolower( $raw );
    }

    return '';
}

/**
 * Default AdSense slot ID (fallback for all zones).
 *
 * @return string
 */
function neonews_get_adsense_default_slot_id() {
    $settings = neonews_get_settings();
    $slot     = isset( $settings['adsense_default_slot_id'] ) ? (string) $settings['adsense_default_slot_id'] : '';
    $slot     = preg_replace( '/\D/', '', $slot );

    return $slot;
}

/**
 * Slot ID for a zone (zone-specific, then default fallback).
 *
 * @param string $zone_id Zone ID.
 * @return string
 */
function neonews_get_adsense_slot_id( $zone_id ) {
    $settings = neonews_get_settings();
    $slots    = isset( $settings['adsense_slots'] ) && is_array( $settings['adsense_slots'] ) ? $settings['adsense_slots'] : array();
    $slot     = isset( $slots[ $zone_id ] ) ? (string) $slots[ $zone_id ] : '';
    $slot     = preg_replace( '/\D/', '', $slot );

    if ( '' !== $slot ) {
        return $slot;
    }

    return neonews_get_adsense_default_slot_id();
}

/**
 * Whether a zone has a configured AdSense slot.
 *
 * @param string $zone_id Zone ID.
 * @return bool
 */
function neonews_adsense_has_zone_slot( $zone_id ) {
    if ( ! neonews_adsense_is_active() || ! neonews_is_ad_zone_enabled( $zone_id ) ) {
        return false;
    }

    return '' !== neonews_get_adsense_slot_id( $zone_id );
}

/**
 * Count enabled ad zones that have an AdSense slot (direct or default).
 *
 * @return array{ready:int,total:int}
 */
function neonews_get_adsense_zone_coverage() {
    $ready = 0;
    $total = 0;

    foreach ( neonews_get_ad_zone_ids() as $zone_id ) {
        if ( ! neonews_is_ad_zone_enabled( $zone_id ) ) {
            continue;
        }

        ++$total;
        if ( neonews_adsense_has_zone_slot( $zone_id ) ) {
            ++$ready;
        }
    }

    return array(
        'ready' => $ready,
        'total' => $total,
    );
}

/**
 * Publisher ID for ads.txt (pub-XXXXXXXXXXXXXXXX).
 *
 * @return string
 */
function neonews_get_adsense_pub_id_for_ads_txt() {
    $client = neonews_get_adsense_publisher_id();
    if ( ! $client ) {
        return '';
    }

    return preg_replace( '/^ca-/i', '', $client );
}

/**
 * Public ads.txt URL when AdSense is configured.
 *
 * @return string
 */
function neonews_get_ads_txt_url() {
    if ( ! neonews_get_adsense_pub_id_for_ads_txt() ) {
        return '';
    }

    return home_url( '/ads.txt' );
}

/**
 * Serve ads.txt for Google AdSense verification.
 */
function neonews_serve_ads_txt() {
    if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
        return;
    }

    $path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
    if ( '/ads.txt' !== untrailingslashit( (string) $path ) ) {
        return;
    }

    $pub_id = neonews_get_adsense_pub_id_for_ads_txt();
    if ( ! $pub_id ) {
        return;
    }

    status_header( 200 );
    header( 'Content-Type: text/plain; charset=utf-8' );
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plain text response.
    echo 'google.com, ' . $pub_id . ", DIRECT, f08e47fec0942fa0\n";
    exit;
}
add_action( 'template_redirect', 'neonews_serve_ads_txt', 0 );

/**
 * Should the AdSense library load on this request?
 *
 * @return bool
 */
function neonews_should_load_adsense_script() {
    if ( ! neonews_adsense_is_active() ) {
        return false;
    }

    if ( ! neonews_should_show_marketing_content() ) {
        return false;
    }

    if ( false === apply_filters( 'neonews_should_render_ad_zone', true, 'adsense-script' ) ) {
        return false;
    }

    $settings = neonews_get_settings();
    if ( ! empty( $settings['adsense_auto_ads'] ) ) {
        return true;
    }

    foreach ( neonews_get_ad_zone_ids() as $zone_id ) {
        if ( neonews_adsense_has_zone_slot( $zone_id ) && neonews_is_ad_zone_enabled( $zone_id ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Ad format hints per zone.
 *
 * @param string $zone_id Zone ID.
 * @return array{format?:string,full_width?:bool}
 */
function neonews_get_adsense_zone_format( $zone_id ) {
    $sidebar_zones = array( 'ad-home-sidebar', 'ad-sidebar' );
    $mobile_zones  = array( 'ad-mobile' );

    if ( in_array( $zone_id, $sidebar_zones, true ) ) {
        return array(
            'format'     => 'vertical',
            'full_width' => true,
        );
    }

    if ( in_array( $zone_id, $mobile_zones, true ) ) {
        return array(
            'format'     => 'auto',
            'full_width' => true,
        );
    }

    return array(
        'format'     => 'auto',
        'full_width' => true,
    );
}

/**
 * Render an AdSense unit for a zone.
 *
 * @param string $zone_id Zone ID.
 * @return bool True when markup was output.
 */
function neonews_render_adsense_unit( $zone_id ) {
    if ( ! neonews_adsense_has_zone_slot( $zone_id ) ) {
        return false;
    }

    $client = neonews_get_adsense_publisher_id();
    $slot   = neonews_get_adsense_slot_id( $zone_id );
    $format = neonews_get_adsense_zone_format( $zone_id );

    echo '<div class="nn-ad-adsense nn-ad-adsense-' . esc_attr( $zone_id ) . '">';
    echo '<ins class="adsbygoogle" style="display:block"';
    echo ' data-ad-client="' . esc_attr( $client ) . '"';
    echo ' data-ad-slot="' . esc_attr( $slot ) . '"';

    if ( ! empty( $format['format'] ) ) {
        echo ' data-ad-format="' . esc_attr( $format['format'] ) . '"';
    }
    if ( ! empty( $format['full_width'] ) ) {
        echo ' data-full-width-responsive="true"';
    }

    echo '></ins>';

    if ( neonews_should_load_adsense_script() ) {
        echo '<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
    }

    echo '</div>';

    return true;
}

/**
 * Output AdSense head script when consent allows on first paint.
 */
function neonews_output_adsense_head_script() {
    if ( ! neonews_should_load_adsense_script() ) {
        return;
    }

    $client = neonews_get_adsense_publisher_id();
    ?>
    <script async src="<?php echo esc_url( 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . rawurlencode( $client ) ); ?>" crossorigin="anonymous"></script>
    <?php
}
add_action( 'wp_head', 'neonews_output_adsense_head_script', 100 );

/**
 * Extend consent JS config with AdSense settings.
 *
 * @param array $config Config array.
 * @return array
 */
function neonews_adsense_consent_config( $config ) {
    $settings = neonews_get_settings();

    $config['adsenseEnabled']     = neonews_adsense_is_active();
    $config['adsensePublisherId'] = neonews_get_adsense_publisher_id();
    $config['adsenseAutoAds']     = ! empty( $settings['adsense_auto_ads'] );

    return $config;
}
add_filter( 'neonews_consent_js_config', 'neonews_adsense_consent_config' );
