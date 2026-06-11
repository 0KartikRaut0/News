<?php
/**
 * Weather widget — Open-Meteo (no API key required).
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Is weather enabled?
 *
 * @return bool
 */
function neonews_weather_enabled() {
    $settings = neonews_get_settings();
    return ! empty( $settings['weather_enabled'] );
}

/**
 * Weather data attributes for JS.
 *
 * @return array
 */
function neonews_get_weather_data_attrs() {
    $settings = neonews_get_settings();
    return array(
        'city' => ! empty( $settings['weather_city'] ) ? $settings['weather_city'] : 'New York',
        'lat'  => isset( $settings['weather_lat'] ) ? $settings['weather_lat'] : '',
        'lon'  => isset( $settings['weather_lon'] ) ? $settings['weather_lon'] : '',
    );
}

/**
 * Compact weather chip for the header datetime bar.
 */
function neonews_render_weather_header() {
    if ( ! neonews_weather_enabled() ) {
        return;
    }

    $attrs = neonews_get_weather_data_attrs();
    ?>
    <div class="nn-header-weather" id="nn-weather-header" data-city="<?php echo esc_attr( $attrs['city'] ); ?>" data-lat="<?php echo esc_attr( $attrs['lat'] ); ?>" data-lon="<?php echo esc_attr( $attrs['lon'] ); ?>" aria-live="polite">
        <span class="nn-header-weather-loading"><?php esc_html_e( 'Weather…', 'neonews' ); ?></span>
    </div>
    <?php
}

/**
 * Render weather widget shell (JS fills data).
 */
function neonews_render_weather_widget() {
    if ( ! neonews_weather_enabled() ) {
        return;
    }

    $attrs = neonews_get_weather_data_attrs();
    ?>
    <div class="nn-widget nn-widget-graphical nn-weather-widget" id="nn-weather-widget" data-city="<?php echo esc_attr( $attrs['city'] ); ?>" data-lat="<?php echo esc_attr( $attrs['lat'] ); ?>" data-lon="<?php echo esc_attr( $attrs['lon'] ); ?>">
        <h3 class="nn-widget-title"><span class="nn-widget-icon">🌤</span> <?php esc_html_e( 'Weather', 'neonews' ); ?></h3>
        <div class="nn-weather-body">
            <div class="nn-weather-loading"><?php esc_html_e( 'Loading forecast…', 'neonews' ); ?></div>
        </div>
    </div>
    <?php
}

/**
 * AJAX weather data.
 */
