<?php
/**
 * Interactive dashboard bar for Posts & Pages list screens.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return bool
 */
function neonews_is_content_list_dashboard_screen() {
	if ( ! is_admin() ) {
		return false;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen ) {
		return false;
	}

	return in_array( $screen->id, array( 'edit-post', 'edit-page' ), true );
}

/**
 * Collect list stats for a post type.
 *
 * @param string $post_type post|page.
 * @return array{counts: object, premium: int, categories: int, base_url: string}
 */
function neonews_content_dashboard_get_stats( $post_type ) {
	$counts = wp_count_posts( $post_type );
	$base   = admin_url( 'edit.php' );
	if ( 'page' !== $post_type ) {
		$base = add_query_arg( 'post_type', $post_type, $base );
	}

	$premium    = 0;
	$categories = 0;

	if ( 'post' === $post_type ) {
		$cached = get_transient( 'nn_content_dash_premium_count' );
		if ( false !== $cached ) {
			$premium = (int) $cached;
		} else {
			$q = new WP_Query(
				array(
					'post_type'              => 'post',
					'post_status'            => array( 'publish', 'future', 'private' ),
					'meta_key'               => '_neonews_premium_post',
					'meta_value'             => '1',
					'posts_per_page'         => 1,
					'fields'                 => 'ids',
					'no_found_rows'          => false,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);
			$premium = (int) $q->found_posts;
			set_transient( 'nn_content_dash_premium_count', $premium, 5 * MINUTE_IN_SECONDS );
		}

		$term_count = wp_count_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
			)
		);
		$categories = is_wp_error( $term_count ) ? 0 : (int) $term_count;
	}

	return array(
		'counts'     => $counts,
		'premium'    => $premium,
		'categories' => $categories,
		'base_url'   => $base,
	);
}

/**
 * Build stat cards for shell.
 *
 * @param string $post_type post|page.
 * @return array<int, array<string, mixed>>
 */
function neonews_content_dashboard_stat_cards( $post_type ) {
	$data   = neonews_content_dashboard_get_stats( $post_type );
	$counts = $data['counts'];
	$base   = $data['base_url'];
	$stats  = array();

	$stats[] = array(
		'icon'  => '✅',
		'value' => number_format_i18n( (int) ( $counts->publish ?? 0 ) ),
		'label' => __( 'Published', 'neonews' ),
		'url'   => $base,
		'state' => 'publish',
	);
	$stats[] = array(
		'icon'  => '📝',
		'value' => number_format_i18n( (int) ( $counts->draft ?? 0 ) ),
		'label' => __( 'Drafts', 'neonews' ),
		'url'   => add_query_arg( 'post_status', 'draft', $base ),
		'state' => 'draft',
	);
	$stats[] = array(
		'icon'  => '⏳',
		'value' => number_format_i18n( (int) ( $counts->pending ?? 0 ) ),
		'label' => __( 'Pending', 'neonews' ),
		'url'   => add_query_arg( 'post_status', 'pending', $base ),
		'state' => 'pending',
	);
	$stats[] = array(
		'icon'  => '🗑',
		'value' => number_format_i18n( (int) ( $counts->trash ?? 0 ) ),
		'label' => __( 'Trash', 'neonews' ),
		'url'   => add_query_arg( 'post_status', 'trash', $base ),
		'state' => 'trash',
	);

	if ( 'post' === $post_type ) {
		$stats[] = array(
			'icon'  => '⭐',
			'value' => number_format_i18n( $data['premium'] ),
			'label' => __( 'Premium', 'neonews' ),
			'url'   => add_query_arg(
				array(
					'neonews_filter' => 'premium',
				),
				$base
			),
			'state' => 'premium',
		);
		$stats[] = array(
			'icon'  => '🏷',
			'value' => number_format_i18n( $data['categories'] ),
			'label' => __( 'Categories', 'neonews' ),
			'url'   => admin_url( 'edit-tags.php?taxonomy=category' ),
			'state' => '',
		);
	}

	if ( 'page' === $post_type && isset( $counts->private ) ) {
		$stats[] = array(
			'icon'  => '🔒',
			'value' => number_format_i18n( (int) $counts->private ),
			'label' => __( 'Private', 'neonews' ),
			'url'   => add_query_arg( 'post_status', 'private', $base ),
			'state' => 'private',
		);
	}

	return $stats;
}

/**
 * Render dashboard block (injected on list screen).
 */
