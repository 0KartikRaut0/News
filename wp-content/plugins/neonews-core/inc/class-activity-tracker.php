<?php
/**
 * User Activity Tracker
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Activity Tracker Class
 */
class NeoNews_Activity_Tracker {

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
        if ( ! NeoNews_Core::get_settings( 'enable_activity_tracking' ) ) {
            return;
        }

        add_action( 'wp_login', array( $this, 'track_login' ), 10, 2 );
        add_action( 'template_redirect', array( $this, 'track_post_read' ) );
        add_action( 'wp_ajax_neonews_track_read', array( $this, 'ajax_track_read' ) );
    }

    /**
     * Track user login
     */
    public function track_login( $user_login, $user ) {
        update_user_meta( $user->ID, '_neonews_last_login', current_time( 'mysql' ) );
        
        $login_count = (int) get_user_meta( $user->ID, '_neonews_login_count', true );
        update_user_meta( $user->ID, '_neonews_login_count', $login_count + 1 );
    }

    /**
     * Track post read on page load
     */
    public function track_post_read() {
        if ( ! is_singular( 'post' ) || ! is_user_logged_in() ) {
            return;
        }

        $post_id = get_the_ID();
        $user_id = get_current_user_id();

        $this->record_post_read( $user_id, $post_id );
    }

    /**
     * AJAX handler for tracking reads
     */
    public function ajax_track_read() {
        check_ajax_referer( 'neonews_core_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error();
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

        if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
            wp_send_json_error();
        }

        $this->record_post_read( get_current_user_id(), $post_id );

        wp_send_json_success();
    }

    /**
     * Record a post read
     */
    private function record_post_read( $user_id, $post_id ) {
        $transient_key = 'nn_read_' . $user_id . '_' . $post_id;
        
        if ( get_transient( $transient_key ) ) {
            return;
        }

        set_transient( $transient_key, true, 5 * MINUTE_IN_SECONDS );

        $posts_read = get_user_meta( $user_id, '_neonews_posts_read', true );
        
        if ( ! is_array( $posts_read ) ) {
            $posts_read = array();
        }

        if ( ! in_array( $post_id, $posts_read, true ) ) {
            $posts_read[] = $post_id;
            
            if ( count( $posts_read ) > 1000 ) {
                $posts_read = array_slice( $posts_read, -1000 );
            }
            
            update_user_meta( $user_id, '_neonews_posts_read', $posts_read );
        }

        $total_read_count = (int) get_user_meta( $user_id, '_neonews_total_posts_read', true );
        update_user_meta( $user_id, '_neonews_total_posts_read', $total_read_count + 1 );

        update_user_meta( $user_id, '_neonews_last_read_post', $post_id );
        update_user_meta( $user_id, '_neonews_last_read_time', current_time( 'mysql' ) );

        if ( function_exists( 'neonews_log_user_activity' ) ) {
            neonews_log_user_activity(
                'post_read',
                sprintf(
                    /* translators: %s: post title */
                    __( 'Read: %s', 'neonews-core' ),
                    get_the_title( $post_id )
                ),
                $post_id,
                array(),
                $user_id
            );
        }
    }

    /**
     * Get user's last login time
     */
    public static function get_last_login( $user_id ) {
        return get_user_meta( $user_id, '_neonews_last_login', true );
    }

    /**
     * Get user's login count
     */
    public static function get_login_count( $user_id ) {
        return (int) get_user_meta( $user_id, '_neonews_login_count', true );
    }

    /**
     * Get user's total posts read count
     */
    public static function get_posts_read_count( $user_id ) {
        return (int) get_user_meta( $user_id, '_neonews_total_posts_read', true );
    }

    /**
     * Get user's read posts list
     */
    public static function get_posts_read( $user_id, $limit = 10 ) {
        $posts_read = get_user_meta( $user_id, '_neonews_posts_read', true );
        
        if ( ! is_array( $posts_read ) ) {
            return array();
        }

        $posts_read = array_reverse( $posts_read );

        if ( $limit > 0 ) {
            $posts_read = array_slice( $posts_read, 0, $limit );
        }

        return $posts_read;
    }

    /**
     * Check if user has read a specific post
     */
    public static function has_read_post( $user_id, $post_id ) {
        $posts_read = get_user_meta( $user_id, '_neonews_posts_read', true );
        
        if ( ! is_array( $posts_read ) ) {
            return false;
        }

        return in_array( $post_id, $posts_read, true );
    }

    /**
     * Get user activity summary
     */
    public static function get_activity_summary( $user_id ) {
        return array(
            'last_login'       => self::get_last_login( $user_id ),
            'login_count'      => self::get_login_count( $user_id ),
            'posts_read_count' => self::get_posts_read_count( $user_id ),
            'last_read_post'   => get_user_meta( $user_id, '_neonews_last_read_post', true ),
            'last_read_time'   => get_user_meta( $user_id, '_neonews_last_read_time', true ),
        );
    }
}
