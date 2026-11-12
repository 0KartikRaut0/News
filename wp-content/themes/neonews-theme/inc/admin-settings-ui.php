<?php
/**
 * NewsPulse settings — interactive admin dashboard shell.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tab metadata (icons + short descriptions).
 *
 * @return array<string, array{label:string, icon:string, hint:string}>
 */
function neonews_settings_tabs_meta() {
	return array(
		'platform' => array(
			'label' => __( 'Ads & Integrations', 'neonews' ),
			'icon'  => '🔌',
			'hint'  => __( 'Ads, CAPTCHA, performance, uploads', 'neonews' ),
		),
		'homepage' => array(
			'label' => __( 'Homepage', 'neonews' ),
			'icon'  => '🏠',
			'hint'  => __( 'Sections, layouts, custom blocks', 'neonews' ),
		),
		'header'   => array(
			'label' => __( 'Header', 'neonews' ),
			'icon'  => '📌',
			'hint'  => __( 'Breaking bar, search, navigation', 'neonews' ),
		),
		'footer'   => array(
			'label' => __( 'Footer', 'neonews' ),
			'icon'  => '⬇️',
			'hint'  => __( 'Social, newsletter, legal links', 'neonews' ),
		),
		'whatsapp' => array(
			'label' => __( 'WhatsApp', 'neonews' ),
			'icon'  => '💬',
			'hint'  => __( 'Floating button & contact CTAs', 'neonews' ),
		),
		'sidebar'  => array(
			'label' => __( 'Sidebar', 'neonews' ),
			'icon'  => '📋',
			'hint'  => __( 'Weather, fresh reads, topics', 'neonews' ),
		),
		'features' => array(
			'label' => __( 'Features & Colors', 'neonews' ),
			'icon'  => '✨',
			'hint'  => __( 'Engagement bar, article back button', 'neonews' ),
		),
	);
}

/**
 * Quick stats for dashboard header.
 *
 * @return array<string, mixed>
 */
function neonews_settings_dashboard_stats() {
	$posts = wp_count_posts( 'post' );
	$users = count_users();
	$stats = array(
		'posts'    => isset( $posts->publish ) ? (int) $posts->publish : 0,
		'comments' => (int) get_comments(
			array(
				'count'   => true,
				'status'  => 'approve',
			)
		),
		'users'    => isset( $users['total_users'] ) ? (int) $users['total_users'] : 0,
		'perf'     => null,
	);

	if ( function_exists( 'neonews_get_performance_report' ) ) {
		$report       = neonews_get_performance_report();
		$stats['perf'] = isset( $report['score'] ) ? (int) $report['score'] : null;
	}

	return $stats;
}

/**
 * Enqueue dashboard assets on NewsPulse settings screen.
 *
 * @param string $hook Hook suffix.
 */
