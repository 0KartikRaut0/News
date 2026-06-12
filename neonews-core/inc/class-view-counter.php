<?php
/**
 * View Counter
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * View Counter Class
 */
class NeoNews_View_Counter {

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
        if ( ! NeoNews_Core::get_settings( 'enable_view_counter' ) ) {
            return;
        }

        add_action( 'wp_head', array( $this, 'track_view' ) );
        add_action( 'wp_ajax_neonews_track_view', array( $this, 'ajax_track_view' ) );
        add_action( 'wp_ajax_nopriv_neonews_track_view', array( $this, 'ajax_track_view' ) );
    }

    /**
     * Track view on page load
     */
    public function track_view() {
        if ( ! apply_filters( 'neonews_allow_view_tracking', true ) ) {
            return;
        }

        if ( ! is_singular( 'post' ) ) {
            return;
        }

        if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
            return;
        }

        if ( $this->is_bot() ) {
            return;
        }

        $post_id = get_the_ID();
        
        if ( $this->can_count_view( $post_id ) ) {
            $this->increment_view( $post_id );
        }
    }

    /**
     * AJAX handler for view tracking
     */
    public function ajax_track_view() {
        if ( ! apply_filters( 'neonews_allow_view_tracking', true ) ) {
            wp_send_json_error();
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

        if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
            wp_send_json_error();
        }

        if ( $this->is_bot() ) {
            wp_send_json_error();
        }

        if ( $this->can_count_view( $post_id ) ) {
            $this->increment_view( $post_id );
            wp_send_json_success();
        }

        wp_send_json_error();
    }

    /**
     * Check if view can be counted (rate limiting)
     */
    private function can_count_view( $post_id ) {
        $ip_hash = $this->get_ip_hash();
        $transient_key = 'nn_view_' . $post_id . '_' . $ip_hash;

        if ( get_transient( $transient_key ) ) {
            return false;
        }

        set_transient( $transient_key, true, 60 );

        return true;
    }

    /**
     * Increment view count
     */
    private function increment_view( $post_id ) {
        $current_views = (int) get_post_meta( $post_id, '_neonews_post_views', true );
        update_post_meta( $post_id, '_neonews_post_views', $current_views + 1 );

        $today = gmdate( 'Y-m-d' );
        $daily_views = get_post_meta( $post_id, '_neonews_daily_views', true );
        
        if ( ! is_array( $daily_views ) ) {
            $daily_views = array();
        }

        if ( ! isset( $daily_views[ $today ] ) ) {
            $daily_views[ $today ] = 0;
        }
        $daily_views[ $today ]++;

        $cutoff_date = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
        foreach ( $daily_views as $date => $count ) {
            if ( $date < $cutoff_date ) {
                unset( $daily_views[ $date ] );
            }
        }

        update_post_meta( $post_id, '_neonews_daily_views', $daily_views );
    }

    /**
     * Get IP hash (privacy-friendly)
     */
    private function get_ip_hash() {
        $ip = '';
        
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
            $ip = explode( ',', $ip )[0];
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }

        return md5( $ip . gmdate( 'Y-m-d' ) );
    }

    /**
     * Detect bots
     */
    private function is_bot() {
        if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
            return true;
        }

        $user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
        
        $bot_patterns = array(
            'bot',
            'crawl',
            'spider',
            'slurp',
            'googlebot',
            'bingbot',
            'yandex',
            'baidu',
            'duckduckbot',
            'facebookexternalhit',
            'twitterbot',
            'linkedinbot',
            'pinterest',
            'semrush',
            'ahrefs',
            'mj12bot',
            'dotbot',
            'petalbot',
            'bytespider',
        );

        $user_agent_lower = strtolower( $user_agent );
        
        foreach ( $bot_patterns as $pattern ) {
            if ( strpos( $user_agent_lower, $pattern ) !== false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get post view count
     */
    public static function get_views( $post_id ) {
        return (int) get_post_meta( $post_id, '_neonews_post_views', true );
    }

    /**
     * Get views for a specific period
     */
    public static function get_views_for_period( $post_id, $days = 7 ) {
        $daily_views = get_post_meta( $post_id, '_neonews_daily_views', true );
        
        if ( ! is_array( $daily_views ) ) {
            return 0;
        }

        $total = 0;
        $cutoff_date = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );

        foreach ( $daily_views as $date => $count ) {
            if ( $date >= $cutoff_date ) {
                $total += (int) $count;
            }
        }

        return $total;
    }

    /**
     * Get trending posts
     */
    public static function get_trending( $limit = 5, $days = 7 ) {
        global $wpdb;

        $posts = get_posts( array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $limit * 3,
            'date_query'     => array(
                array(
                    'after' => "{$days} days ago",
                ),
            ),
            'meta_key'       => '_neonews_post_views',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        ) );

        if ( empty( $posts ) ) {
            return array();
        }

        $trending = array();
        foreach ( $posts as $post ) {
            $period_views = self::get_views_for_period( $post->ID, $days );
            $trending[ $post->ID ] = $period_views;
        }

        arsort( $trending );
        $trending = array_slice( $trending, 0, $limit, true );

        $result = array();
        foreach ( array_keys( $trending ) as $post_id ) {
            $result[] = get_post( $post_id );
        }

        return $result;
    }

    /**
     * Format view count for display
     */
    public static function format_views( $views ) {
        $views = absint( $views );
        
        if ( $views >= 1000000 ) {
            return round( $views / 1000000, 1 ) . 'M';
        } elseif ( $views >= 1000 ) {
            return round( $views / 1000, 1 ) . 'K';
        }
        
        return number_format_i18n( $views );
    }
}
