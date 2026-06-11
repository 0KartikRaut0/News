<?php
/**
 * WordPress dashboard widgets for user activity.
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Dashboard activity widgets.
 */
class NeoNews_Activity_Dashboard_Widget {

    /**
     * @var NeoNews_Activity_Dashboard_Widget|null
     */
    private static $instance = null;

    /**
     * Get instance.
     *
     * @return NeoNews_Activity_Dashboard_Widget
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'wp_dashboard_setup', array( $this, 'register_widgets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
    }

    /**
     * Register dashboard widgets.
     */
    public function register_widgets() {
        if ( ! current_user_can( 'manage_options' ) || ! NeoNews_User_Activity_Log::is_enabled() ) {
            return;
        }

        wp_add_dashboard_widget(
            'neonews_activity_overview',
            __( 'NewsPulse Activity Overview', 'neonews-core' ),
            array( $this, 'render_overview' )
        );

        wp_add_dashboard_widget(
            'neonews_activity_recent',
            __( 'Recent User Activity', 'neonews-core' ),
            array( $this, 'render_recent' )
        );
    }

    /**
     * Enqueue widget styles on dashboard.
     *
     * @param string $hook Hook suffix.
     */
    public function assets( $hook ) {
        if ( 'index.php' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'neonews-activity-log-admin',
            NEONEWS_CORE_URL . 'admin/css/activity-log.css',
            array(),
            NEONEWS_CORE_VERSION
        );
    }

    /**
     * Overview widget markup.
     */
    public function render_overview() {
        $stats   = NeoNews_User_Activity_Log::get_stats( 7 );
        $types   = NeoNews_User_Activity_Log::get_event_types();
        $log_url = admin_url( 'admin.php?page=neonews-user-activity' );
        $max_cnt = 0;

        foreach ( $stats['by_type'] as $row ) {
            $max_cnt = max( $max_cnt, (int) $row['cnt'] );
        }
        ?>
        <div class="nn-dash-activity">
            <div class="nn-activity-stats nn-dash-stats">
                <div class="nn-activity-stat-card">
                    <span class="nn-activity-stat-num"><?php echo esc_html( number_format_i18n( $stats['total_events'] ) ); ?></span>
                    <span class="nn-activity-stat-label"><?php esc_html_e( 'Events (7d)', 'neonews-core' ); ?></span>
                </div>
                <div class="nn-activity-stat-card">
                    <span class="nn-activity-stat-num"><?php echo esc_html( number_format_i18n( $stats['active_users'] ) ); ?></span>
                    <span class="nn-activity-stat-label"><?php esc_html_e( 'Active users', 'neonews-core' ); ?></span>
                </div>
            </div>

            <?php if ( ! empty( $stats['by_type'] ) ) : ?>
                <ul class="nn-dash-bars">
                    <?php foreach ( array_slice( $stats['by_type'], 0, 6 ) as $row ) : ?>
                        <?php
                        $label = $types[ $row['event_type'] ] ?? $row['event_type'];
                        $pct   = $max_cnt ? round( ( (int) $row['cnt'] / $max_cnt ) * 100 ) : 0;
                        ?>
                        <li>
                            <span class="nn-dash-bar-label"><?php echo esc_html( $label ); ?></span>
                            <span class="nn-dash-bar-track"><span class="nn-dash-bar-fill" style="width:<?php echo esc_attr( $pct ); ?>%"></span></span>
                            <span class="nn-dash-bar-count"><?php echo esc_html( number_format_i18n( $row['cnt'] ) ); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p><?php esc_html_e( 'No activity recorded yet.', 'neonews-core' ); ?></p>
            <?php endif; ?>

            <p class="nn-dash-footer">
                <a href="<?php echo esc_url( $log_url ); ?>" class="button button-secondary"><?php esc_html_e( 'Open activity log', 'neonews-core' ); ?></a>
            </p>
        </div>
        <?php
    }

    /**
     * Recent activity widget markup.
     */
    public function render_recent() {
        $items   = NeoNews_User_Activity_Log::get_recent( 8 );
        $types   = NeoNews_User_Activity_Log::get_event_types();
        $log_url = admin_url( 'admin.php?page=neonews-user-activity' );
        ?>
        <div class="nn-dash-activity">
            <?php if ( empty( $items ) ) : ?>
                <p><?php esc_html_e( 'No recent activity.', 'neonews-core' ); ?></p>
            <?php else : ?>
                <ul class="nn-dash-recent">
                    <?php foreach ( $items as $row ) : ?>
                        <li>
                            <span class="nn-activity-time"><?php echo esc_html( mysql2date( 'M j, g:i A', $row['created_at'] ) ); ?></span>
                            <span class="nn-activity-badge nn-activity-<?php echo esc_attr( $row['event_type'] ); ?>">
                                <?php echo esc_html( $types[ $row['event_type'] ] ?? $row['event_type'] ); ?>
                            </span>
                            <?php if ( ! empty( $row['user_id'] ) ) : ?>
                                <a href="<?php echo esc_url( get_edit_user_link( (int) $row['user_id'] ) ); ?>"><?php echo esc_html( $row['user_login'] ); ?></a>
                            <?php else : ?>
                                <?php echo esc_html( $row['user_login'] ); ?>
                            <?php endif; ?>
                            <span class="nn-dash-recent-msg"><?php echo esc_html( wp_trim_words( $row['message'], 8, '…' ) ); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <p class="nn-dash-footer">
                <a href="<?php echo esc_url( $log_url ); ?>"><?php esc_html_e( 'View all activity →', 'neonews-core' ); ?></a>
            </p>
        </div>
        <?php
    }
}