function neonews_settings_dashboard_assets( $hook ) {
	if ( false === strpos( $hook, 'neonews-settings' ) ) {
		return;
	}

	wp_enqueue_style(
		'neonews-admin-settings-dash',
		NEONEWS_THEME_URI . '/css/admin-settings-dashboard.css',
		array(),
		NEONEWS_THEME_VERSION
	);

	wp_enqueue_script(
		'neonews-admin-settings-dash',
		NEONEWS_THEME_URI . '/js/admin-settings-dashboard.js',
		array(),
		NEONEWS_THEME_VERSION,
		true
	);

	wp_localize_script(
		'neonews-admin-settings-dash',
		'nnSettingsDash',
		array(
			'i18n' => array(
				'searchPlaceholder' => __( 'Search options on this tab…', 'neonews' ),
				'noResults'         => __( 'No matching settings on this tab.', 'neonews' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'neonews_settings_dashboard_assets', 20 );

/**
 * Dashboard header + stat cards + quick actions.
 *
 * @param string $active_tab Active tab slug.
 */
function neonews_render_settings_dashboard_header( $active_tab ) {
	$meta     = neonews_settings_tabs_meta();
	$tab_hint = isset( $meta[ $active_tab ]['hint'] ) ? $meta[ $active_tab ]['hint'] : '';
	$stats    = neonews_settings_dashboard_stats();
	$analytics_url = admin_url( 'admin.php?page=neonews-analytics' );
	$has_analytics = class_exists( 'NeoNews_Analytics_Dashboard' );
	?>
	<header class="nn-settings-dash-header">
		<div class="nn-settings-dash-title">
			<span class="nn-settings-dash-logo" aria-hidden="true">⚡</span>
			<div>
				<h1><?php esc_html_e( 'NewsPulse', 'neonews' ); ?></h1>
				<p class="nn-settings-dash-subtitle"><?php esc_html_e( 'Platform control center — site features, homepage, and integrations', 'neonews' ); ?></p>
				<?php if ( $tab_hint ) : ?>
					<p class="nn-settings-dash-tab-hint"><?php echo esc_html( $tab_hint ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<div class="nn-settings-dash-actions">
			<?php if ( $has_analytics ) : ?>
				<a class="nn-settings-quick-btn" href="<?php echo esc_url( $analytics_url ); ?>">📊 <?php esc_html_e( 'Analytics', 'neonews' ); ?></a>
			<?php endif; ?>
			<a class="nn-settings-quick-btn" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">🎨 <?php esc_html_e( 'Customize', 'neonews' ); ?></a>
			<a class="nn-settings-quick-btn nn-settings-quick-btn-ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View site', 'neonews' ); ?> ↗</a>
			<span class="nn-settings-build"><?php echo esc_html( 'v' . NEONEWS_THEME_VERSION ); ?></span>
		</div>
	</header>

	<div class="nn-settings-stat-cards">
		<div class="nn-settings-stat-card">
			<span class="nn-settings-stat-icon">📰</span>
			<strong class="nn-settings-stat-value"><?php echo esc_html( number_format_i18n( $stats['posts'] ) ); ?></strong>
			<span class="nn-settings-stat-label"><?php esc_html_e( 'Published posts', 'neonews' ); ?></span>
		</div>
		<div class="nn-settings-stat-card">
			<span class="nn-settings-stat-icon">💬</span>
			<strong class="nn-settings-stat-value"><?php echo esc_html( number_format_i18n( $stats['comments'] ) ); ?></strong>
			<span class="nn-settings-stat-label"><?php esc_html_e( 'Approved comments', 'neonews' ); ?></span>
		</div>
		<div class="nn-settings-stat-card">
			<span class="nn-settings-stat-icon">👥</span>
			<strong class="nn-settings-stat-value"><?php echo esc_html( number_format_i18n( $stats['users'] ) ); ?></strong>
			<span class="nn-settings-stat-label"><?php esc_html_e( 'Users', 'neonews' ); ?></span>
		</div>
		<?php if ( null !== $stats['perf'] ) : ?>
			<a class="nn-settings-stat-card nn-settings-stat-card-link" href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-settings&tab=platform' ) ); ?>#nn-perf-panel">
				<span class="nn-settings-stat-icon">⚡</span>
				<strong class="nn-settings-stat-value"><?php echo esc_html( (string) $stats['perf'] ); ?><small>%</small></strong>
				<span class="nn-settings-stat-label"><?php esc_html_e( 'Performance score', 'neonews' ); ?></span>
			</a>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Tab navigation as interactive cards.
 *
 * @param string $active_tab Active tab.
 */
function neonews_render_settings_tab_nav( $active_tab ) {
	$meta = neonews_settings_tabs_meta();
	?>
	<nav class="nn-settings-tabs" aria-label="<?php esc_attr_e( 'Settings sections', 'neonews' ); ?>">
		<?php foreach ( $meta as $slug => $item ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-settings&tab=' . $slug ) ); ?>"
			   class="nn-settings-tab-card<?php echo $slug === $active_tab ? ' is-active' : ''; ?>">
				<span class="nn-settings-tab-icon" aria-hidden="true"><?php echo esc_html( $item['icon'] ); ?></span>
				<span class="nn-settings-tab-label"><?php echo esc_html( $item['label'] ); ?></span>
				<span class="nn-settings-tab-hint"><?php echo esc_html( $item['hint'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="nn-settings-toolbar">
		<label class="nn-settings-search-wrap">
			<span class="nn-settings-search-icon" aria-hidden="true">🔍</span>
			<input type="search" id="nn-settings-search" class="nn-settings-search" autocomplete="off"
				placeholder="<?php esc_attr_e( 'Search options on this tab…', 'neonews' ); ?>" />
		</label>
		<p class="nn-settings-toolbar-note">
			<?php esc_html_e( 'Categories: Posts → Categories · Menus: Appearance → Menus', 'neonews' ); ?>
		</p>
	</div>
	<?php
}
