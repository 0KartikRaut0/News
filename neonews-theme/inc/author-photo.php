<?php
/**
 * Author photo upload in WordPress admin (Users → Edit User).
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue media scripts on user profile screens.
 *
 * @param string $hook Admin page hook.
 */
function neonews_author_photo_admin_assets( $hook ) {
    if ( ! in_array( $hook, array( 'user-edit.php', 'profile.php' ), true ) ) {
        return;
    }

    wp_enqueue_media();
    wp_add_inline_script(
        'jquery',
        "(function($){
            $(function(){
                var frame;
                var \$input = $('#neonews_author_photo_id');
                var \$preview = $('#neonews-author-photo-preview');
                $('#neonews-author-photo-select').on('click', function(e){
                    e.preventDefault();
                    if (frame) { frame.open(); return; }
                    frame = wp.media({
                        title: '" . esc_js( __( 'Select author photo', 'neonews' ) ) . "',
                        button: { text: '" . esc_js( __( 'Use photo', 'neonews' ) ) . "' },
                        multiple: false,
                        library: { type: 'image' }
                    });
                    frame.on('select', function(){
                        var attachment = frame.state().get('selection').first().toJSON();
                        \$input.val(attachment.id);
                        \$preview.html('<img src=\"' + attachment.url + '\" alt=\"\" style=\"width:96px;height:96px;border-radius:50%;object-fit:cover;\" />');
                        $('#neonews-remove-author-photo').prop('checked', false);
                    });
                    frame.open();
                });
                $('#neonews-author-photo-clear').on('click', function(e){
                    e.preventDefault();
                    \$input.val('');
                    \$preview.html('<span class=\"neonews-author-photo-empty\">" . esc_js( __( 'No photo selected', 'neonews' ) ) . "</span>');
                    $('#neonews-remove-author-photo').prop('checked', true);
                });
            });
        })(jQuery);"
    );
}
add_action( 'admin_enqueue_scripts', 'neonews_author_photo_admin_assets' );

/**
 * Author photo field on user profile.
 *
 * @param WP_User $user User object.
 */
function neonews_render_author_photo_field( $user ) {
    if ( ! current_user_can( 'upload_files' ) ) {
        return;
    }

    $photo_id = neonews_get_profile_photo_id( $user->ID );
    ?>
    <h2><?php esc_html_e( 'Author Photo', 'neonews' ); ?></h2>
    <p class="description" style="margin-bottom:12px;">
        <?php esc_html_e( 'Upload a display photo for this author. It appears on posts next to “By Author Name”.', 'neonews' ); ?>
    </p>
    <table class="form-table" role="presentation">
        <tr>
            <th><label for="neonews_author_photo_id"><?php esc_html_e( 'Photo', 'neonews' ); ?></label></th>
            <td>
                <div id="neonews-author-photo-preview" style="margin-bottom:12px;">
                    <?php
                    if ( $photo_id ) {
                        echo wp_get_attachment_image( $photo_id, array( 96, 96 ), false, array(
                            'style' => 'width:96px;height:96px;border-radius:50%;object-fit:cover;',
                        ) );
                    } else {
                        echo get_avatar( $user->ID, 96 );
                    }
                    ?>
                </div>
                <input type="hidden" name="neonews_author_photo_id" id="neonews_author_photo_id" value="<?php echo esc_attr( $photo_id ); ?>" />
                <p>
                    <button type="button" class="button button-secondary" id="neonews-author-photo-select"><?php esc_html_e( 'Upload / select photo', 'neonews' ); ?></button>
                    <button type="button" class="button" id="neonews-author-photo-clear"><?php esc_html_e( 'Remove photo', 'neonews' ); ?></button>
                </p>
                <label style="display:none;">
                    <input type="checkbox" name="neonews_remove_author_photo" id="neonews-remove-author-photo" value="1" />
                </label>
                <p class="description"><?php esc_html_e( 'Recommended: square image, at least 200×200 px.', 'neonews' ); ?></p>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'neonews_render_author_photo_field' );
add_action( 'edit_user_profile', 'neonews_render_author_photo_field' );

/**
 * Save author photo from admin user profile.
 *
 * @param int $user_id User ID.
 */
function neonews_save_author_photo_field( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }

    if ( ! empty( $_POST['neonews_remove_author_photo'] ) ) {
        neonews_remove_profile_photo( $user_id );
        return;
    }

    if ( ! isset( $_POST['neonews_author_photo_id'] ) ) {
        return;
    }

    $attachment_id = absint( $_POST['neonews_author_photo_id'] );
    if ( ! $attachment_id ) {
        neonews_remove_profile_photo( $user_id );
        return;
    }

    if ( 'attachment' !== get_post_type( $attachment_id ) ) {
        return;
    }

    $old_id = neonews_get_profile_photo_id( $user_id );
    if ( $old_id && $old_id !== $attachment_id ) {
        wp_delete_attachment( $old_id, true );
    }

    update_user_meta( $user_id, '_neonews_profile_photo_id', $attachment_id );
}
add_action( 'personal_options_update', 'neonews_save_author_photo_field' );
add_action( 'edit_user_profile_update', 'neonews_save_author_photo_field' );
