<?php
/**
 * User Activity Log admin page.
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Activity log admin UI.
 */
class NeoNews_Activity_Log_Admin {

    /**
     * @var NeoNews_Activity_Log_Admin|null
     */
    private static $instance = null;

    /**
     * Get instance.
     *
     * @return NeoNews_Activity_Log_Admin
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
        add_action( 'admin_menu', array( $this, 'menu' ), 22 );
        add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
        add_action( 'admin_post_neonews_export_activity', array( $this, 'export_csv' ) );
        add_action( 'show_user_profile', array( $this, 'user_profile_section' ) );
        add_action( 'edit_user_profile', array( $this, 'user_profile_section' ) );
    }

    /**
     * Register submenu.
     */
    public function menu() {
        add_submenu_page(
            'neonews-settings',
            __( 'User Activity Log', 'neonews-core' ),
            __( 'User Activity', 'neonews-core' ),
            'manage_options',
            'neonews-user-activity',
            array( $this, 'render_page' )
        );
    }

    /**
     * Enqueue admin assets.
     *
     * @param string $hook Hook suffix.
     */
    public function assets( $hook ) {
        if ( false === strpos( $hook, 'neonews-user-activity' ) ) {
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
     * Render admin page.
     */
    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $filters = array(
            'user_id'    => isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0,
            'event_type' => isset( $_GET['event_type'] ) ? sanitize_key( wp_unslash( $_GET['event_type'] ) ) : '',
            'search'     => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
            'date_from'  => isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '',
            'date_to'    => isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '',
            'page'       => isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1,
            'per_page'   => 30,
        );

        $result = NeoNews_User_Activity_Log::query( $filters );
        $stats  = NeoNews_User_Activity_Log::get_stats( 7 );
        $types  = NeoNews_User_Activity_Log::get_event_types();
        $users  = get_users( array(
            'number'  => 200,
            'orderby' => 'registered',
            'order'   => 'DESC',
            'fields'  => array( 'ID', 'user_login', 'display_name' ),
        ) );

        $export_url = wp_nonce_url(
            add_query_arg(
                array(
                    'action'     => 'neonews_export_activity',
                    'user_id'    => $filters['user_id'],
                    'event_type' => $filters['event_type'],
                    's'          => $filters['search'],
                    'date_from'  => $filters['date_from'],
                    'date_to'    => $filters['date_to'],
                ),
                admin_url( 'admin-post.php' )
            ),
            'neonews_export_activity'
        );
        ?>
        <div class="wrap nn-activity-log-wrap">
            <h1><?php esc_html_e( 'User Activity Log', 'neonews-core' ); ?></h1>
            <p class="description">
                <?php esc_html_e( 'Track logins, reading, submissions, likes, shares, comments, and profile changes. Enable tracking in NewsPulse → Core Features.', 'neonews-core' ); ?>
            </p>

            <div class="nn-activity-stats">
                <div class="nn-activity-stat-card">
                    <span class="nn-activity-stat-num"><?php echo esc_html( number_format_i18n( $stats['total_events'] ) ); ?></span>
                    <span class="nn-activity-stat-label"><?php esc_html_e( 'Events (7 days)', 'neonews-core' ); ?></span>
                </div>
                <div class="nn-activity-stat-card">
                    <span class="nn-activity-stat-num"><?php echo esc_html( number_format_i18n( $stats['active_users'] ) ); ?></span>
                    <span class="nn-activity-stat-label"><?php esc_html_e( 'Active users', 'neonews-core' ); ?></span>
                </div>
                <div class="nn-activity-stat-card nn-activity-stat-types">
                    <?php if ( ! empty( $stats['by_type'] ) ) : ?>
                        <?php foreach ( array_slice( $stats['by_type'], 0, 4 ) as $row ) : ?>
                            <span class="nn-activity-type-pill">
                                <?php echo esc_html( $types[ $row['event_type'] ] ?? $row['event_type'] ); ?>:
                                <strong><?php echo esc_html( $row['cnt'] ); ?></strong>
                            </span>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <span class="nn-activity-stat-label"><?php esc_html_e( 'No activity yet', 'neonews-core' ); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <form method="get" class="nn-activity-filters">
                <input type="hidden" name="page" value="neonews-user-activity" />

                <select name="user_id">
                    <option value="0"><?php esc_html_e( 'All users', 'neonews-core' ); ?></option>
                    <?php foreach ( $users as $u ) : ?>
                        <option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $filters['user_id'], $u->ID ); ?>>
                            <?php echo esc_html( $u->display_name . ' (@' . $u->user_login . ')' ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="event_type">
                    <option value=""><?php esc_html_e( 'All event types', 'neonews-core' ); ?></option>
                    <?php foreach ( $types as $slug => $label ) : ?>
                        <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $filters['event_type'], $slug ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="date" name="date_from" value="<?php echo esc_attr( $filters['date_from'] ); ?>" placeholder="<?php esc_attr_e( 'From', 'neonews-core' ); ?>" />
                <input type="date" name="date_to" value="<?php echo esc_attr( $filters['date_to'] ); ?>" />

                <input type="search" name="s" value="<?php echo esc_attr( $filters['search'] ); ?>" placeholder="<?php esc_attr_e( 'Search message or username…', 'neonews-core' ); ?>" class="regular-text" />

                <?php submit_button( __( 'Filter', 'neonews-core' ), 'secondary', '', false ); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-user-activity' ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'neonews-core' ); ?></a>
                <a href="<?php echo esc_url( $export_url ); ?>" class="button button-secondary"><?php esc_html_e( 'Export CSV', 'neonews-core' ); ?></a>
            </form>

            <table class="wp-list-table widefat fixed striped nn-activity-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Time', 'neonews-core' ); ?></th>
                        <th><?php esc_html_e( 'User', 'neonews-core' ); ?></th>
                        <th><?php esc_html_e( 'Event', 'neonews-core' ); ?></th>
                        <th><?php esc_html_e( 'Details', 'neonews-core' ); ?></th>
                        <th><?php esc_html_e( 'Reference', 'neonews-core' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $result['items'] ) ) : ?>
                        <tr><td colspan="5"><?php esc_html_e( 'No activity found for these filters.', 'neonews-core' ); ?></td></tr>
                    <?php else : ?>
                        <?php foreach ( $result['items'] as $row ) : ?>
                            <tr>
                                <td>
                                    <span class="nn-activity-time"><?php echo esc_html( mysql2date( 'M j, Y g:i A', $row['created_at'] ) ); ?></span>
                                </td>
                                <td>
                                    <?php if ( ! empty( $row['user_id'] ) ) : ?>
                                        <a href="<?php echo esc_url( get_edit_user_link( (int) $row['user_id'] ) ); ?>">
                                            <?php echo esc_html( $row['user_login'] ); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo esc_html( $row['user_login'] ); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="nn-activity-badge nn-activity-<?php echo esc_attr( $row['event_type'] ); ?>">
                                        <?php echo esc_html( $types[ $row['event_type'] ] ?? $row['event_type'] ); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html( $row['message'] ); ?></td>
                                <td>
                                    <?php if ( ! empty( $row['object_url'] ) ) : ?>
                                        <a href="<?php echo esc_url( $row['object_url'] ); ?>" target="_blank" rel="noopener noreferrer">
                                            <?php echo esc_html( $row['object_title'] ?: '#' . $row['object_id'] ); ?>
                                        </a>
                                    <?php elseif ( ! empty( $row['object_id'] ) ) : ?>
                                        #<?php echo esc_html( $row['object_id'] ); ?>
                                    <?php else : ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ( $result['pages'] > 1 ) : ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <?php
                        echo wp_kses_post(
                            paginate_links(
                                array(
                                    'base'      => add_query_arg( 'paged', '%#%' ),
                                    'format'    => '',
                                    'prev_text' => '&laquo;',
                                    'next_text' => '&raquo;',
                                    'total'     => $result['pages'],
                                    'current'   => $result['page'],
                                )
                            )
                        );
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <p class="description nn-activity-retention">
                <?php
                printf(
                    esc_html__( 'Logs are retained for %d days. IP addresses are stored as daily hashes for privacy.', 'neonews-core' ),
                    (int) apply_filters( 'neonews_user_activity_retention_days', 180 )
                );
                ?>
            </p>

            <?php if ( NeoNews_Activity_Anomaly_Detector::notifications_enabled() ) : ?>
                <p class="description">
                    <?php
                    $parts = array();
                    if ( NeoNews_Activity_Anomaly_Detector::alerts_enabled() ) {
                        $parts[] = sprintf(
                            /* translators: %s: email */
                            __( 'Email → %s', 'neonews-core' ),
                            NeoNews_Activity_Anomaly_Detector::get_alert_email()
                        );
                    }
                    if ( NeoNews_Activity_Anomaly_Detector::webhook_enabled() ) {
                        $parts[] = sprintf(
                            /* translators: %s: webhook type */
                            __( 'Webhook (%s)', 'neonews-core' ),
                            'slack' === NeoNews_Activity_Anomaly_Detector::get_webhook_type() ? 'Slack' : 'JSON'
                        );
                    }
                    echo esc_html( implode( ' · ', $parts ) );
                    ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-core-settings' ) ); ?>"><?php esc_html_e( 'Configure alerts', 'neonews-core' ); ?></a>
                </p>
            <?php endif; ?>

            <p class="description">
                <?php esc_html_e( 'REST API (admin auth required):', 'neonews-core' ); ?>
                <code><?php echo esc_html( rest_url( 'neonews/v1/activity' ) ); ?></code>
            </p>
        </div>
        <?php
    }

