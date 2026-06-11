<?php
/**
 * Auto excerpt on save (server-local).
 *
 * @package NeoNews_Smart
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Smart_Excerpt {

    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        add_action( 'save_post', array( $this, 'maybe_auto_excerpt' ), 25, 2 );
    }

    /**
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post.
     */
    public function maybe_auto_excerpt( $post_id, $post ) {
        if ( ! NeoNews_Smart::is_enabled( 'auto_excerpt' ) ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) || 'post' !== $post->post_type ) {
            return;
        }

        if ( ! in_array( $post->post_status, array( 'publish', 'draft', 'pending', 'future' ), true ) ) {
            return;
        }

        if ( get_post_field( 'post_excerpt', $post_id ) ) {
            return;
        }

        $max     = absint( NeoNews_Smart::get_settings( 'excerpt_max_sentences' ) );
        $excerpt = NeoNews_Smart_Helpers::build_excerpt_from_content( $post->post_content, $max );

        if ( ! $excerpt ) {
            return;
        }

        remove_action( 'save_post', array( $this, 'maybe_auto_excerpt' ), 25 );
        wp_update_post(
            array(
                'ID'           => $post_id,
                'post_excerpt' => $excerpt,
            )
        );
        add_action( 'save_post', array( $this, 'maybe_auto_excerpt' ), 25, 2 );

        update_post_meta( $post_id, '_neonews_smart_glance', sanitize_textarea_field( $excerpt ) );
    }
}