function neonews_ajax_weather() {
    check_ajax_referer( 'neonews_nonce', 'nonce' );

    $city = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : 'New York';
    $lat  = isset( $_POST['lat'] ) ? floatval( $_POST['lat'] ) : 0;
    $lon  = isset( $_POST['lon'] ) ? floatval( $_POST['lon'] ) : 0;

    $cache_key = 'neonews_weather_' . md5( $city . $lat . $lon );
    $cached    = get_transient( $cache_key );
    if ( false !== $cached ) {
        wp_send_json_success( $cached );
    }

    if ( ! $lat || ! $lon ) {
        $geo = wp_remote_get( add_query_arg( array(
            'name'  => $city,
            'count' => 1,
        ), 'https://geocoding-api.open-meteo.com/v1/search' ), array( 'timeout' => 8 ) );

        if ( is_wp_error( $geo ) ) {
            wp_send_json_error( array( 'message' => __( 'Could not find location.', 'neonews' ) ) );
        }

        $geo_body = json_decode( wp_remote_retrieve_body( $geo ), true );
        if ( empty( $geo_body['results'][0] ) ) {
            wp_send_json_error( array( 'message' => __( 'City not found.', 'neonews' ) ) );
        }

        $lat          = $geo_body['results'][0]['latitude'];
        $lon          = $geo_body['results'][0]['longitude'];
        $city         = $geo_body['results'][0]['name'];
        $country      = isset( $geo_body['results'][0]['country'] ) ? $geo_body['results'][0]['country'] : '';
    } else {
        $country = '';
    }

    $forecast = wp_remote_get( add_query_arg( array(
        'latitude'  => $lat,
        'longitude' => $lon,
        'current'   => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m',
        'daily'     => 'weather_code,temperature_2m_max,temperature_2m_min',
        'timezone'  => 'auto',
        'forecast_days' => 3,
    ), 'https://api.open-meteo.com/v1/forecast' ), array( 'timeout' => 8 ) );

    if ( is_wp_error( $forecast ) ) {
        wp_send_json_error( array( 'message' => __( 'Weather service unavailable.', 'neonews' ) ) );
    }

    $data = json_decode( wp_remote_retrieve_body( $forecast ), true );
    if ( empty( $data['current'] ) ) {
        wp_send_json_error( array( 'message' => __( 'No forecast data.', 'neonews' ) ) );
    }

    $result = array(
        'city'        => $city,
        'country'     => $country,
        'temp'        => round( $data['current']['temperature_2m'] ),
        'humidity'    => isset( $data['current']['relative_humidity_2m'] ) ? $data['current']['relative_humidity_2m'] : 0,
        'wind'        => round( $data['current']['wind_speed_10m'] ),
        'code'        => (int) $data['current']['weather_code'],
        'label'       => neonews_weather_code_label( (int) $data['current']['weather_code'] ),
        'icon'        => neonews_weather_code_icon( (int) $data['current']['weather_code'] ),
        'high'        => isset( $data['daily']['temperature_2m_max'][0] ) ? round( $data['daily']['temperature_2m_max'][0] ) : null,
        'low'         => isset( $data['daily']['temperature_2m_min'][0] ) ? round( $data['daily']['temperature_2m_min'][0] ) : null,
        'unit'        => isset( $data['current_units']['temperature_2m'] ) ? $data['current_units']['temperature_2m'] : '°C',
    );

    set_transient( $cache_key, $result, 30 * MINUTE_IN_SECONDS );
    wp_send_json_success( $result );
}
add_action( 'wp_ajax_neonews_weather', 'neonews_ajax_weather' );
add_action( 'wp_ajax_nopriv_neonews_weather', 'neonews_ajax_weather' );

/**
 * WMO weather code label.
 *
 * @param int $code Code.
 * @return string
 */
function neonews_weather_code_label( $code ) {
    $map = array(
        0  => __( 'Clear sky', 'neonews' ),
        1  => __( 'Mainly clear', 'neonews' ),
        2  => __( 'Partly cloudy', 'neonews' ),
        3  => __( 'Overcast', 'neonews' ),
        45 => __( 'Foggy', 'neonews' ),
        48 => __( 'Foggy', 'neonews' ),
        51 => __( 'Light drizzle', 'neonews' ),
        61 => __( 'Rain', 'neonews' ),
        63 => __( 'Rain', 'neonews' ),
        65 => __( 'Heavy rain', 'neonews' ),
        71 => __( 'Snow', 'neonews' ),
        80 => __( 'Showers', 'neonews' ),
        95 => __( 'Thunderstorm', 'neonews' ),
    );
    return isset( $map[ $code ] ) ? $map[ $code ] : __( 'Variable', 'neonews' );
}

/**
 * Weather emoji icon.
 *
 * @param int $code Code.
 * @return string
 */
function neonews_weather_code_icon( $code ) {
    if ( 0 === $code || 1 === $code ) {
        return '☀️';
    }
    if ( 2 === $code || 3 === $code ) {
        return '⛅';
    }
    if ( in_array( $code, array( 45, 48 ), true ) ) {
        return '🌫';
    }
    if ( in_array( $code, array( 51, 53, 55, 61, 63, 65, 80, 81, 82 ), true ) ) {
        return '🌧';
    }
    if ( in_array( $code, array( 71, 73, 75 ), true ) ) {
        return '❄️';
    }
    if ( in_array( $code, array( 95, 96, 99 ), true ) ) {
        return '⛈';
    }
    return '🌤';
}
