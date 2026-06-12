<?php
/**
 * Demo images — generated locally (works on Local WP without allow_url_fopen).
 *
 * @package NeoNews_Demo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Demo Images Class
 */
class NeoNews_Demo_Images {

    /**
     * Category color presets (RGB).
     */
    private static $category_colors = array(
        'politics'      => array( 124, 58, 237 ),
        'technology'    => array( 6, 182, 212 ),
        'business'      => array( 245, 158, 11 ),
        'sports'        => array( 34, 197, 94 ),
        'entertainment' => array( 244, 114, 182 ),
        'default'       => array( 99, 102, 241 ),
    );

    /**
     * Attach featured images to all posts in map.
     *
     * @param array $post_image_map Post ID => seed string.
     * @param bool  $force          Replace existing thumbnails.
     * @return int Number attached.
     */
    public static function attach_featured_images( $post_image_map, $force = false ) {
        self::load_media_includes();

        $count = 0;
        foreach ( $post_image_map as $post_id => $seed ) {
            if ( ! $force && has_post_thumbnail( $post_id ) ) {
                continue;
            }

            if ( $force && has_post_thumbnail( $post_id ) ) {
                $old_id = get_post_thumbnail_id( $post_id );
                if ( $old_id ) {
                    delete_post_thumbnail( $post_id );
                }
            }

            $attachment_id = self::create_and_attach( $post_id, $seed, 1200, 675 );

            if ( ! $attachment_id ) {
                $attachment_id = self::try_remote_picsum( $post_id, $seed );
            }

            if ( $attachment_id ) {
                set_post_thumbnail( $post_id, $attachment_id );
                update_post_meta( $attachment_id, '_neonews_demo_content', true );
                $count++;
            }
        }

        return $count;
    }

    /**
     * Fix all demo posts missing thumbnails.
     *
     * @return array Result stats.
     */
    public static function fix_missing_images() {
        $posts = get_posts( array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ) );

        $map = array();
        foreach ( $posts as $post_id ) {
            if ( has_post_thumbnail( $post_id ) ) {
                continue;
            }
            $title = get_the_title( $post_id );
            $map[ $post_id ] = sanitize_title( $title ? $title : 'post-' . $post_id );
        }

        $attached = self::attach_featured_images( $map, false );
        $icon     = self::create_site_icon();

