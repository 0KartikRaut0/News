<?php
/**
 * Theme activation — prompt to import full demo.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * After theme switch, flag admin to import demo.
 */
function neonews_after_switch_theme() {
    if ( get_option( 'neonews_demo_imported' ) ) {
        return;
    }
    set_transient( 'neonews_show_demo_notice', 1, WEEK_IN_SECONDS );
}
add_action( 'after_switch_theme', 'neonews_after_switch_theme' );

/**
 * Admin notice to import turnkey demo.
 */
function neonews_demo_import_admin_notice() {
    if ( ! get_transient( 'neonews_show_demo_notice' ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $url = admin_url( 'admin.php?page=neonews-demo' );
    ?>
    <div class="notice notice-info is-dismissible">
        <p>
            <strong><?php esc_html_e( 'NeoNews:', 'neonews' ); ?></strong>
            <?php esc_html_e( 'Import the full demo to get menus, widgets, homepage, images, and a ready-to-edit youth news layout.', 'neonews' ); ?>
            <a href="<?php echo esc_url( $url ); ?>" class="button button-primary" style="margin-left:8px;"><?php esc_html_e( 'Import Full Demo', 'neonews' ); ?></a>
        </p>
    </div>
    <?php
}
add_action( 'admin_notices', 'neonews_demo_import_admin_notice' );

/**
 * Dismiss notice when demo imported.
 */
function neonews_clear_demo_notice() {
    if ( get_option( 'neonews_demo_imported' ) ) {
        delete_transient( 'neonews_show_demo_notice' );
    }
}
add_action( 'admin_init', 'neonews_clear_demo_notice' );
