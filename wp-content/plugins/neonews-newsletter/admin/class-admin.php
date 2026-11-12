<?php
/**
 * Admin dashboard — subscriber list, stats, CSV export, settings.
 *
 * @package NeoNews_Newsletter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Newsletter_Admin {

    public static function init() {
        add_action( 'admin_menu',    array( __CLASS__, 'add_menu' ) );
        add_action( 'admin_init',    array( __CLASS__, 'handle_export' ) );
        add_action( 'admin_init',    array( __CLASS__, 'handle_delete' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
    }

    /**
     * Add submenu under NeoNews settings.
     */
    public static function add_menu() {
        add_submenu_page(
            'neonews-settings',
            __( 'Newsletter Subscribers', 'neonews-newsletter' ),
            __( 'Subscribers', 'neonews-newsletter' ),
            'manage_options',
            'neonews-subscribers',
            array( __CLASS__, 'render_page' )
        );
    }

    /**
     * Enqueue minimal admin styles.
     */
    public static function enqueue_styles( $hook ) {
        if ( 'neonews_page_neonews-subscribers' !== $hook ) {
            return;
        }
        wp_add_inline_style( 'wp-admin', '
            .nn-sub-stats { display:flex; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
            .nn-sub-stat  { background:#fff; border:1px solid #ccd0d4; border-radius:6px; padding:16px 24px; text-align:center; min-width:120px; }
            .nn-sub-stat strong { display:block; font-size:28px; color:#1d3557; }
            .nn-sub-stat span   { font-size:12px; color:#666; text-transform:uppercase; letter-spacing:.5px; }
            .nn-sub-stat.confirmed strong { color:#2d6a4f; }
            .nn-sub-stat.pending strong   { color:#e67e22; }
            .nn-sub-stat.unsubscribed strong { color:#c0392b; }
            .nn-sub-filters { display:flex; gap:10px; align-items:center; margin-bottom:16px; flex-wrap:wrap; }
            .nn-sub-search  { padding:6px 10px; border:1px solid #ccd0d4; border-radius:4px; font-size:13px; min-width:200px; }
            .nn-export-btn  { display:inline-flex; align-items:center; gap:6px; }
        ' );
    }

    /**
     * Handle CSV export.
     */
    public static function handle_export() {
        if ( ! isset( $_GET['page'], $_GET['nn_export'] ) || 'neonews-subscribers' !== $_GET['page'] ) {
            return;
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'neonews-newsletter' ) );
        }
        check_admin_referer( 'nn_export_subscribers' );

        $rows = NeoNews_Newsletter_DB::get_all_confirmed();

        header( 'Content-Type: text/csv; charset=UTF-8' );
        header( 'Content-Disposition: attachment; filename="subscribers-' . gmdate( 'Y-m-d' ) . '.csv"' );
        header( 'Pragma: no-cache' );

        $out = fopen( 'php://output', 'w' );
        fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ); // UTF-8 BOM.
        fputcsv( $out, array( 'ID', 'Email', 'First Name', 'Subscribed At', 'Confirmed At' ) );

        foreach ( $rows as $row ) {
            fputcsv( $out, array(
                $row->id,
                $row->email,
                $row->first_name,
                $row->subscribed_at,
                $row->confirmed_at ?? '',
            ) );
        }

        fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
        exit;
    }

    /**
     * Handle single subscriber delete.
     */
    public static function handle_delete() {
        if ( ! isset( $_GET['page'], $_GET['nn_delete'] ) || 'neonews-subscribers' !== $_GET['page'] ) {
            return;
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'neonews-newsletter' ) );
        }
        check_admin_referer( 'nn_delete_subscriber_' . (int) $_GET['nn_delete'] );

        NeoNews_Newsletter_DB::delete( (int) $_GET['nn_delete'] );

        wp_safe_redirect( remove_query_arg( array( 'nn_delete', '_wpnonce' ) ) );
        exit;
    }

    /**
     * Render the admin page.
     */
    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $counts  = NeoNews_Newsletter_DB::get_counts();
        $status  = sanitize_key( $_GET['nn_status_filter'] ?? '' );
        $search  = sanitize_text_field( wp_unslash( $_GET['nn_search'] ?? '' ) );
        $page    = max( 1, (int) ( $_GET['nn_paged'] ?? 1 ) );

        $result  = NeoNews_Newsletter_DB::get_subscribers( array(
            'status'   => $status,
            'search'   => $search,
            'per_page' => 25,
            'page'     => $page,
        ) );

        $rows       = $result['rows'];
        $total      = $result['total'];
        $total_pages= (int) ceil( $total / 25 );

        $export_url = wp_nonce_url(
            add_query_arg( array( 'page' => 'neonews-subscribers', 'nn_export' => '1' ), admin_url( 'admin.php' ) ),
            'nn_export_subscribers'
        );

        if ( function_exists( 'neonews_admin_shell_begin' ) ) {
            neonews_admin_shell_begin(
                array(
                    'id'       => 'nn-subscribers',
                    'icon'     => '📧',
                    'title'    => __( 'Subscribers', 'neonews-newsletter' ),
                    'subtitle' => __( 'Newsletter sign-ups, confirmation flow, and CSV export.', 'neonews-newsletter' ),
                    'stats'    => array(
                        array(
                            'icon'  => '👥',
                            'value' => number_format_i18n( $counts['total'] ),
                            'label' => __( 'Total', 'neonews-newsletter' ),
                        ),
                        array(
                            'icon'  => '✅',
                            'value' => number_format_i18n( $counts['confirmed'] ),
                            'label' => __( 'Confirmed', 'neonews-newsletter' ),
                        ),
                        array(
                            'icon'  => '⏳',
                            'value' => number_format_i18n( $counts['pending'] ),
                            'label' => __( 'Pending', 'neonews-newsletter' ),
                        ),
                        array(
                            'icon'  => '🚫',
                            'value' => number_format_i18n( $counts['unsubscribed'] ),
                            'label' => __( 'Unsubscribed', 'neonews-newsletter' ),
                        ),
                    ),
                    'actions'  => array_merge(
                        array(
                            array(
                                'label'   => __( 'Export CSV', 'neonews-newsletter' ),
                                'url'     => $export_url,
                                'primary' => true,
                            ),
                        ),
                        function_exists( 'neonews_admin_shell_default_actions' ) ? neonews_admin_shell_default_actions() : array()
                    ),
                )
            );
            neonews_admin_shell_panel_open();
        } else {
            echo '<div class="wrap"><h1>' . esc_html__( 'Newsletter Subscribers', 'neonews-newsletter' ) . '</h1>';
        }
        ?>
            <div class="nn-sub-stats">
                <div class="nn-sub-stat">
                    <strong><?php echo esc_html( $counts['total'] ); ?></strong>
                    <span><?php esc_html_e( 'Total', 'neonews-newsletter' ); ?></span>
                </div>
                <div class="nn-sub-stat confirmed">
                    <strong><?php echo esc_html( $counts['confirmed'] ); ?></strong>
                    <span><?php esc_html_e( 'Confirmed', 'neonews-newsletter' ); ?></span>
                </div>
                <div class="nn-sub-stat pending">
                    <strong><?php echo esc_html( $counts['pending'] ); ?></strong>
                    <span><?php esc_html_e( 'Pending', 'neonews-newsletter' ); ?></span>
                </div>
                <div class="nn-sub-stat unsubscribed">
                    <strong><?php echo esc_html( $counts['unsubscribed'] ); ?></strong>
                    <span><?php esc_html_e( 'Unsubscribed', 'neonews-newsletter' ); ?></span>
                </div>
            </div>

            <!-- Filters -->
            <form method="get" action="">
                <input type="hidden" name="page" value="neonews-subscribers">
                <div class="nn-sub-filters">
                    <input type="text" name="nn_search" class="nn-sub-search" placeholder="<?php esc_attr_e( 'Search email…', 'neonews-newsletter' ); ?>" value="<?php echo esc_attr( $search ); ?>">
                    <select name="nn_status_filter">
                        <option value=""><?php esc_html_e( 'All statuses', 'neonews-newsletter' ); ?></option>
                        <option value="confirmed"    <?php selected( $status, 'confirmed' ); ?>><?php esc_html_e( 'Confirmed', 'neonews-newsletter' ); ?></option>
                        <option value="pending"      <?php selected( $status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'neonews-newsletter' ); ?></option>
                        <option value="unsubscribed" <?php selected( $status, 'unsubscribed' ); ?>><?php esc_html_e( 'Unsubscribed', 'neonews-newsletter' ); ?></option>
                    </select>
                    <?php submit_button( __( 'Filter', 'neonews-newsletter' ), 'secondary', '', false ); ?>
                    <?php if ( $search || $status ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-subscribers' ) ); ?>"><?php esc_html_e( 'Clear', 'neonews-newsletter' ); ?></a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Table -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( '#', 'neonews-newsletter' ); ?></th>
                        <th><?php esc_html_e( 'Email', 'neonews-newsletter' ); ?></th>
                        <th><?php esc_html_e( 'Name', 'neonews-newsletter' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'neonews-newsletter' ); ?></th>
                        <th><?php esc_html_e( 'Subscribed', 'neonews-newsletter' ); ?></th>
                        <th><?php esc_html_e( 'Confirmed', 'neonews-newsletter' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'neonews-newsletter' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( empty( $rows ) ) : ?>
                    <tr><td colspan="7" style="text-align:center;padding:24px;color:#888;">
                        <?php esc_html_e( 'No subscribers found.', 'neonews-newsletter' ); ?>
                    </td></tr>
                <?php else : ?>
                    <?php foreach ( $rows as $row ) :
                        $status_colors = array(
                            'confirmed'    => '#2d6a4f',
                            'pending'      => '#e67e22',
                            'unsubscribed' => '#c0392b',
                        );
                        $color      = $status_colors[ $row->status ] ?? '#666';
                        $delete_url = wp_nonce_url(
                            add_query_arg( array(
                                'page'      => 'neonews-subscribers',
                                'nn_delete' => $row->id,
                            ), admin_url( 'admin.php' ) ),
                            'nn_delete_subscriber_' . $row->id
                        );
                    ?>
                    <tr>
                        <td><?php echo esc_html( $row->id ); ?></td>
                        <td><strong><?php echo esc_html( $row->email ); ?></strong></td>
                        <td><?php echo esc_html( $row->first_name ?: '—' ); ?></td>
                        <td><span style="color:<?php echo esc_attr( $color ); ?>;font-weight:600;text-transform:capitalize;"><?php echo esc_html( $row->status ); ?></span></td>
                        <td><?php echo esc_html( $row->subscribed_at ? date_i18n( get_option( 'date_format' ), strtotime( $row->subscribed_at ) ) : '—' ); ?></td>
                        <td><?php echo esc_html( $row->confirmed_at ? date_i18n( get_option( 'date_format' ), strtotime( $row->confirmed_at ) ) : '—' ); ?></td>
                        <td>
                            <a href="<?php echo esc_url( $delete_url ); ?>" class="button button-small" onclick="return confirm('<?php esc_attr_e( 'Delete this subscriber?', 'neonews-newsletter' ); ?>');" style="color:#c0392b;border-color:#c0392b;">
                                <?php esc_html_e( 'Delete', 'neonews-newsletter' ); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ( $total_pages > 1 ) : ?>
            <div class="tablenav bottom" style="margin-top:12px;">
                <div class="tablenav-pages">
                    <span class="displaying-num">
                        <?php printf( esc_html__( '%s items', 'neonews-newsletter' ), number_format_i18n( $total ) ); ?>
                    </span>
                    <span class="pagination-links">
                    <?php for ( $p = 1; $p <= $total_pages; $p++ ) :
                        $purl = add_query_arg( array(
                            'page'      => 'neonews-subscribers',
                            'nn_paged'  => $p,
                            'nn_search' => $search,
                            'nn_status_filter' => $status,
                        ), admin_url( 'admin.php' ) );
                    ?>
                        <a href="<?php echo esc_url( $purl ); ?>" class="button button-small<?php echo $p === $page ? ' button-primary' : ''; ?>"><?php echo esc_html( $p ); ?></a>
                    <?php endfor; ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Info box -->
            <div style="background:#fff;border:1px solid #ccd0d4;border-radius:6px;padding:20px 24px;margin-top:24px;">
                <h3 style="margin-top:0;"><?php esc_html_e( 'How it works', 'neonews-newsletter' ); ?></h3>
                <ol style="margin-left:20px;line-height:1.8;">
                    <li><?php esc_html_e( 'Visitor enters email → stored as "Pending"', 'neonews-newsletter' ); ?></li>
                    <li><?php esc_html_e( 'Confirmation email sent via wp_mail()', 'neonews-newsletter' ); ?></li>
                    <li><?php esc_html_e( 'Visitor clicks confirm link → status changes to "Confirmed" + welcome email sent', 'neonews-newsletter' ); ?></li>
                    <li><?php esc_html_e( 'Export confirmed subscribers as CSV anytime for sending newsletters', 'neonews-newsletter' ); ?></li>
                </ol>
                <p style="margin-bottom:0;color:#666;font-size:13px;">
                    <?php esc_html_e( 'Tip: For reliable email delivery, install WP Mail SMTP and connect it to Gmail, Outlook, or Brevo (free).', 'neonews-newsletter' ); ?>
                </p>
            </div>
        <?php
        if ( function_exists( 'neonews_admin_shell_panel_close' ) ) {
            neonews_admin_shell_panel_close();
            neonews_admin_shell_end();
        } else {
            echo '</div>';
        }
    }
}
