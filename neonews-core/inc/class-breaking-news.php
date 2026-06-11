<?php
/**
 * Breaking News Handler
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Breaking News Class
 */
class NeoNews_Breaking_News {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action( 'save_post', array( $this, 'handle_breaking_news_save' ), 10, 2 );
        add_action( 'neonews_expire_breaking_news', array( $this, 'expire_breaking_news' ) );
    }

    /**
     * Handle breaking news on post save
     */
    public function handle_breaking_news_save( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( 'post' !== $post->post_type ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( ! isset( $_POST['neonews_post_meta_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['neonews_post_meta_nonce'] ) ), 'neonews_save_post_meta' ) ) {
            return;
        }

        $is_breaking = isset( $_POST['_neonews_breaking_news'] ) ? '1' : '';
        $was_breaking = get_post_meta( $post_id, '_neonews_breaking_news', true );

        update_post_meta( $post_id, '_neonews_breaking_news', $is_breaking );

        if ( $is_breaking && ! $was_breaking && 'publish' === $post->post_status ) {
            update_post_meta( $post_id, '_neonews_breaking_news_time', current_time( 'mysql' ) );
            
            $expiry_hours = apply_filters( 'neonews_breaking_news_expiry', 24 );
            $expiry_time = time() + ( $expiry_hours * HOUR_IN_SECONDS );
            
            wp_schedule_single_event( $expiry_time, 'neonews_expire_breaking_news', array( $post_id ) );

            do_action( 'neonews_post_marked_breaking', $post_id, $post );
        }
    }

    /**
     * Expire breaking news
     */
    public function expire_breaking_news( $post_id ) {
        delete_post_meta( $post_id, '_neonews_breaking_news' );
        delete_post_meta( $post_id, '_neonews_breaking_news_time' );
    }

    /**
     * Get breaking news posts
     */
    public static function get_breaking_news( $limit = 5 ) {
        $args = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => absint( $limit ),
            'meta_query'     => array(
                array(
                    'key'     => '_neonews_breaking_news',
                    'value'   => '1',
                    'compare' => '=',
                ),
            ),
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        );

        return new WP_Query( $args );
    }

    /**
     * Check if post is breaking news
     */
    public static function is_breaking_news( $post_id ) {
        return '1' === get_post_meta( $post_id, '_neonews_breaking_news', true );
    }

    /**
     * Mark post as breaking news
     */
    public static function mark_as_breaking( $post_id ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return false;
        }

        update_post_meta( $post_id, '_neonews_breaking_news', '1' );
        update_post_meta( $post_id, '_neonews_breaking_news_time', current_time( 'mysql' ) );

        $expiry_hours = apply_filters( 'neonews_breaking_news_expiry', 24 );
        $expiry_time = time() + ( $expiry_hours * HOUR_IN_SECONDS );
        
        wp_schedule_single_event( $expiry_time, 'neonews_expire_breaking_news', array( $post_id ) );

        return true;
    }

    /**
     * Remove breaking news status
     */
    public static function remove_breaking( $post_id ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return false;
        }

        delete_post_meta( $post_id, '_neonews_breaking_news' );
        delete_post_meta( $post_id, '_neonews_breaking_news_time' );

        wp_clear_scheduled_hook( 'neonews_expire_breaking_news', array( $post_id ) );

        return true;
    }
}
