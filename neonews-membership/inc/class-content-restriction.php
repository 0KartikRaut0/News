<?php
/**
 * Content Restriction Handler
 *
 * @package NeoNews_Membership
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Content Restriction Class
 */
class NeoNews_Content_Restriction {

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
        if ( ! NeoNews_Membership::get_settings( 'enable_paywall' ) ) {
            return;
        }

        add_filter( 'the_content', array( $this, 'maybe_restrict_content' ), 99 );
        add_action( 'template_redirect', array( $this, 'track_article_read' ) );
    }

    /**
     * Maybe restrict content based on paywall rules
     */
    public function maybe_restrict_content( $content ) {
        if ( ! is_singular( 'post' ) ) {
            return $content;
        }

        if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
            return $content;
        }

        $post_id = get_the_ID();

        if ( ! NeoNews_Premium_Content::is_premium_post( $post_id ) ) {
            return $content;
        }

        if ( NeoNews_Membership_Levels::is_premium() ) {
            return $content;
        }

        if ( NeoNews_Membership::get_settings( 'enable_blur_lock' ) ) {
            return $this->get_blur_lock_content( $content, $post_id );
        }

        if ( $this->user_has_free_articles_remaining() ) {
            $this->increment_article_count();
            return $content;
        }

        return $this->get_restricted_content( $content, $post_id );
    }

    /**
     * Blurred lock preview for non-premium users.
     *
     * @param string $content Full content.
     * @param int    $post_id Post ID.
     * @return string
     */
    private function get_blur_lock_content( $content, $post_id ) {
        $settings = NeoNews_Membership::get_settings();
        $blur     = max( 2, min( 16, absint( $settings['lock_blur_strength'] ) ) );
        $excerpt  = wp_trim_words( wp_strip_all_tags( $content ), 40, '…' );

        ob_start();
        ?>
        <div class="nn-premium-lock-wrap">
            <div class="nn-premium-lock-preview">
                <div class="nn-premium-lock-blur" style="--nn-lock-blur: <?php echo esc_attr( $blur ); ?>px;">
                    <?php echo wp_kses_post( wpautop( $excerpt ) ); ?>
                    <div class="nn-premium-lock-fade"></div>
                </div>
                <div class="nn-premium-lock-overlay-panel">
                    <span class="nn-lock-icon" aria-hidden="true">🔒</span>
                    <h3><?php esc_html_e( 'Exclusive Premium Story', 'neonews-membership' ); ?></h3>
                    <p><?php esc_html_e( 'Become a premium member to read the full article without interruptions.', 'neonews-membership' ); ?></p>
                    <div class="nn-paywall-actions">
                        <?php if ( ! is_user_logged_in() ) : ?>
                            <a href="<?php echo esc_url( wp_login_url( get_permalink( $post_id ) ) ); ?>" class="nn-btn nn-btn-outline"><?php esc_html_e( 'Log In', 'neonews-membership' ); ?></a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( NeoNews_Membership_Frontend::get_enroll_url() ); ?>" class="nn-btn"><?php esc_html_e( 'Enroll to Premium', 'neonews-membership' ); ?></a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Check if user has free articles remaining
     */
    private function user_has_free_articles_remaining() {
        $limit = NeoNews_Membership::get_settings( 'free_article_limit' );
        
        if ( $limit <= 0 ) {
            return false;
        }

        $count = $this->get_user_article_count();
        
        return $count < $limit;
    }

    /**
     * Get user's article read count for current period
     */
    private function get_user_article_count() {
        $period = NeoNews_Membership::get_settings( 'free_article_period' );
        $cookie_name = 'nn_articles_read';

        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $period_key = $this->get_period_key( $period );
            $meta_key = '_neonews_articles_read_' . $period_key;
            
            return (int) get_user_meta( $user_id, $meta_key, true );
        }

        if ( isset( $_COOKIE[ $cookie_name ] ) ) {
            $cookie_data = json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) ), true );
            $period_key = $this->get_period_key( $period );
            
            if ( isset( $cookie_data[ $period_key ] ) ) {
                return (int) $cookie_data[ $period_key ];
            }
        }

        return 0;
    }

    /**
     * Increment article count
     */
    private function increment_article_count() {
        $period = NeoNews_Membership::get_settings( 'free_article_period' );
        $period_key = $this->get_period_key( $period );

        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $meta_key = '_neonews_articles_read_' . $period_key;
            $current = (int) get_user_meta( $user_id, $meta_key, true );
            update_user_meta( $user_id, $meta_key, $current + 1 );
        } else {
            $cookie_name = 'nn_articles_read';
            $cookie_data = array();
            
            if ( isset( $_COOKIE[ $cookie_name ] ) ) {
                $cookie_data = json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) ), true );
                if ( ! is_array( $cookie_data ) ) {
                    $cookie_data = array();
                }
            }

            if ( ! isset( $cookie_data[ $period_key ] ) ) {
                $cookie_data[ $period_key ] = 0;
            }
            
            $cookie_data[ $period_key ]++;

            $expiry = $this->get_period_expiry( $period );
            setcookie( $cookie_name, wp_json_encode( $cookie_data ), $expiry, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
        }
    }

    /**
     * Get period key
     */
    private function get_period_key( $period ) {
        switch ( $period ) {
            case 'day':
                return gmdate( 'Y-m-d' );
            case 'week':
                return gmdate( 'Y-W' );
            case 'month':
            default:
                return gmdate( 'Y-m' );
        }
    }

    /**
     * Get period expiry timestamp
     */
    private function get_period_expiry( $period ) {
        switch ( $period ) {
            case 'day':
                return strtotime( 'tomorrow' );
            case 'week':
                return strtotime( 'next monday' );
            case 'month':
            default:
                return strtotime( 'first day of next month' );
        }
    }

    /**
     * Get restricted content HTML
     */
    private function get_restricted_content( $content, $post_id ) {
        if ( NeoNews_Membership::get_settings( 'enable_blur_lock' ) ) {
            return $this->get_blur_lock_content( $content, $post_id );
        }

        $excerpt_length = NeoNews_Membership::get_settings( 'premium_excerpt_length' );
        $excerpt = wp_trim_words( wp_strip_all_tags( $content ), $excerpt_length / 5, '' );

        $limit = NeoNews_Membership::get_settings( 'free_article_limit' );
        $period = NeoNews_Membership::get_settings( 'free_article_period' );
        
        $period_labels = array(
            'day'   => __( 'day', 'neonews-membership' ),
            'week'  => __( 'week', 'neonews-membership' ),
            'month' => __( 'month', 'neonews-membership' ),
        );

        ob_start();
        ?>
        <div class="nn-content-excerpt">
            <?php echo wp_kses_post( wpautop( $excerpt ) ); ?>
        </div>

        <div class="nn-paywall">
            <div class="nn-paywall-inner">
                <div class="nn-paywall-icon">📰</div>
                <h3><?php esc_html_e( "You've reached your free article limit", 'neonews-membership' ); ?></h3>
                <p>
                    <?php
                    printf(
                        /* translators: 1: number of free articles, 2: period (day/week/month) */
                        esc_html__( 'Free readers can access %1$d articles per %2$s. Subscribe to get unlimited access to all our premium content.', 'neonews-membership' ),
                        absint( $limit ),
                        esc_html( $period_labels[ $period ] ?? $period )
                    );
                    ?>
                </p>

                <div class="nn-paywall-benefits">
                    <h4><?php esc_html_e( 'Premium Benefits:', 'neonews-membership' ); ?></h4>
                    <ul>
                        <li><?php esc_html_e( 'Unlimited access to all articles', 'neonews-membership' ); ?></li>
                        <li><?php esc_html_e( 'Ad-free reading experience', 'neonews-membership' ); ?></li>
                        <li><?php esc_html_e( 'Exclusive premium content', 'neonews-membership' ); ?></li>
                        <li><?php esc_html_e( 'Support independent journalism', 'neonews-membership' ); ?></li>
                    </ul>
                </div>

                <div class="nn-paywall-actions">
                    <?php if ( ! is_user_logged_in() ) : ?>
                        <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="nn-btn nn-btn-outline">
                            <?php esc_html_e( 'Log In', 'neonews-membership' ); ?>
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( $this->get_subscribe_url() ); ?>" class="nn-btn">
                        <?php esc_html_e( 'Subscribe Now', 'neonews-membership' ); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Track article read
     */
    public function track_article_read() {
        if ( ! is_singular( 'post' ) ) {
            return;
        }

        $post_id = get_the_ID();
        
        if ( ! NeoNews_Premium_Content::is_premium_post( $post_id ) ) {
            return;
        }
    }

    /**
     * Get subscribe URL
     */
    private function get_subscribe_url() {
        $page_id = get_option( 'neonews_subscribe_page_id' );
        
        if ( $page_id ) {
            return get_permalink( $page_id );
        }

        return home_url( '/subscribe/' );
    }
}
