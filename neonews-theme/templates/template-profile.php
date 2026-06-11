<?php
/**
 * Template Name: My Profile
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_user_logged_in() ) {
    wp_safe_redirect( neonews_get_account_url() );
    exit;
}

$user_id = get_current_user_id();
$user    = wp_get_current_user();
$saved   = isset( $_GET['profile_updated'] );
$error   = '';

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['neonews_profile_nonce'] ) ) {
    if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['neonews_profile_nonce'] ) ), 'neonews_update_profile' ) ) {
        if ( ! empty( $_POST['remove_profile_photo'] ) ) {
            neonews_remove_profile_photo( $user_id );
        } elseif ( ! empty( $_FILES['profile_photo']['tmp_name'] ) ) {
            $upload_result = neonews_handle_profile_photo_upload( $user_id, $_FILES['profile_photo'] );
            if ( is_wp_error( $upload_result ) ) {
                $error = $upload_result->get_error_message();
            }
        }

        if ( ! $error ) {
            $display_name = isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';
            $bio          = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
            $phone        = isset( $_POST['neonews_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['neonews_phone'] ) ) : '';

            if ( $display_name ) {
                wp_update_user( array(
                    'ID'           => $user_id,
                    'display_name' => $display_name,
                    'description'  => $bio,
                ) );
            }
            update_user_meta( $user_id, '_neonews_phone', $phone );
            wp_safe_redirect( add_query_arg( 'profile_updated', '1', get_permalink() ) );
            exit;
        }
    }
}

get_header();
?>

<div class="nn-container nn-page-fancy nn-profile-page">
    <?php if ( $saved ) : ?>
        <div class="nn-toast nn-toast-success nn-toast-visible"><?php esc_html_e( 'Profile updated successfully.', 'neonews' ); ?></div>
    <?php endif; ?>

    <?php if ( $error ) : ?>
        <div class="nn-form-error nn-profile-error"><?php echo esc_html( $error ); ?></div>
    <?php endif; ?>

    <header class="nn-profile-hero nn-reveal">
        <div class="nn-profile-photo-wrap" id="nn-profile-photo-preview">
            <?php
            $avatar = neonews_get_user_avatar_html( $user_id, 96, 'nn-profile-photo' );
            if ( class_exists( 'NeoNews_Membership_Display' ) ) {
                echo NeoNews_Membership_Display::wrap_avatar_with_badge( $avatar, $user_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            } else {
                echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            ?>
        </div>
        <div>
            <h1><?php echo esc_html( $user->display_name ); ?></h1>
            <p><?php echo esc_html( $user->user_email ); ?></p>
            <?php
            if ( function_exists( 'neonews_membership_render_plan_tag' ) ) {
                neonews_membership_render_plan_tag( 'profile', $user_id );
            } elseif ( function_exists( 'neonews_is_user_premium' ) && neonews_is_user_premium() ) {
                echo '<span class="nn-member-badge nn-badge-premium">' . esc_html__( 'Premium Member', 'neonews' ) . '</span>';
            }
            ?>
        </div>
    </header>

    <?php do_action( 'neonews_profile_membership_panel' ); ?>

    <div class="nn-profile-grid nn-reveal">
        <div class="nn-page-card">
            <h2><?php esc_html_e( 'Basic details', 'neonews' ); ?></h2>
            <form method="post" class="nn-profile-form" enctype="multipart/form-data">
                <?php wp_nonce_field( 'neonews_update_profile', 'neonews_profile_nonce' ); ?>

                <div class="nn-profile-photo-upload">
                    <label class="nn-profile-photo-label"><?php esc_html_e( 'Profile photo', 'neonews' ); ?></label>
                    <div class="nn-profile-photo-controls">
                        <label class="nn-btn nn-btn-fancy nn-btn-fancy-outline nn-profile-photo-btn">
                            <span class="nn-btn-fancy-icon" aria-hidden="true">📷</span>
                            <span><?php esc_html_e( 'Upload photo', 'neonews' ); ?></span>
                            <input type="file" name="profile_photo" id="nn-profile-photo-input" accept="image/jpeg,image/png,image/webp,image/gif" />
                        </label>
                        <?php if ( neonews_get_profile_photo_id( $user_id ) ) : ?>
                            <button type="submit" name="remove_profile_photo" value="1" class="nn-btn nn-btn-text nn-profile-photo-remove"><?php esc_html_e( 'Remove photo', 'neonews' ); ?></button>
                        <?php endif; ?>
                    </div>
                    <p class="nn-profile-photo-hint"><?php esc_html_e( 'JPG, PNG, GIF, or WebP. Max 2 MB.', 'neonews' ); ?></p>
                </div>

                <label><?php esc_html_e( 'Display name', 'neonews' ); ?></label>
                <input type="text" name="display_name" class="nn-form-input" value="<?php echo esc_attr( $user->display_name ); ?>" required />

                <label><?php esc_html_e( 'Email address', 'neonews' ); ?></label>
                <input type="email" class="nn-form-input" value="<?php echo esc_attr( $user->user_email ); ?>" disabled />
                <p class="nn-field-note"><?php esc_html_e( 'Email changes require administrator approval. Contact the site admin to update your email.', 'neonews' ); ?></p>

                <label><?php esc_html_e( 'Phone number', 'neonews' ); ?></label>
                <input type="tel" name="neonews_phone" class="nn-form-input" value="<?php echo esc_attr( get_user_meta( $user_id, '_neonews_phone', true ) ); ?>" placeholder="+1 555 000 0000" />
                <p class="nn-field-note"><?php esc_html_e( 'Phone updates may require admin verification on some sites.', 'neonews' ); ?></p>

                <label><?php esc_html_e( 'Bio', 'neonews' ); ?></label>
                <textarea name="description" class="nn-form-textarea" rows="4"><?php echo esc_textarea( $user->description ); ?></textarea>

                <button type="submit" class="nn-btn"><?php esc_html_e( 'Save profile', 'neonews' ); ?></button>
            </form>
        </div>

        <div class="nn-page-card">
            <h2><?php esc_html_e( 'Password & security', 'neonews' ); ?></h2>
            <p><?php esc_html_e( 'For security, password changes are handled by your site administrator.', 'neonews' ); ?></p>
            <ul class="nn-profile-security-list">
                <li><?php esc_html_e( 'Ask an admin to reset your password from Users in WordPress admin.', 'neonews' ); ?></li>
                <li><?php esc_html_e( 'Or use the lost password link if enabled on login.', 'neonews' ); ?></li>
            </ul>
            <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="nn-btn nn-btn-fancy nn-btn-fancy-outline nn-btn-fancy-soft">
                <span class="nn-btn-fancy-icon" aria-hidden="true">✉</span>
                <?php esc_html_e( 'Reset password via email', 'neonews' ); ?>
            </a>
            <hr />
            <a href="<?php echo esc_url( neonews_get_account_url() ); ?>" class="nn-section-link"><?php esc_html_e( 'View my activity →', 'neonews' ); ?></a>
        </div>
    </div>
</div>

<?php get_footer(); ?>