        return array(
            'checked'  => count( $posts ),
            'attached' => $attached,
            'icon'     => $icon ? 1 : 0,
        );
    }

    /**
     * Create site icon if missing.
     */
    public static function create_site_icon() {
        if ( get_option( 'site_icon' ) ) {
            return (int) get_option( 'site_icon' );
        }

        self::load_media_includes();

        $file = self::generate_image_file( 'neopulse-site-icon', 512, 512, 'NeoPulse' );
        if ( ! $file ) {
            return self::try_remote_picsum( 0, 'neopulse-icon', 512, 512 );
        }

        $attachment_id = self::insert_attachment_from_file( $file, 0, 'NeoPulse Site Icon' );
        if ( $attachment_id ) {
            update_option( 'site_icon', $attachment_id );
            update_post_meta( $attachment_id, '_neonews_demo_content', true );
        }

        return $attachment_id;
    }

    /**
     * Create attachment and link to post.
     */
    public static function create_and_attach( $post_id, $seed, $width, $height ) {
        $label = self::get_label_for_post( $post_id, $seed );
        $file  = self::generate_image_file( $seed, $width, $height, $label, $post_id );

        if ( ! $file ) {
            return 0;
        }

        return self::insert_attachment_from_file( $file, $post_id, $label );
    }

    /**
     * Generate JPEG using GD (no network required).
     */
    private static function generate_image_file( $seed, $width, $height, $label = '', $post_id = 0 ) {
        if ( ! function_exists( 'imagecreatetruecolor' ) ) {
            return false;
        }

        $colors = self::get_colors_for_seed( $seed, $post_id );
        $img    = imagecreatetruecolor( $width, $height );

        if ( ! $img ) {
            return false;
        }

        self::draw_gradient( $img, $width, $height, $colors['from'], $colors['to'] );
        self::draw_decorations( $img, $width, $height, $colors['accent'] );

        if ( $label ) {
            self::draw_label( $img, $width, $height, $label );
        }

        $upload_dir = wp_upload_dir();
        if ( ! empty( $upload_dir['error'] ) ) {
            imagedestroy( $img );
            return false;
        }

        $filename = 'neonews-' . sanitize_file_name( sanitize_title( $seed ) ) . '-' . wp_generate_password( 6, false ) . '.jpg';
        $filepath = trailingslashit( $upload_dir['path'] ) . $filename;

        $saved = imagejpeg( $img, $filepath, 88 );
        imagedestroy( $img );

        return $saved ? $filepath : false;
    }

    /**
     * Draw vertical gradient.
     */
    private static function draw_gradient( $img, $width, $height, $from, $to ) {
        for ( $y = 0; $y < $height; $y++ ) {
            $ratio = $y / max( 1, $height - 1 );
            $r = (int) ( $from[0] + ( $to[0] - $from[0] ) * $ratio );
            $g = (int) ( $from[1] + ( $to[1] - $from[1] ) * $ratio );
            $b = (int) ( $from[2] + ( $to[2] - $from[2] ) * $ratio );
            $line = imagecolorallocate( $img, $r, $g, $b );
            imageline( $img, 0, $y, $width, $y, $line );
        }
    }

    /**
     * Modern abstract shapes overlay.
     */
    private static function draw_decorations( $img, $width, $height, $accent ) {
        $alpha = imagecolorallocatealpha( $img, $accent[0], $accent[1], $accent[2], 90 );
        imagefilledellipse( $img, (int) ( $width * 0.85 ), (int) ( $height * 0.15 ), (int) ( $width * 0.45 ), (int) ( $width * 0.45 ), $alpha );
        imagefilledellipse( $img, (int) ( $width * 0.1 ), (int) ( $height * 0.75 ), (int) ( $width * 0.35 ), (int) ( $width * 0.35 ), $alpha );

        $white = imagecolorallocatealpha( $img, 255, 255, 255, 110 );
        imagefilledellipse( $img, (int) ( $width * 0.55 ), (int) ( $height * 0.55 ), (int) ( $width * 0.25 ), (int) ( $width * 0.25 ), $white );
    }

    /**
     * Draw title strip at bottom.
     */
    private static function draw_label( $img, $width, $height, $label ) {
        $bar_h = (int) max( 60, $height * 0.18 );
        $dark  = imagecolorallocatealpha( $img, 15, 23, 42, 30 );
        imagefilledrectangle( $img, 0, $height - $bar_h, $width, $height, $dark );

        $text_color = imagecolorallocate( $img, 255, 255, 255 );
        $brand      = imagecolorallocate( $img, 200, 200, 255 );
        $short      = wp_html_excerpt( $label, 48, '…' );

        imagestring( $img, 5, 24, $height - $bar_h + 16, 'NEOPULSE', $brand );
        imagestring( $img, 4, 24, $height - $bar_h + 36, strtoupper( $short ), $text_color );
    }

    /**
     * Insert file into media library.
     */
    private static function insert_attachment_from_file( $file, $post_id, $title ) {
        $filetype = wp_check_filetype( basename( $file ), null );

        $attachment = array(
            'post_mime_type' => $filetype['type'] ? $filetype['type'] : 'image/jpeg',
            'post_title'     => sanitize_text_field( $title ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        $attach_id = wp_insert_attachment( $attachment, $file, $post_id );

        if ( is_wp_error( $attach_id ) || ! $attach_id ) {
            return 0;
        }

        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        return (int) $attach_id;
    }

    /**
     * Remote fallback when GD unavailable.
     */
    private static function try_remote_picsum( $post_id, $seed, $w = 1200, $h = 675 ) {
        if ( ! ini_get( 'allow_url_fopen' ) && ! function_exists( 'curl_init' ) ) {
            return 0;
        }

        self::load_media_includes();

        $url = sprintf(
            'https://picsum.photos/seed/%s/%d/%d',
            rawurlencode( sanitize_title( $seed ) ),
            absint( $w ),
            absint( $h )
        );

        $attachment_id = media_sideload_image( $url, $post_id, sanitize_text_field( $seed ), 'id' );

        if ( is_wp_error( $attachment_id ) ) {
            return 0;
        }

        return (int) $attachment_id;
    }

    /**
     * Colors from post category or seed hash.
     */
    private static function get_colors_for_seed( $seed, $post_id = 0 ) {
        $base = self::$category_colors['default'];

        if ( $post_id ) {
            $cats = get_the_category( $post_id );
            if ( ! empty( $cats ) ) {
                $slug = $cats[0]->slug;
                if ( isset( self::$category_colors[ $slug ] ) ) {
                    $base = self::$category_colors[ $slug ];
                }
            }
        }

        $hash = abs( crc32( (string) $seed ) );
        $from = $base;
        $to   = array(
            max( 0, min( 255, $base[0] - 40 + ( $hash % 30 ) ) ),
            max( 0, min( 255, $base[1] - 20 + ( ( $hash >> 4 ) % 40 ) ) ),
            max( 0, min( 255, $base[2] + ( ( $hash >> 8 ) % 50 ) ) ),
        );
        $accent = array(
            min( 255, $base[0] + 60 ),
            min( 255, $base[1] + 40 ),
            min( 255, $base[2] + 80 ),
        );

        return array(
            'from'   => $from,
            'to'     => $to,
            'accent' => $accent,
        );
    }

    /**
     * Label text for image overlay.
     */
    private static function get_label_for_post( $post_id, $seed ) {
        if ( $post_id ) {
            $title = get_the_title( $post_id );
            if ( $title ) {
                return $title;
            }
        }
        return ucwords( str_replace( '-', ' ', sanitize_title( $seed ) ) );
    }

    /**
     * Load WP media helpers.
     */
    private static function load_media_includes() {
        if ( ! function_exists( 'media_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
    }
}