    /**
     * Recent activity on user edit screen.
     *
     * @param WP_User $user User object.
     */
    public function user_profile_section( $user ) {
        if ( ! current_user_can( 'manage_options' ) || ! NeoNews_User_Activity_Log::is_enabled() ) {
            return;
        }

        $result = NeoNews_User_Activity_Log::query(
            array(
                'user_id'  => $user->ID,
                'per_page' => 10,
                'page'     => 1,
            )
        );
        $types  = NeoNews_User_Activity_Log::get_event_types();
        $log_url = add_query_arg(
            array(
                'page'    => 'neonews-user-activity',
                'user_id' => $user->ID,
            ),
            admin_url( 'admin.php' )
        );
        ?>
        <h2><?php esc_html_e( 'NewsPulse Activity', 'neonews-core' ); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e( 'Recent activity', 'neonews-core' ); ?></th>
                <td>
                    <?php if ( empty( $result['items'] ) ) : ?>
                        <p><?php esc_html_e( 'No logged activity for this user yet.', 'neonews-core' ); ?></p>
                    <?php else : ?>
                        <ul class="nn-activity-user-list">
                            <?php foreach ( $result['items'] as $row ) : ?>
                                <li>
                                    <span class="nn-activity-time"><?php echo esc_html( mysql2date( 'M j, Y g:i A', $row['created_at'] ) ); ?></span>
                                    —
                                    <span class="nn-activity-badge nn-activity-<?php echo esc_attr( $row['event_type'] ); ?>">
                                        <?php echo esc_html( $types[ $row['event_type'] ] ?? $row['event_type'] ); ?>
                                    </span>
                                    <?php echo esc_html( $row['message'] ); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <p>
                        <a href="<?php echo esc_url( $log_url ); ?>" class="button button-secondary">
                            <?php esc_html_e( 'View full activity log', 'neonews-core' ); ?>
                        </a>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Export filtered log as CSV.
     */
    public function export_csv() {
        if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'neonews_export_activity' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'neonews-core' ) );
        }

        $filters = array(
            'user_id'    => isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0,
            'event_type' => isset( $_GET['event_type'] ) ? sanitize_key( wp_unslash( $_GET['event_type'] ) ) : '',
            'search'     => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
            'date_from'  => isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '',
            'date_to'    => isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '',
            'per_page'   => 5000,
            'page'       => 1,
        );

        $result = NeoNews_User_Activity_Log::query( $filters );
        $types  = NeoNews_User_Activity_Log::get_event_types();

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=neonews-user-activity-' . gmdate( 'Y-m-d' ) . '.csv' );

        $out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
        fputcsv( $out, array( 'Time (UTC)', 'User ID', 'Username', 'Event', 'Message', 'Object ID' ) );

        foreach ( $result['items'] as $row ) {
            fputcsv(
                $out,
                array(
                    $row['created_at'],
                    $row['user_id'],
                    $row['user_login'],
                    $types[ $row['event_type'] ] ?? $row['event_type'],
                    $row['message'],
                    $row['object_id'],
                )
            );
        }

        fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
        exit;
    }
}
