<?php
/**
 * Shared NeoNews admin dashboard shell (analytics-style).
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<string, mixed> $args Configuration.
 */
function neonews_admin_shell_begin( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'id'         => 'nn-admin-shell',
			'icon'       => '⚡',
			'title'      => '',
			'subtitle'   => '',
			'stats'      => array(),
			'actions'    => array(),
			'show_title' => true,
			'class'      => '',
		)
	);

	$id       = sanitize_key( $args['id'] );
	$title    = $args['title'];
	$subtitle = $args['subtitle'];
	$stats    = is_array( $args['stats'] ) ? $args['stats'] : array();
	$actions  = is_array( $args['actions'] ) ? $args['actions'] : array();

	$extra_class = isset( $args['class'] ) ? trim( (string) $args['class'] ) : '';
	echo '<div class="wrap nn-admin-shell-wrap' . ( $extra_class ? ' ' . esc_attr( $extra_class ) : '' ) . '" id="' . esc_attr( $id ) . '">';

	echo '<header class="nn-admin-shell-header">';
	echo '<div class="nn-admin-shell-title">';
	echo '<span class="nn-admin-shell-icon" aria-hidden="true">' . esc_html( $args['icon'] ) . '</span>';
	echo '<div>';
	if ( $args['show_title'] && $title ) {
		echo '<h1>' . esc_html( $title ) . '</h1>';
	}
	if ( $subtitle ) {
		echo '<p class="nn-admin-shell-subtitle">' . esc_html( $subtitle ) . '</p>';
	}
	echo '</div></div>';

	if ( ! empty( $actions ) ) {
		echo '<div class="nn-admin-shell-actions">';
		foreach ( $actions as $action ) {
			if ( empty( $action['url'] ) || empty( $action['label'] ) ) {
				continue;
			}
			$external = ! empty( $action['external'] );
			$class    = 'nn-admin-shell-btn';
			if ( ! empty( $action['primary'] ) ) {
				$class .= ' nn-admin-shell-btn-primary';
			}
			echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $action['url'] ) . '"';
			if ( $external ) {
				echo ' target="_blank" rel="noopener"';
			}
			echo '>' . esc_html( $action['label'] );
			if ( $external ) {
				echo ' ↗';
			}
			echo '</a>';
		}
		echo '</div>';
	}
	echo '</header>';

	if ( ! empty( $stats ) ) {
		echo '<div class="nn-admin-stat-cards">';
		foreach ( $stats as $stat ) {
			$value = isset( $stat['value'] ) ? $stat['value'] : '—';
			$label = isset( $stat['label'] ) ? $stat['label'] : '';
			$icon  = isset( $stat['icon'] ) ? $stat['icon'] : '•';
			$url   = isset( $stat['url'] ) ? $stat['url'] : '';
			$tag   = $url ? 'a' : 'div';
			echo '<' . esc_attr( $tag ) . ' class="nn-admin-stat-card' . ( $url ? ' nn-admin-stat-card-link' : '' ) . '"';
			if ( $url ) {
				echo ' href="' . esc_url( $url ) . '"';
			}
			echo '>';
			echo '<span class="nn-admin-stat-icon" aria-hidden="true">' . esc_html( $icon ) . '</span>';
			echo '<strong class="nn-admin-stat-value">' . esc_html( (string) $value ) . '</strong>';
			echo '<span class="nn-admin-stat-label">' . esc_html( $label ) . '</span>';
			echo '</' . esc_attr( $tag ) . '>';
		}
		echo '</div>';
	}
}

/**
 * Open main content panel.
 *
 * @param string $extra_class Optional class.
 */
function neonews_admin_shell_panel_open( $extra_class = '' ) {
	echo '<div class="nn-admin-panel' . ( $extra_class ? ' ' . esc_attr( $extra_class ) : '' ) . '">';
}

/**
 * Close main content panel.
 */
function neonews_admin_shell_panel_close() {
	echo '</div>';
}

/**
 * Close shell wrap.
 */
function neonews_admin_shell_end() {
	echo '</div>';
}

/**
 * Default quick links for NeoNews admin screens.
 *
 * @return array<int, array<string, mixed>>
 */
function neonews_admin_shell_default_actions() {
	$actions = array();
	if ( menu_page_url( 'neonews-settings', false ) ) {
		$actions[] = array(
			'label' => __( 'NewsPulse', 'neonews' ),
			'url'   => admin_url( 'admin.php?page=neonews-settings' ),
		);
	}
	$actions[] = array(
		'label'    => __( 'View site', 'neonews' ),
		'url'      => home_url( '/' ),
		'external' => true,
	);
	return $actions;
}

/**
 * Enqueue shell styles (called from admin-dashboard-icons).
 *
 * @param string $hook Hook suffix.
 */
function neonews_admin_shell_assets( $hook ) {
	if ( ! neonews_is_neonews_admin_screen( $hook ) ) {
		return;
	}
	wp_enqueue_style(
		'neonews-admin-shell',
		NEONEWS_THEME_URI . '/css/admin-neonews-shell.css',
		array(),
		NEONEWS_THEME_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'neonews_admin_shell_assets', 15 );
