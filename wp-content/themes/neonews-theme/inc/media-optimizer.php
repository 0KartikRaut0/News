<?php
/**
 * Media uploads — size limits, WebP output, fewer derivatives.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Max upload size in bytes from settings.
 *
 * @return int
 */
function neonews_get_max_upload_bytes() {
	$mb = (int) neonews_get_setting( 'media_max_upload_mb', 4 );
	$mb = max( 1, min( 32, $mb ) );
	return $mb * MB_IN_BYTES;
}

/**
 * Whether the server can create WebP images.
 *
 * @return bool
 */
function neonews_media_webp_supported() {
	return (bool) wp_image_editor_supports(
		array(
			'mime_type' => 'image/webp',
		)
	);
}

/**
 * Register upload optimizations.
 */
function neonews_media_optimizer_bootstrap() {
	if ( neonews_get_setting( 'media_limit_upload_size', true ) ) {
		add_filter( 'wp_handle_upload_prefilter', 'neonews_media_upload_prefilter' );
		add_filter( 'neonews_max_upload_bytes', 'neonews_get_max_upload_bytes' );
	}

	if ( neonews_get_setting( 'media_force_webp', true ) && neonews_media_webp_supported() ) {
		add_filter( 'image_editor_output_format', 'neonews_media_webp_output_format', 10, 2 );
	}

	if ( neonews_get_setting( 'media_strip_extra_sizes', true ) ) {
		add_filter( 'intermediate_image_sizes_advanced', 'neonews_media_limit_derivatives', 10, 2 );
		$max = (int) neonews_get_setting( 'media_big_image_max', 1920 );
		$max = max( 1200, min( 2560, $max ) );
		add_filter(
			'big_image_size_threshold',
			static function () use ( $max ) {
				return $max;
			}
		);
	}
}
add_action( 'init', 'neonews_media_optimizer_bootstrap', 20 );

/**
 * Reject oversize uploads (Media Library + front-end uploads using core handlers).
 *
 * @param array $file Upload array.
 * @return array
 */
function neonews_media_upload_prefilter( $file ) {
	if ( empty( $file['size'] ) || empty( $file['name'] ) ) {
		return $file;
	}

	$max = neonews_get_max_upload_bytes();
	if ( (int) $file['size'] <= $max ) {
		return $file;
	}

	$mb = round( $max / MB_IN_BYTES, 1 );
	$file['error'] = sprintf(
		/* translators: %s: max size in megabytes */
		__( 'This file is too large. Maximum upload size is %s MB (NewsPulse → Platform → Media uploads).', 'neonews' ),
		$mb
	);

	return $file;
}

/**
 * Save JPEG/PNG uploads as WebP when supported (WordPress 6.1+).
 *
 * @param array  $formats MIME map.
 * @param string $filename File path.
 * @return array
 */
function neonews_media_webp_output_format( $formats, $filename ) {
	unset( $filename );
	$formats['image/jpeg'] = 'image/webp';
	$formats['image/png']  = 'image/webp';
	return $formats;
}

/**
 * Only generate sizes the theme actually uses (+ admin thumbnails).
 *
 * @param array $sizes    Sizes to generate.
 * @param array $metadata Attachment metadata.
 * @return array
 */
function neonews_media_limit_derivatives( $sizes, $metadata ) {
	unset( $metadata );

	$allow = array(
		'thumbnail'          => true,
		'medium'             => true,
		'large'              => true,
		'neonews-featured'   => true,
		'neonews-card'       => true,
		'neonews-thumbnail'  => true,
	);

	foreach ( array_keys( $sizes ) as $name ) {
		if ( empty( $allow[ $name ] ) ) {
			unset( $sizes[ $name ] );
		}
	}

	return $sizes;
}
