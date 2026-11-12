<?php
/**
 * NeoNews admin dashboards — native emoji icons (no Twemoji CDN images).
 *
 * WordPress replaces emojis with <img src="https://s.w.org/..."> in admin; on Local/offline
 * those URLs often fail and show the "broken image" icon beside real emoji.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current admin request is a NeoNews-branded screen.
 *
 * @param string $hook Optional admin hook suffix.
 * @return bool
 */
function neonews_is_neonews_admin_screen( $hook = '' ) {
	if ( ! is_admin() ) {
		return false;
	}

	if ( $hook && is_string( $hook ) ) {
		if ( false !== strpos( $hook, 'neonews' ) ) {
			return true;
		}
		if ( false !== strpos( $hook, 'news_pulse' ) ) {
			return true;
		}
	}

	if ( isset( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = sanitize_key( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 0 === strpos( $page, 'neonews' ) ) {
			return true;
		}
	}

	global $pagenow;
	if ( isset( $pagenow ) && in_array( $pagenow, array( 'admin.php', 'options-general.php' ), true ) ) {
		$parent = isset( $_GET['parent'] ) ? sanitize_key( wp_unslash( $_GET['parent'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'neonews-settings' === $parent ) {
			return true;
		}
	}

	return false;
}

/**
 * Stop Twemoji / CDN emoji images on NeoNews admin UIs.
 */
function neonews_admin_disable_twemoji() {
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );

	remove_filter( 'the_content', 'wp_staticize_emoji' );
	remove_filter( 'the_excerpt', 'wp_staticize_emoji' );
	remove_filter( 'comment_text', 'wp_staticize_emoji' );
	remove_filter( 'widget_text_content', 'wp_staticize_emoji' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	add_filter( 'emoji_svg_url', '__return_false' );
	add_filter( 'wp_staticize_emoji', '__return_false' );
}

/**
 * Boot icon fix on matching admin screens.
 *
 * @param string $hook Admin hook suffix.
 */
function neonews_admin_dashboard_icons_boot( $hook ) {
	if ( ! neonews_is_neonews_admin_screen( $hook ) ) {
		return;
	}

	neonews_admin_disable_twemoji();

	wp_enqueue_style(
		'neonews-admin-dashboard-icons',
		NEONEWS_THEME_URI . '/css/admin-dashboard-icons.css',
		array(),
		NEONEWS_THEME_VERSION
	);
}
add_action( 'admin_init', 'neonews_admin_dashboard_icons_init', 1 );
add_action( 'admin_enqueue_scripts', 'neonews_admin_dashboard_icons_boot', 1 );

/**
 * Disable Twemoji before admin output starts.
 */
function neonews_admin_dashboard_icons_init() {
	if ( neonews_is_neonews_admin_screen( '' ) ) {
		neonews_admin_disable_twemoji();
	}
}

/**
 * @param string $classes Body classes.
 * @return string
 */
function neonews_admin_dashboard_body_class( $classes ) {
	if ( neonews_is_neonews_admin_screen( '' ) ) {
		$classes .= ' nn-neonews-admin';
	}
	return $classes;
}
add_filter( 'admin_body_class', 'neonews_admin_dashboard_body_class' );

/**
 * Allow plugins to call the same Twemoji disable (Feature Control, etc.).
 */
function neonews_ensure_admin_native_emoji() {
	if ( is_admin() ) {
		neonews_admin_disable_twemoji();
	}
}