function neonews_content_dashboard_render() {
	if ( ! neonews_is_content_list_dashboard_screen() ) {
		return;
	}

	$screen    = get_current_screen();
	$post_type = $screen->post_type;
	$is_post   = 'post' === $post_type;

	if ( $is_post && ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	if ( ! $is_post && ! current_user_can( 'edit_pages' ) ) {
		return;
	}

	$new_url = $is_post ? admin_url( 'post-new.php' ) : admin_url( 'post-new.php?post_type=page' );

	$actions = array(
		array(
			'label'   => $is_post ? __( 'Add post', 'neonews' ) : __( 'Add page', 'neonews' ),
			'url'     => $new_url,
			'primary' => true,
		),
	);

	if ( $is_post && class_exists( 'NeoNews_Analytics_Dashboard' ) && current_user_can( 'manage_options' ) ) {
		$actions[] = array(
			'label' => __( 'Analytics', 'neonews' ),
			'url'   => admin_url( 'admin.php?page=neonews-analytics' ),
		);
	}

	if ( $is_post && post_type_exists( 'post' ) && current_user_can( 'edit_others_posts' ) ) {
		$actions[] = array(
			'label' => __( 'Story submissions', 'neonews' ),
			'url'   => admin_url( 'admin.php?page=neonews-submissions' ),
		);
	}

	$actions = array_merge( $actions, function_exists( 'neonews_admin_shell_default_actions' ) ? neonews_admin_shell_default_actions() : array() );

	echo '<div class="nn-content-dashboard-bar" id="nn-content-list-dashboard">';
	if ( function_exists( 'neonews_admin_shell_begin' ) ) {
		neonews_admin_shell_begin(
			array(
				'id'         => $is_post ? 'nn-posts-dashboard' : 'nn-pages-dashboard',
				'icon'       => $is_post ? '📰' : '📄',
				'title'      => $is_post ? __( 'Posts', 'neonews' ) : __( 'Pages', 'neonews' ),
				'subtitle'   => $is_post
					? __( 'Manage stories, filters, and publishing — tap a stat to jump to that list.', 'neonews' )
					: __( 'Manage static pages — tap a stat to filter the list below.', 'neonews' ),
				'stats'      => neonews_content_dashboard_stat_cards( $post_type ),
				'actions'    => $actions,
				'outer_wrap' => false,
			)
		);
	}
	echo '</div>';
}

/**
 * Premium filter for posts list table (admin request vars).
 *
 * @param array<string, mixed> $vars Query vars.
 * @return array<string, mixed>
 */
function neonews_content_dashboard_premium_filter( $vars ) {
	global $pagenow;

	if ( 'edit.php' !== $pagenow ) {
		return $vars;
	}

	$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'post'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( 'post' !== $post_type ) {
		return $vars;
	}

	if ( isset( $_GET['neonews_filter'] ) && 'premium' === sanitize_key( wp_unslash( $_GET['neonews_filter'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$vars['meta_key']   = '_neonews_premium_post';
		$vars['meta_value'] = '1';
	}

	return $vars;
}
add_filter( 'request', 'neonews_content_dashboard_premium_filter' );

add_action(
	'admin_notices',
	static function () {
		neonews_content_dashboard_render();
	},
	0
);

/**
 * Mark active stat from URL.
 *
 * @param string $classes Body classes.
 * @return string
 */
function neonews_content_dashboard_body_class( $classes ) {
	if ( neonews_is_content_list_dashboard_screen() ) {
		$classes .= ' nn-content-dashboard nn-neonews-admin';

		$screen = get_current_screen();
		if ( $screen && 'edit-post' === $screen->id ) {
			$classes .= ' nn-content-dashboard-posts';
		} else {
			$classes .= ' nn-content-dashboard-pages';
		}
	}
	return $classes;
}
add_filter( 'admin_body_class', 'neonews_content_dashboard_body_class', 15 );

/**
 * Assets for posts/pages list.
 *
 * @param string $hook Hook suffix.
 */
function neonews_content_dashboard_assets( $hook ) {
	if ( 'edit.php' !== $hook ) {
		return;
	}

	$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'post'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! in_array( $post_type, array( 'post', 'page' ), true ) ) {
		return;
	}

	if ( function_exists( 'neonews_admin_disable_twemoji' ) ) {
		neonews_admin_disable_twemoji();
	}

	wp_enqueue_style(
		'neonews-admin-shell',
		NEONEWS_THEME_URI . '/css/admin-neonews-shell.css',
		array(),
		NEONEWS_THEME_VERSION
	);
	wp_enqueue_style(
		'neonews-content-dashboard',
		NEONEWS_THEME_URI . '/css/admin-content-dashboard.css',
		array( 'neonews-admin-shell' ),
		NEONEWS_THEME_VERSION
	);

	wp_enqueue_script(
		'neonews-content-dashboard',
		NEONEWS_THEME_URI . '/js/admin-content-dashboard.js',
		array(),
		NEONEWS_THEME_VERSION,
		true
	);

	$status = isset( $_GET['post_status'] ) ? sanitize_key( wp_unslash( $_GET['post_status'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$filter = isset( $_GET['neonews_filter'] ) ? sanitize_key( wp_unslash( $_GET['neonews_filter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	wp_localize_script(
		'neonews-content-dashboard',
		'nnContentDash',
		array(
			'postStatus' => $status,
			'filter'     => $filter,
		)
	);
}
add_action( 'admin_enqueue_scripts', 'neonews_content_dashboard_assets', 20 );

/**
 * Clear premium count cache when posts change.
 *
 * @param int $post_id Post ID.
 */
function neonews_content_dashboard_clear_cache( $post_id ) {
	$post = get_post( $post_id );
	if ( $post && 'post' === $post->post_type ) {
		delete_transient( 'nn_content_dash_premium_count' );
	}
}
add_action( 'save_post', 'neonews_content_dashboard_clear_cache' );
add_action( 'deleted_post', 'neonews_content_dashboard_clear_cache' );
