<?php
/**
 * Performance score admin panel.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Bootstrap admin panel hooks.
 */
function neonews_performance_panel_init() {
    add_action( 'admin_enqueue_scripts', 'neonews_performance_panel_assets' );
    add_action( 'admin_post_neonews_apply_perf_recommendations', 'neonews_performance_panel_apply' );
}
add_action( 'admin_init', 'neonews_performance_panel_init' );

/**
 * Enqueue panel styles on NewsPulse settings.
 *
 * @param string $hook Hook suffix.
 */
function neonews_performance_panel_assets( $hook ) {
    if ( false === strpos( $hook, 'neonews-settings' ) ) {
        return;
    }

    wp_enqueue_style(
        'neonews-admin-performance',
        NEONEWS_THEME_URI . '/css/admin-performance.css',
        array(),
        NEONEWS_THEME_VERSION
    );
}

/**
 * One-click apply recommended performance toggles.
 */
function neonews_performance_panel_apply() {
    if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'neonews_apply_perf_recommendations' ) ) {
        wp_die( esc_html__( 'Unauthorized', 'neonews' ) );
    }

    $count = neonews_apply_performance_recommendations();

    set_transient(
        'neonews_perf_applied_' . get_current_user_id(),
        $count,
        MINUTE_IN_SECONDS
    );

    wp_safe_redirect(
        add_query_arg(
            array(
                'page'       => 'neonews-settings',
                'tab'        => 'platform',
                'perf_fixed' => '1',
            ),
            admin_url( 'admin.php' )
        )
    );
    exit;
}

/**
 * Show notice after apply.
 */
