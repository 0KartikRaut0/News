<?php
/**
 * Smart Tools default settings.
 *
 * @package NeoNews_Smart
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Persist Smart settings without autoloading into every request.
 *
 * @param array $settings Settings.
 * @return bool
 */
function neonews_smart_save_settings( $settings ) {
    return update_option( 'neonews_smart_settings', $settings, false );
}

/**
 * Ensure Smart settings are not in wp_load_alloptions.
 */
function neonews_smart_ensure_settings_not_autoloaded() {
    global $wpdb;

    $wpdb->update(
        $wpdb->options,
        array( 'autoload' => 'no' ),
        array( 'option_name' => 'neonews_smart_settings' ),
        array( '%s' ),
        array( '%s' )
    );

    wp_cache_delete( 'neonews_smart_settings', 'options' );
}

/**
 * Default Smart settings.
 *
 * @return array
 */
function neonews_smart_default_settings() {
    return array(
        'enable_auto_excerpt'       => true,
        'enable_story_glance'       => true,
        'enable_key_points'         => true,
        'enable_toc'                => true,
        'enable_smart_related'      => true,
        'enable_personalized_home'  => true,
        'enable_submission_checks'  => true,
        'enable_weekly_digest'      => true,
        'excerpt_max_sentences'     => 2,
        'submission_max_links'      => 8,
        'submission_banned_words'   => 'casino,viagra,crypto scam,get rich quick',
        'digest_day'                => 1,
        'digest_hour'               => 9,
    );
}
