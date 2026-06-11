<?php
/**
 * Custom profile photo upload and display.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get stored profile photo attachment ID.
 *
 * @param int $user_id User ID.
 * @return int
 */
function neonews_get_profile_photo_id( $user_id ) {
    return (int) get_user_meta( $user_id, '_neonews_profile_photo_id', true );
}

/**
 * Resolve user ID from get_avatar args.
 *
 * @param mixed $id_or_email ID or email.
 * @return int
 */
function neonews_resolve_user_id( $id_or_email ) {
    if ( is_numeric( $id_or_email ) ) {
        return (int) $id_or_email;
    }

    if ( $id_or_email instanceof WP_User ) {
        return (int) $id_or_email->ID;
    }

    if ( $id_or_email instanceof WP_Post ) {
        return (int) $id_or_email->post_author;
    }

    if ( $id_or_email instanceof WP_Comment ) {
        return (int) $id_or_email->user_id;
    }

    if ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
        $user = get_user_by( 'email', $id_or_email );
        return $user ? (int) $user->ID : 0;
    }

    return 0;
}

/**
 * Profile photo HTML.
 *
 * @param int    $user_id User ID.
 * @param int    $size    Size in pixels.
 * @param string $class   Extra class names.
 * @return string
 */
function neonews_get_user_avatar_html( $user_id, $size = 96, $class = '' ) {
    $photo_id = neonews_get_profile_photo_id( $user_id );

    if ( $photo_id ) {
        $img = wp_get_attachment_image(
            $photo_id,
            array( $size, $size ),
            false,
            array(
                'class' => trim( 'nn-avatar nn-avatar-custom ' . $class ),
                'alt'   => esc_attr( get_the_author_meta( 'display_name', $user_id ) ),
            )
        );

        if ( $img ) {
            return $img;
        }
    }

    return get_avatar(
        $user_id,
        $size,
        '',
        '',
        array(
            'class' => trim( 'nn-avatar ' . $class ),
        )
    );
}

/**
 * Upload and attach profile photo.
 *
 * @param int   $user_id User ID.
 * @param array $file    $_FILES item.
 * @return int|WP_Error Attachment ID or error.
 */
function neonews_handle_profile_photo_upload( $user_id, $file ) {
    if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
        return new WP_Error( 'neonews_no_file', __( 'No photo was uploaded.', 'neonews' ) );
    }

    $allowed_types = array(
        'jpg|jpeg|jpe' => 'image/jpeg',
        'gif'          => 'image/gif',
        'png'          => 'image/png',
        'webp'         => 'image/webp',
    );

    $check = wp_check_filetype( $file['name'], $allowed_types );
    if ( empty( $check['type'] ) ) {
        return new WP_Error( 'neonews_invalid_type', __( 'Please upload a JPG, PNG, GIF, or WebP image.', 'neonews' ) );
    }

    if ( ! empty( $file['size'] ) && $file['size'] > 2 * MB_IN_BYTES ) {
        return new WP_Error( 'neonews_file_large', __( 'Photo must be smaller than 2 MB.', 'neonews' ) );
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $upload = wp_handle_upload(
        $file,
        array(
            'test_form' => false,
            'mimes'     => $allowed_types,
        )
    );

    if ( ! empty( $upload['error'] ) ) {
        return new WP_Error( 'neonews_upload_error', $upload['error'] );
    }

    $attachment_id = wp_insert_attachment(
        array(
            'post_mime_type' => $check['type'],
            'post_title'     => sanitize_file_name( pathinfo( $file['name'], PATHINFO_FILENAME ) ),
            'post_content'   => '',
            'post_status'    => 'inherit',
            'post_author'    => $user_id,
        ),
        $upload['file']
    );

    if ( is_wp_error( $attachment_id ) ) {
        return $attachment_id;
    }

    wp_update_attachment_metadata(
        $attachment_id,
        wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
    );

    $old_id = neonews_get_profile_photo_id( $user_id );
    if ( $old_id && $old_id !== $attachment_id ) {
        wp_delete_attachment( $old_id, true );
    }

    update_user_meta( $user_id, '_neonews_profile_photo_id', $attachment_id );

    if ( function_exists( 'neonews_log_user_activity' ) ) {
        neonews_log_user_activity(
            'profile_photo',
            __( 'Profile photo updated', 'neonews' ),
            $user_id,
            array( 'attachment_id' => $attachment_id ),
            $user_id
        );
    }

    return $attachment_id;
}

/**
 * Remove profile photo.
 *
 * @param int $user_id User ID.
 */
function neonews_remove_profile_photo( $user_id ) {
    $photo_id = neonews_get_profile_photo_id( $user_id );
    if ( $photo_id ) {
        wp_delete_attachment( $photo_id, true );
    }
    delete_user_meta( $user_id, '_neonews_profile_photo_id' );

    if ( function_exists( 'neonews_log_user_activity' ) ) {
        neonews_log_user_activity(
            'profile_photo',
            __( 'Profile photo removed', 'neonews' ),
            $user_id,
            array(),
            $user_id
        );
    }
}

/**
 * Use custom profile photo in get_avatar().
 *
 * @param string $avatar      Avatar HTML.
 * @param mixed  $id_or_email User identifier.
 * @param int    $size        Size.
 * @param string $default     Default URL.
 * @param string $alt         Alt text.
 * @param array  $args        Extra args.
 * @return string
 */
function neonews_filter_get_avatar( $avatar, $id_or_email, $size, $default, $alt, $args ) {
    $user_id = neonews_resolve_user_id( $id_or_email );
    if ( ! $user_id ) {
        return $avatar;
    }

    $photo_id = neonews_get_profile_photo_id( $user_id );
    if ( ! $photo_id ) {
        return $avatar;
    }

    $class = isset( $args['class'] ) ? $args['class'] : '';
    if ( is_array( $class ) ) {
        $class = implode( ' ', $class );
    }

    $img = wp_get_attachment_image(
        $photo_id,
        array( (int) $size, (int) $size ),
        false,
        array(
            'class' => trim( 'nn-avatar nn-avatar-custom avatar avatar-' . (int) $size . ' photo ' . $class ),
            'alt'   => $alt ? $alt : esc_attr( get_the_author_meta( 'display_name', $user_id ) ),
        )
    );

    return $img ? $img : $avatar;
}
add_filter( 'get_avatar', 'neonews_filter_get_avatar', 10, 6 );
