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

        // Server-side fallback (works without JS / for non-cached pages).
        add_action( 'wp_head', array( $this, 'track_view' ) );
        // AJAX endpoint — called by JS so it works even on fully cached pages.
        add_action( 'wp_ajax_neonews_track_view', array( $this, 'ajax_track_view' ) );
        add_action( 'wp_ajax_nopriv_neonews_track_view', array( $this, 'ajax_track_view' ) );
        // Inject post ID into the page for JS to use.
        add_action( 'wp_footer', array( $this, 'inject_view_script' ), 5 );
    }

    /**
     * Inject a small inline script in wp_footer so JS can fire the AJAX view ping.
     * This runs after the page is served — works correctly even with page caching.
     */
    public function inject_view_script() {
        if ( ! is_singular( 'post' ) ) {
            return;
        }
        if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
            return;
        }
        if ( $this->is_bot() ) {
            return;
        }

        $post_id   = get_the_ID();
        $ajax_url  = admin_url( 'admin-ajax.php' );
        $nonce     = wp_create_nonce( 'neonews_view_' . $post_id );
        $store_key = 'nn_view_' . $post_id;
        ?>
        <script>
        (function(){
            var key = <?php echo wp_json_encode( $store_key ); ?>;
            var postId = <?php echo (int) $post_id; ?>;
            var ajaxUrl = <?php echo wp_json_encode( $ajax_url ); ?>;
            var nonce = <?php echo wp_json_encode( $nonce ); ?>;

            // Client-side guard: localStorage stores timestamp of last view ping.
            // Only fire AJAX if more than 24 hours have passed.
            try {
                var last = parseInt( localStorage.getItem( key ) || '0', 10 );
                var now  = Math.floor( Date.now() / 1000 );
                if ( last && ( now - last ) < 86400 ) {
                    return; // already counted within 24 h on this device
                }
                localStorage.setItem( key, String( now ) );
            } catch(e) {}

            // Fire AJAX ping — server does a second IP-based check before incrementing.
            var fd = new FormData();
            fd.append( 'action',  'neonews_track_view' );
            fd.append( 'nonce',   nonce );
            fd.append( 'post_id', postId );
            fetch( ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' } ).catch(function(){});
        })();
        </script>
        <?php
    }

    /**
     * Server-side fallback: track view on wp_head (no-JS / non-cached pages).
     * The AJAX path (inject_view_script) is preferred; this is a belt-and-suspenders backup.
     */
    public function track_view() {
        // Skip — JS AJAX path handles it to avoid double-counting on non-cached pages.
        // Kept as hook registration is still in __construct for legacy compatibility.
    }

    /**
     * AJAX handler for view tracking — called by inject_view_script JS.
     */
    public function ajax_track_view() {
        if ( ! apply_filters( 'neonews_allow_view_tracking', true ) ) {
            wp_send_json_error();
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
            wp_send_json_error();
        }

        // Verify nonce (post-specific so it can't be replayed across posts).
        if ( ! check_ajax_referer( 'neonews_view_' . $post_id, 'nonce', false ) ) {
            wp_send_json_error( array( 'reason' => 'nonce' ) );
        }

        if ( $this->is_bot() ) {
            wp_send_json_error();
        }

        if ( $this->can_count_view( $post_id ) ) {
            $this->increment_view( $post_id );
            wp_send_json_success( array( 'counted' => true ) );
        }

        wp_send_json_success( array( 'counted' => false ) ); // duplicate, not an error
    }

    /**
     * Check if view can be counted.
     *
     * Logged-in users  → stored in user meta; counted only once ever per post.
     * Guest users      → IP-based transient with a 24-hour cooldown.
     */
    private function can_count_view( $post_id ) {
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $viewed  = get_user_meta( $user_id, '_neonews_viewed_posts', true );
            if ( ! is_array( $viewed ) ) {
                $viewed = array();
            }
            if ( in_array( (int) $post_id, $viewed, true ) ) {
                return false; // already counted for this user.
            }
            $viewed[] = (int) $post_id;
            update_user_meta( $user_id, '_neonews_viewed_posts', $viewed );
            return true;
        }

        // Guest: rolling 24-hour cooldown per IP per post.
        // Hash is IP + secret salt (no date component) so the key is stable;
        // the transient TTL of DAY_IN_SECONDS enforces the rolling 24 h window.
        $ip_hash       = $this->get_ip_hash();
        $transient_key = 'nn_vw_' . $post_id . '_' . $ip_hash; // max 172 chars — safe for WP transient key
        if ( get_transient( $transient_key ) ) {
            return false;
        }
        set_transient( $transient_key, 1, DAY_IN_SECONDS );
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
            $ip = trim( explode( ',', $ip )[0] );
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }

        // Use WordPress auth salt so the hash is site-specific and non-reversible.
        // No date component — the transient TTL handles the rolling 24 h window.
        return substr( hash( 'sha256', $ip . wp_salt( 'auth' ) ), 0, 16 );
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

        $total       = 0;
        $cutoff_date = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );

        foreach ( $daily_views as $date => $count ) {
            if ( $date >= $cutoff_date ) {
                $total += (int) $count;
            }
        }

        return $total;
    }

    /**
     * Views between two Y-m-d dates (inclusive), from daily breakdown meta.
     *
     * @param int    $post_id Post ID.
     * @param string $from    Start date Y-m-d.
     * @param string $to      End date Y-m-d.
     * @return int
     */
    public static function get_views_for_date_range( $post_id, $from, $to ) {
        $daily_views = get_post_meta( $post_id, '_neonews_daily_views', true );

        if ( ! is_array( $daily_views ) ) {
            return 0;
        }

        $total = 0;
        foreach ( $daily_views as $date => $count ) {
            if ( $date >= $from && $date <= $to ) {
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