function neonews_performance_panel_notice() {
    if ( empty( $_GET['perf_fixed'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
        return;
    }

    $count = get_transient( 'neonews_perf_applied_' . get_current_user_id() );
    delete_transient( 'neonews_perf_applied_' . get_current_user_id() );

    if ( false === $count ) {
        return;
    }

    printf(
        '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
        esc_html(
            sprintf(
                /* translators: %d: number of settings enabled */
                _n(
                    'Applied %d performance optimization.',
                    'Applied %d performance optimizations.',
                    max( 1, (int) $count ),
                    'neonews'
                ),
                max( 1, (int) $count )
            )
        )
    );
}

/**
 * Render performance score panel.
 */
function neonews_render_performance_panel() {
    if ( ! function_exists( 'neonews_get_performance_report' ) ) {
        return;
    }

    neonews_performance_panel_notice();

    $report     = neonews_get_performance_report();
    $score      = (int) $report['score'];
    $circum     = 2 * M_PI * 54;
    $dashoffset = $circum - ( $circum * $score / 100 );
    $apply_url  = wp_nonce_url(
        admin_url( 'admin-post.php?action=neonews_apply_perf_recommendations' ),
        'neonews_apply_perf_recommendations'
    );
    ?>
    <div class="nn-perf-panel">
        <div class="nn-perf-panel-header">
            <div class="nn-perf-score-ring" style="--nn-score: <?php echo esc_attr( $score ); ?>;">
                <svg viewBox="0 0 120 120" aria-hidden="true">
                    <circle class="nn-perf-ring-bg" cx="60" cy="60" r="54" />
                    <circle class="nn-perf-ring-fill" cx="60" cy="60" r="54"
                        stroke-dasharray="<?php echo esc_attr( $circum ); ?>"
                        stroke-dashoffset="<?php echo esc_attr( $dashoffset ); ?>" />
                </svg>
                <div class="nn-perf-score-text">
                    <span class="nn-perf-score-num"><?php echo esc_html( $score ); ?></span>
                    <span class="nn-perf-score-grade"><?php echo esc_html( $report['grade'] ); ?></span>
                </div>
            </div>
            <div class="nn-perf-summary">
                <h2><?php esc_html_e( 'Performance Score', 'neonews' ); ?></h2>
                <p class="nn-perf-grade-label"><?php echo esc_html( $report['grade_label'] ); ?></p>
                <p class="description">
                    <?php esc_html_e( 'Estimated savings from active optimizations on a typical page view.', 'neonews' ); ?>
                </p>
                <ul class="nn-perf-totals">
                    <li>
                        <strong><?php echo esc_html( number_format_i18n( $report['totals']['saved_kb'] ) ); ?> KB</strong>
                        <?php esc_html_e( 'transfer saved', 'neonews' ); ?>
                    </li>
                    <li>
                        <strong><?php echo esc_html( number_format_i18n( $report['totals']['saved_requests'] ) ); ?></strong>
                        <?php esc_html_e( 'fewer requests', 'neonews' ); ?>
                    </li>
                    <li>
                        <strong>~<?php echo esc_html( number_format_i18n( $report['totals']['tti_estimate'] ) ); ?> ms</strong>
                        <?php esc_html_e( 'faster interactivity (est.)', 'neonews' ); ?>
                    </li>
                </ul>
                <?php if ( ! empty( $report['auto_fix_count'] ) ) : ?>
                    <a href="<?php echo esc_url( $apply_url ); ?>" class="button button-primary nn-perf-apply-btn">
                        <?php
                        printf(
                            esc_html(
                                _n(
                                    'Apply %d recommended fix',
                                    'Apply %d recommended fixes',
                                    $report['auto_fix_count'],
                                    'neonews'
                                )
                            ),
                            (int) $report['auto_fix_count']
                        );
                        ?>
                    </a>
                <?php else : ?>
                    <span class="nn-perf-all-good"><?php esc_html_e( 'All automatic optimizations are enabled.', 'neonews' ); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="nn-perf-columns">
            <div class="nn-perf-column">
                <h3><?php esc_html_e( 'Active optimizations', 'neonews' ); ?> (<?php echo esc_html( count( $report['active'] ) ); ?>)</h3>
                <?php if ( empty( $report['active'] ) ) : ?>
                    <p><?php esc_html_e( 'No optimizations active yet. Enable lightweight mode below.', 'neonews' ); ?></p>
                <?php else : ?>
                    <ul class="nn-perf-list nn-perf-list-active">
                        <?php foreach ( $report['active'] as $item ) : ?>
                            <li>
                                <span class="nn-perf-icon nn-perf-icon-ok" aria-hidden="true">✓</span>
                                <span class="nn-perf-item-body">
                                    <strong><?php echo esc_html( $item['label'] ); ?></strong>
                                    <span><?php echo esc_html( $item['description'] ); ?></span>
                                    <?php if ( ! empty( $item['savings_kb'] ) ) : ?>
                                        <em><?php echo esc_html( sprintf( __( '~%d KB saved', 'neonews' ), (int) $item['savings_kb'] ) ); ?></em>
                                    <?php endif; ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="nn-perf-column">
                <h3><?php esc_html_e( 'Recommendations', 'neonews' ); ?> (<?php echo esc_html( count( $report['recommendations'] ) ); ?>)</h3>
                <?php if ( empty( $report['recommendations'] ) ) : ?>
                    <p><?php esc_html_e( 'Nothing else to optimize in NewsPulse settings.', 'neonews' ); ?></p>
                <?php else : ?>
                    <ul class="nn-perf-list nn-perf-list-rec">
                        <?php foreach ( $report['recommendations'] as $item ) : ?>
                            <li>
                                <span class="nn-perf-icon nn-perf-icon-tip" aria-hidden="true">→</span>
                                <span class="nn-perf-item-body">
                                    <strong><?php echo esc_html( $item['label'] ); ?></strong>
                                    <span><?php echo esc_html( $item['description'] ); ?></span>
                                    <?php if ( ! empty( $item['potential_kb'] ) ) : ?>
                                        <em><?php echo esc_html( sprintf( __( 'Up to ~%d KB', 'neonews' ), (int) $item['potential_kb'] ) ); ?></em>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $item['setting'] ) ) : ?>
                                        <a href="#nn-perf-setting-<?php echo esc_attr( $item['setting'] ); ?>" class="nn-perf-jump"><?php esc_html_e( 'Jump to setting', 'neonews' ); ?></a>
                                    <?php elseif ( ! empty( $item['manual'] ) ) : ?>
                                        <em class="nn-perf-manual"><?php echo esc_html( $item['manual'] ); ?></em>
                                    <?php endif; ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}
