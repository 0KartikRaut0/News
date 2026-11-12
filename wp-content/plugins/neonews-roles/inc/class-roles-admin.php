<?php
/**
 * Admin UI — Role Manager settings page.
 *
 * @package NeoNews_Roles
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Roles_Admin {

    public static function init() {
        add_action( 'admin_menu',            array( __CLASS__, 'add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
        add_action( 'admin_post_nn_save_roles', array( __CLASS__, 'handle_save' ) );
    }

    /**
     * Register submenu under NeoNews.
     */
    public static function add_menu() {
        add_submenu_page(
            'neonews-settings',
            __( 'Role Manager', 'neonews-roles' ),
            __( 'Role Manager', 'neonews-roles' ),
            'manage_options',       // Only admins can access this page itself.
            'neonews-roles',
            array( __CLASS__, 'render' )
        );
    }

    /**
     * Enqueue styles.
     */
    public static function enqueue( $hook ) {
        if ( 'neonews_page_neonews-roles' !== $hook ) {
            return;
        }

        wp_add_inline_style( 'wp-admin', '
            /* ── Role Manager layout ── */
            #nn-roles-wrap { max-width: 1100px; }

            .nn-roles-table-wrap { overflow-x: auto; margin-top: 20px; }

            .nn-roles-table {
                width: 100%;
                border-collapse: collapse;
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 6px;
                overflow: hidden;
                font-size: 13px;
            }

            .nn-roles-table th,
            .nn-roles-table td {
                padding: 10px 14px;
                border-bottom: 1px solid #f0f0f0;
                text-align: center;
                vertical-align: middle;
                white-space: nowrap;
            }

            .nn-roles-table th {
                background: #1d3557;
                color: #fff;
                font-weight: 600;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: .4px;
            }

            .nn-roles-table th.nn-col-page {
                text-align: left;
                min-width: 180px;
            }

            .nn-roles-table td.nn-col-page {
                text-align: left;
                font-weight: 500;
                color: #333;
            }

            .nn-roles-table tr:last-child td { border-bottom: none; }

            .nn-roles-table tr:hover td { background: #f9fafb; }

            /* Group header rows */
            .nn-roles-group-row td {
                background: #f0f4f8 !important;
                font-weight: 700;
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: .6px;
                color: #1d3557;
                padding: 8px 14px;
                border-top: 2px solid #ccd0d4;
            }

            /* Toggle switch */
            .nn-toggle { position: relative; display: inline-block; width: 38px; height: 22px; }
            .nn-toggle input { opacity: 0; width: 0; height: 0; }
            .nn-toggle-slider {
                position: absolute; cursor: pointer;
                inset: 0;
                background: #ccc;
                border-radius: 22px;
                transition: background .2s;
            }
            .nn-toggle-slider:before {
                content: "";
                position: absolute;
                width: 16px; height: 16px;
                left: 3px; bottom: 3px;
                background: #fff;
                border-radius: 50%;
                transition: transform .2s;
                box-shadow: 0 1px 3px rgba(0,0,0,.2);
            }
            .nn-toggle input:checked + .nn-toggle-slider { background: #2d6a4f; }
            .nn-toggle input:checked + .nn-toggle-slider:before { transform: translateX(16px); }
            .nn-toggle input:disabled + .nn-toggle-slider { opacity: .5; cursor: not-allowed; }

            /* Admin badge */
            .nn-admin-badge {
                display: inline-block;
                background: #1d3557;
                color: #fff;
                font-size: 10px;
                font-weight: 700;
                padding: 2px 7px;
                border-radius: 10px;
                letter-spacing: .3px;
            }

            /* Bulk toggle row */
            .nn-roles-bulk-row td {
                background: #f6f7f8 !important;
                padding: 6px 14px;
            }
            .nn-bulk-btn {
                font-size: 11px;
                padding: 2px 8px;
                cursor: pointer;
                border: 1px solid #ccd0d4;
                border-radius: 3px;
                background: #fff;
                color: #555;
            }
            .nn-bulk-btn:hover { background: #e9ecef; }

            /* Save bar */
            .nn-roles-save-bar {
                position: sticky;
                bottom: 0;
                background: #fff;
                border-top: 2px solid #1d3557;
                padding: 14px 0;
                margin-top: 24px;
                display: flex;
                align-items: center;
                gap: 16px;
            }
            .nn-roles-notice {
                background: #fff;
                border-left: 4px solid #2d6a4f;
                padding: 10px 16px;
                margin-bottom: 20px;
                font-size: 14px;
            }
        ' );

        // Tiny JS for bulk toggle + unsaved-changes warning.
        wp_add_inline_script( 'jquery', '
        jQuery(function($){
            // Bulk toggles per role column.
            $(document).on("click", ".nn-bulk-btn", function(){
                var col   = $(this).data("col");   // 1-based column index
                var state = $(this).data("state") === "on";
                $(".nn-roles-table tbody tr:not(.nn-roles-group-row):not(.nn-roles-bulk-row)")
                    .find("td:eq(" + col + ") .nn-toggle input:not(:disabled)")
                    .prop("checked", state);
            });

            // Unsaved changes warning.
            var changed = false;
            $("#nn-roles-form input[type=checkbox]").on("change", function(){ changed = true; });
            window.addEventListener("beforeunload", function(e){
                if(changed){
                    e.preventDefault();
                    e.returnValue = "";
                }
            });
            $("#nn-roles-form").on("submit", function(){ changed = false; });
        });
        ' );
    }

    /**
     * Handle form save.
     */
    public static function handle_save() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'neonews-roles' ) );
        }
        check_admin_referer( 'nn_save_roles' );

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
        $input = isset( $_POST['nn_roles'] ) && is_array( $_POST['nn_roles'] ) ? $_POST['nn_roles'] : array();

        // Sanitize: only allow known roles & pages.
        $known_roles = array_keys( NeoNews_Roles_Manager::get_editable_roles() );
        $known_pages = array_keys( NeoNews_Roles_Manager::get_pages() );
        $clean       = array();

        foreach ( $known_roles as $role ) {
            $clean[ $role ] = array();
            foreach ( $known_pages as $page ) {
                $clean[ $role ][ $page ] = ! empty( $input[ $role ][ $page ] );
            }
        }

        NeoNews_Roles_Manager::save( $clean );

        wp_safe_redirect( add_query_arg( array(
            'page'    => 'neonews-roles',
            'updated' => '1',
        ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Render the page.
     */
    public static function render() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $permissions = NeoNews_Roles_Manager::get_permissions();
        $pages       = NeoNews_Roles_Manager::get_pages();
        $roles       = NeoNews_Roles_Manager::get_editable_roles();
        $role_slugs  = array_keys( $roles );

        $save_url = admin_url( 'admin-post.php' );
        ?>
        <div class="wrap" id="nn-roles-wrap">
            <h1><?php esc_html_e( 'Role Manager', 'neonews-roles' ); ?></h1>
            <p class="description" style="margin-bottom:4px;">
                <?php esc_html_e( 'Control which roles can access each admin page. Administrators always have full access and cannot be restricted.', 'neonews-roles' ); ?>
            </p>

            <?php if ( isset( $_GET['updated'] ) ) : ?>
            <div class="nn-roles-notice">
                ✅ <?php esc_html_e( 'Permissions saved successfully.', 'neonews-roles' ); ?>
            </div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url( $save_url ); ?>" id="nn-roles-form">
                <?php wp_nonce_field( 'nn_save_roles' ); ?>
                <input type="hidden" name="action" value="nn_save_roles">

                <div class="nn-roles-table-wrap">
                <table class="nn-roles-table">
                    <thead>
                        <tr>
                            <th class="nn-col-page"><?php esc_html_e( 'Page / Feature', 'neonews-roles' ); ?></th>
                            <!-- Administrator (read-only) -->
                            <th>
                                <?php esc_html_e( 'Administrator', 'neonews-roles' ); ?><br>
                                <span class="nn-admin-badge"><?php esc_html_e( 'Full Access', 'neonews-roles' ); ?></span>
                            </th>
                            <?php foreach ( $roles as $slug => $label ) : ?>
                            <th><?php echo esc_html( $label ); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>

                    <?php
                    // Bulk toggle row.
                    echo '<tr class="nn-roles-bulk-row">';
                    echo '<td style="text-align:left;font-size:11px;color:#888;">Bulk toggle ↓</td>';
                    echo '<td>—</td>';
                    $col_idx = 2; // 0-based td index in the row (0=page, 1=admin).
                    foreach ( $role_slugs as $role ) :
                    ?>
                    <td>
                        <button type="button" class="nn-bulk-btn" data-col="<?php echo esc_attr( $col_idx ); ?>" data-state="on" title="Grant all">✓ All</button>
                        <button type="button" class="nn-bulk-btn" data-col="<?php echo esc_attr( $col_idx ); ?>" data-state="off" title="Revoke all">✗ None</button>
                    </td>
                    <?php
                        $col_idx++;
                    endforeach;
                    echo '</tr>';

                    // Rows grouped by 'group'.
                    $current_group = '';
                    foreach ( $pages as $page_slug => $page_info ) :
                        $group = $page_info['group'];

                        // Group header.
                        if ( $group !== $current_group ) :
                            $current_group = $group;
                            $total_cols = count( $roles ) + 2; // +2 for page col + admin col.
                    ?>
                    <tr class="nn-roles-group-row">
                        <td colspan="<?php echo esc_attr( $total_cols ); ?>">
                            <?php echo esc_html( $group ); ?> <?php esc_html_e( 'Pages', 'neonews-roles' ); ?>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <tr>
                        <td class="nn-col-page"><?php echo esc_html( $page_info['label'] ); ?></td>

                        <!-- Administrator always ON, disabled -->
                        <td>
                            <label class="nn-toggle" title="<?php esc_attr_e( 'Administrators always have access', 'neonews-roles' ); ?>">
                                <input type="checkbox" checked disabled>
                                <span class="nn-toggle-slider"></span>
                            </label>
                        </td>

                        <?php foreach ( $role_slugs as $role ) :
                            $checked = ! empty( $permissions[ $role ][ $page_slug ] );
                        ?>
                        <td>
                            <label class="nn-toggle">
                                <input type="checkbox"
                                    name="nn_roles[<?php echo esc_attr( $role ); ?>][<?php echo esc_attr( $page_slug ); ?>]"
                                    value="1"
                                    <?php checked( $checked ); ?>
                                >
                                <span class="nn-toggle-slider"></span>
                            </label>
                        </td>
                        <?php endforeach; ?>
                    </tr>

                    <?php endforeach; ?>

                    </tbody>
                </table>
                </div><!-- .nn-roles-table-wrap -->

                <!-- Sticky save bar -->
                <div class="nn-roles-save-bar">
                    <?php submit_button( __( 'Save Permissions', 'neonews-roles' ), 'primary large', 'submit', false ); ?>
                    <span style="color:#666;font-size:13px;">
                        <?php esc_html_e( 'Changes take effect immediately after saving.', 'neonews-roles' ); ?>
                    </span>
                </div>

            </form>

            <!-- Info box -->
            <div style="background:#fff;border:1px solid #ccd0d4;border-radius:6px;padding:20px 24px;margin-top:8px;">
                <h3 style="margin-top:0;"><?php esc_html_e( 'How it works', 'neonews-roles' ); ?></h3>
                <ul style="margin-left:20px;line-height:1.9;color:#444;">
                    <li><?php esc_html_e( 'Toggle ON = role can see and access that page', 'neonews-roles' ); ?></li>
                    <li><?php esc_html_e( 'Toggle OFF = page is hidden from their admin menu AND direct URL access is blocked', 'neonews-roles' ); ?></li>
                    <li><?php esc_html_e( 'Administrators always have full access — they cannot be restricted here', 'neonews-roles' ); ?></li>
                    <li><?php esc_html_e( 'Use Bulk toggle buttons to grant/revoke all pages for a role at once', 'neonews-roles' ); ?></li>
                    <li><?php esc_html_e( 'To create custom roles (e.g. "SEO Manager"), go to WP Admin → Users → Add New and assign a role', 'neonews-roles' ); ?></li>
                </ul>
            </div>

        </div><!-- .wrap -->
        <?php
    }
}
