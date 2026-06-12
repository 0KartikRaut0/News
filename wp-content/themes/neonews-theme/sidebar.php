<?php
/**
 * The sidebar template — graphical widgets
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

neonews_ad_zone( 'ad-sidebar', __( '300×600 — Sidebar placement', 'neonews' ) );

if ( neonews_is_feature_enabled( 'sidebar_weather' ) ) {
    neonews_render_weather_widget();
}

neonews_render_sidebar_fresh_reads();

if ( neonews_is_feature_enabled( 'sidebar_topics' ) ) {
    neonews_render_sidebar_topics( absint( neonews_get_setting( 'sidebar_topics_count', 8 ) ) );
}

if ( is_active_sidebar( 'sidebar-1' ) ) {
    dynamic_sidebar( 'sidebar-1' );
}
