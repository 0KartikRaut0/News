<?php
/**
 * Feature Control admin UI.
 *
 * @package NeoNews_Feature_Control
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NeoNews_FC_Admin_Dashboard {

	/** @var self|null */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 12 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_nn_fc_toggle', array( $this, 'ajax_toggle' ) );
		add_action( 'wp_ajax_nn_fc_bulk_group', array( $this, 'ajax_bulk_group' ) );
	}

	public function register_menu() {
		$parent = 'neonews-settings';
		if ( ! empty( $GLOBALS['admin_page_hooks']['neonews-settings'] ) ) {
			add_submenu_page(
				$parent,
				__( 'Feature Control', 'neonews-feature-control' ),
				__( '✨ Feature Control', 'neonews-feature-control' ),
				'manage_options',
				'neonews-feature-control',
				array( $this, 'render_page' )
			);
		} else {
			add_menu_page(
				__( 'Feature Control', 'neonews-feature-control' ),
				__( 'Feature Control', 'neonews-feature-control' ),
				'manage_options',
				'neonews-feature-control',
				array( $this, 'render_page' ),
				'dashicons-admin-settings',
				58
			);
		}
	}

	/**
	 * @param string $hook Hook suffix.
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, 'neonews-feature-control' ) ) {
			return;
		}

		if ( function_exists( 'neonews_ensure_admin_native_emoji' ) ) {
			neonews_ensure_admin_native_emoji();
		} else {
			$this->disable_twemoji_fallback();
		}

		wp_enqueue_style(
			'neonews-fc-dashboard',
			NEONEWS_FC_URL . 'assets/css/dashboard.css',
			array(),
			NEONEWS_FC_VERSION
		);

		wp_enqueue_script(
			'neonews-fc-dashboard',
			NEONEWS_FC_URL . 'assets/js/dashboard.js',
			array(),
			NEONEWS_FC_VERSION,
			true
		);

		wp_localize_script(
			'neonews-fc-dashboard',
			'nnFeatureControl',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'nn_fc_toggle' ),
				'i18n'    => array(
					'enabled'  => __( 'On', 'neonews-feature-control' ),
					'disabled' => __( 'Off', 'neonews-feature-control' ),
					'saving'   => __( 'Saving…', 'neonews-feature-control' ),
					'error'    => __( 'Could not save. Try again.', 'neonews-feature-control' ),
					'search'   => __( 'Search features…', 'neonews-feature-control' ),
					'allOn'    => __( 'Turn all on', 'neonews-feature-control' ),
					'allOff'   => __( 'Turn all off', 'neonews-feature-control' ),
					'unavail'  => __( 'Plugin inactive', 'neonews-feature-control' ),
				),
			)
		);
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$groups   = NeoNews_FC_Feature_Registry::get_groups();
		$features = NeoNews_FC_Feature_Registry::get_features();
		$enabled  = 0;
		$total    = 0;

		foreach ( $features as $feature ) {
			if ( ! NeoNews_FC_Feature_Registry::is_available( $feature ) ) {
				continue;
			}
			++$total;
			if ( NeoNews_FC_Feature_Store::is_enabled( $feature ) ) {
				++$enabled;
			}
		}

		$settings_url = admin_url( 'admin.php?page=neonews-settings' );
		?>
		<div class="nn-fc-wrap" id="nn-feature-control">
			<header class="nn-fc-header">
				<div class="nn-fc-header-text">
					<span class="nn-fc-badge" aria-hidden="true">✨</span>
					<div>
						<h1><?php esc_html_e( 'Feature Control', 'neonews-feature-control' ); ?></h1>
						<p><?php esc_html_e( 'Flip features on or off in one place — changes apply immediately on the live site.', 'neonews-feature-control' ); ?></p>
					</div>
				</div>
				<div class="nn-fc-header-meta">
					<div class="nn-fc-ring" style="--nn-fc-pct: <?php echo esc_attr( $total ? round( 100 * $enabled / $total ) : 0 ); ?>;">
						<strong><?php echo esc_html( (string) $enabled ); ?></strong>
						<span>/ <?php echo esc_html( (string) $total ); ?></span>
						<small><?php esc_html_e( 'features on', 'neonews-feature-control' ); ?></small>
					</div>
					<?php if ( menu_page_url( 'neonews-settings', false ) ) : ?>
						<a class="nn-fc-link" href="<?php echo esc_url( $settings_url ); ?>"><?php esc_html_e( 'Detailed settings', 'neonews-feature-control' ); ?> →</a>
					<?php endif; ?>
					<a class="nn-fc-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View site', 'neonews-feature-control' ); ?> ↗</a>
				</div>
			</header>

			<div class="nn-fc-toolbar">
				<label class="nn-fc-search-wrap">
					<span aria-hidden="true">🔍</span>
					<input type="search" id="nn-fc-search" placeholder="<?php esc_attr_e( 'Search features…', 'neonews-feature-control' ); ?>" autocomplete="off" />
				</label>
				<div class="nn-fc-filter-pills" role="tablist">
					<button type="button" class="nn-fc-pill is-active" data-group="all"><?php esc_html_e( 'All', 'neonews-feature-control' ); ?></button>
					<?php foreach ( $groups as $slug => $group ) : ?>
						<button type="button" class="nn-fc-pill" data-group="<?php echo esc_attr( $slug ); ?>">
							<?php echo esc_html( $group['icon'] . ' ' . $group['label'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<?php foreach ( $groups as $slug => $group ) : ?>
				<?php
				$group_features = array_filter(
					$features,
					static function ( $f ) use ( $slug ) {
						return ( $f['group'] ?? '' ) === $slug;
					}
				);
				if ( empty( $group_features ) ) {
					continue;
				}
				?>
				<section class="nn-fc-section" data-group-section="<?php echo esc_attr( $slug ); ?>">
					<div class="nn-fc-section-head">
						<h2><?php echo esc_html( $group['icon'] . ' ' . $group['label'] ); ?></h2>
						<div class="nn-fc-section-actions">
							<button type="button" class="nn-fc-bulk" data-bulk="on" data-group="<?php echo esc_attr( $slug ); ?>"><?php esc_html_e( 'Turn all on', 'neonews-feature-control' ); ?></button>
							<button type="button" class="nn-fc-bulk" data-bulk="off" data-group="<?php echo esc_attr( $slug ); ?>"><?php esc_html_e( 'Turn all off', 'neonews-feature-control' ); ?></button>
						</div>
					</div>
					<div class="nn-fc-grid">
						<?php foreach ( $group_features as $feature ) :
							$available = NeoNews_FC_Feature_Registry::is_available( $feature );
							$on        = $available && NeoNews_FC_Feature_Store::is_enabled( $feature );
							?>
							<article class="nn-fc-card<?php echo $on ? ' is-on' : ''; ?><?php echo $available ? '' : ' is-unavailable'; ?>"
								data-feature-id="<?php echo esc_attr( $feature['id'] ); ?>"
								data-group="<?php echo esc_attr( $slug ); ?>"
								data-search="<?php echo esc_attr( strtolower( $feature['title'] . ' ' . $feature['description'] ) ); ?>">
								<div class="nn-fc-card-top">
									<span class="nn-fc-card-icon" aria-hidden="true"><?php echo esc_html( $feature['icon'] ); ?></span>
									<?php if ( $available ) : ?>
									<button type="button"
										class="nn-fc-switch"
										role="switch"
										aria-checked="<?php echo $on ? 'true' : 'false'; ?>"
										aria-label="<?php echo esc_attr( $feature['title'] ); ?>"
										data-id="<?php echo esc_attr( $feature['id'] ); ?>"
										data-enabled="<?php echo $on ? '1' : '0'; ?>">
										<span class="nn-fc-switch-track"><span class="nn-fc-switch-thumb"></span></span>
									</button>
									<?php else : ?>
										<span class="nn-fc-unavail"><?php esc_html_e( 'Plugin inactive', 'neonews-feature-control' ); ?></span>
									<?php endif; ?>
								</div>
								<h3 class="nn-fc-card-title"><?php echo esc_html( $feature['title'] ); ?></h3>
								<p class="nn-fc-card-desc"><?php echo esc_html( $feature['description'] ); ?></p>
								<p class="nn-fc-card-status" aria-live="polite"><?php echo $on ? esc_html__( 'On', 'neonews-feature-control' ) : esc_html__( 'Off', 'neonews-feature-control' ); ?></p>
							</article>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endforeach; ?>

			<p class="nn-fc-empty" id="nn-fc-empty" hidden><?php esc_html_e( 'No features match your search.', 'neonews-feature-control' ); ?></p>
		</div>
		<?php
	}

	public function ajax_toggle() {
		check_ajax_referer( 'nn_fc_toggle', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Forbidden', 'neonews-feature-control' ) ), 403 );
		}

		$id      = isset( $_POST['id'] ) ? sanitize_key( wp_unslash( $_POST['id'] ) ) : '';
		$enabled = ! empty( $_POST['enabled'] );

		$feature = NeoNews_FC_Feature_Registry::get_by_id( $id );
		if ( ! $feature || ! NeoNews_FC_Feature_Registry::is_available( $feature ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown feature.', 'neonews-feature-control' ) ) );
		}

		$result = NeoNews_FC_Feature_Store::set_enabled( $feature, $enabled );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		$confirmed = NeoNews_FC_Feature_Store::is_enabled( $feature );

		wp_send_json_success(
			array(
				'id'      => $id,
				'enabled' => $confirmed === (bool) $enabled ? $confirmed : (bool) $enabled,
			)
		);
	}

	public function ajax_bulk_group() {
		check_ajax_referer( 'nn_fc_toggle', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Forbidden', 'neonews-feature-control' ) ), 403 );
		}

		$group   = isset( $_POST['group'] ) ? sanitize_key( wp_unslash( $_POST['group'] ) ) : '';
		$enabled = ! empty( $_POST['enabled'] );

		$updated = array();
		foreach ( NeoNews_FC_Feature_Registry::get_features() as $feature ) {
			if ( ( $feature['group'] ?? '' ) !== $group ) {
				continue;
			}
			if ( ! NeoNews_FC_Feature_Registry::is_available( $feature ) ) {
				continue;
			}
			NeoNews_FC_Feature_Store::set_enabled( $feature, $enabled );
			$updated[] = array(
				'id'      => $feature['id'],
				'enabled' => (bool) $enabled,
			);
		}

		if ( class_exists( 'NeoNews_Smart' ) && method_exists( 'NeoNews_Smart', 'flush_settings_cache' ) ) {
			NeoNews_Smart::flush_settings_cache();
		}

		wp_send_json_success( array( 'items' => $updated ) );
	}

	/**
	 * Theme-less fallback when Feature Control runs without NeoNews theme helpers.
	 */
	private function disable_twemoji_fallback() {
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		add_filter( 'emoji_svg_url', '__return_false' );
		add_filter( 'wp_staticize_emoji', '__return_false' );
	}
}
