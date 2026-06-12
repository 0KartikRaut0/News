<?php
/**
 * Premium Content Handler
 *
 * @package NeoNews_Membership
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Premium Content Class
 */
class NeoNews_Premium_Content {

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
        add_shortcode( 'premium_content', array( $this, 'premium_content_shortcode' ) );
        add_shortcode( 'free_content', array( $this, 'free_content_shortcode' ) );
        add_shortcode( 'membership_status', array( $this, 'membership_status_shortcode' ) );
    }

    /**
     * Premium content shortcode
     * [premium_content]Content only for premium users[/premium_content]
     */
    public function premium_content_shortcode( $atts, $content = null ) {
        $atts = shortcode_atts( array(
            'message'       => '',
            'show_excerpt'  => 'true',
            'excerpt_length' => 0,
        ), $atts, 'premium_content' );

        if ( empty( $content ) ) {
            return '';
        }

        if ( NeoNews_Membership_Levels::is_premium() ) {
            return do_shortcode( $content );
        }

        $default_message = sprintf(
            /* translators: %s: upgrade link */
            __( 'This content is for premium members only. %s to access.', 'neonews-membership' ),
            '<a href="' . esc_url( $this->get_upgrade_url() ) . '">' . esc_html__( 'Upgrade your account', 'neonews-membership' ) . '</a>'
        );

        $message = ! empty( $atts['message'] ) ? $atts['message'] : $default_message;

        $output = '<div class="nn-premium-notice">';
        $output .= '<div class="nn-premium-icon">🔒</div>';
        $output .= '<h3>' . esc_html__( 'Premium Content', 'neonews-membership' ) . '</h3>';
        $output .= '<p>' . wp_kses_post( $message ) . '</p>';

        if ( 'true' === $atts['show_excerpt'] ) {
            $excerpt_length = absint( $atts['excerpt_length'] );
            if ( ! $excerpt_length ) {
                $excerpt_length = NeoNews_Membership::get_settings( 'premium_excerpt_length' );
            }
            
            $excerpt = wp_strip_all_tags( $content );
            if ( strlen( $excerpt ) > $excerpt_length ) {
                $excerpt = substr( $excerpt, 0, $excerpt_length ) . '...';
            }
            
            if ( ! empty( $excerpt ) ) {
                $output .= '<div class="nn-premium-excerpt">' . esc_html( $excerpt ) . '</div>';
            }
        }

        if ( ! is_user_logged_in() ) {
            $output .= '<div class="nn-premium-actions">';
            $output .= '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '" class="nn-btn nn-btn-outline">' . esc_html__( 'Log In', 'neonews-membership' ) . '</a>';
            $output .= '<a href="' . esc_url( wp_registration_url() ) . '" class="nn-btn">' . esc_html__( 'Sign Up', 'neonews-membership' ) . '</a>';
            $output .= '</div>';
        } else {
            $output .= '<div class="nn-premium-actions">';
            $output .= '<a href="' . esc_url( $this->get_upgrade_url() ) . '" class="nn-btn">' . esc_html__( 'Upgrade to Premium', 'neonews-membership' ) . '</a>';
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Free content shortcode (shown to everyone)
     * [free_content]Content for all users[/free_content]
     */
    public function free_content_shortcode( $atts, $content = null ) {
        return do_shortcode( $content );
    }

    /**
     * Membership status shortcode
     * [membership_status]
     */
    public function membership_status_shortcode( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<div class="nn-membership-status nn-status-guest">' . 
                   esc_html__( 'You are not logged in.', 'neonews-membership' ) . 
                   ' <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . esc_html__( 'Log In', 'neonews-membership' ) . '</a>' .
                   '</div>';
        }

        $user_id = get_current_user_id();
        $is_premium = NeoNews_Membership_Levels::is_premium( $user_id );
        $expiry = NeoNews_Membership_Levels::get_premium_expiry( $user_id );

        $output = '<div class="nn-membership-status nn-status-' . ( $is_premium ? 'premium' : 'free' ) . '">';
        
        if ( $is_premium ) {
            $output .= '<span class="nn-status-badge nn-badge-premium">✓ ' . esc_html__( 'Premium Member', 'neonews-membership' ) . '</span>';
            
            if ( $expiry ) {
                $output .= '<span class="nn-status-expiry">';
                $output .= sprintf(
                    /* translators: %s: expiry date */
                    esc_html__( 'Valid until: %s', 'neonews-membership' ),
                    esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expiry ) ) )
                );
                $output .= '</span>';
            }
        } else {
            $output .= '<span class="nn-status-badge nn-badge-free">' . esc_html__( 'Free Member', 'neonews-membership' ) . '</span>';
            $output .= ' <a href="' . esc_url( $this->get_upgrade_url() ) . '" class="nn-upgrade-link">' . esc_html__( 'Upgrade to Premium', 'neonews-membership' ) . '</a>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Get upgrade URL
     */
    private function get_upgrade_url() {
        $upgrade_page_id = get_option( 'neonews_upgrade_page_id' );
        
        if ( $upgrade_page_id ) {
            return get_permalink( $upgrade_page_id );
        }

        return home_url( '/membership/' );
    }

    /**
     * Check if post is premium
     */
    public static function is_premium_post( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        return '1' === get_post_meta( $post_id, '_neonews_premium_post', true );
    }
}
