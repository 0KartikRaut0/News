<?php
/**
 * AJAX handlers — subscribe, confirm, unsubscribe.
 *
 * @package NeoNews_Newsletter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Newsletter_Ajax {

    public static function init() {
        // Subscribe form submission.
        add_action( 'wp_ajax_nn_subscribe',        array( __CLASS__, 'handle_subscribe' ) );
        add_action( 'wp_ajax_nopriv_nn_subscribe', array( __CLASS__, 'handle_subscribe' ) );

        // Confirmation & unsubscribe via URL query params (no AJAX needed).
        add_action( 'template_redirect', array( __CLASS__, 'handle_url_actions' ) );

        // Inject nonce into page for JS.
        add_action( 'wp_footer', array( __CLASS__, 'inject_nonce' ), 5 );
    }

    /**
     * Handle subscribe AJAX request.
     */
    public static function handle_subscribe() {
        // Soft nonce check — return fresh nonce if expired.
        if ( ! check_ajax_referer( 'nn_subscribe_nonce', 'nonce', false ) ) {
            wp_send_json_error( array(
                'message'     => __( 'Session expired. Please try again.', 'neonews-newsletter' ),
                'fresh_nonce' => wp_create_nonce( 'nn_subscribe_nonce' ),
                'code'        => 'nonce_fail',
            ) );
        }

        $email      = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
        $first_name = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
        $ip         = self::get_ip();

        if ( ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'neonews-newsletter' ) ) );
        }

        // Rate limit: max 3 attempts per IP per hour.
        $rate_key = 'nn_sub_rate_' . md5( $ip );
        $attempts = (int) get_transient( $rate_key );
        if ( $attempts >= 3 ) {
            wp_send_json_error( array( 'message' => __( 'Too many attempts. Please try again later.', 'neonews-newsletter' ) ) );
        }
        set_transient( $rate_key, $attempts + 1, HOUR_IN_SECONDS );

        $existing = NeoNews_Newsletter_DB::get_by_email( $email );

        if ( $existing ) {
            if ( 'confirmed' === $existing->status ) {
                // Already confirmed — just say thanks, don't expose status.
                wp_send_json_success( array( 'message' => __( "You're already subscribed! Check your inbox for our daily briefing.", 'neonews-newsletter' ) ) );
            }
            if ( 'unsubscribed' === $existing->status ) {
                // Allow re-subscribe: reset to pending with new token.
                global $wpdb;
                $token = bin2hex( random_bytes( 32 ) );
                $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                    NeoNews_Newsletter_DB::table(),
                    array(
                        'status'        => 'pending',
                        'confirm_token' => $token,
                        'subscribed_at' => current_time( 'mysql' ),
                        'confirmed_at'  => null,
                    ),
                    array( 'id' => $existing->id ),
                    array( '%s', '%s', '%s', null ),
                    array( '%d' )
                );
                NeoNews_Newsletter_Mailer::send_confirmation( $email, $first_name, $token );
                wp_send_json_success( array( 'message' => __( "We've sent you a confirmation email. Please check your inbox!", 'neonews-newsletter' ) ) );
            }
            // Pending — resend confirmation.
            NeoNews_Newsletter_Mailer::send_confirmation( $email, $first_name, $existing->confirm_token );
            wp_send_json_success( array( 'message' => __( "We've resent your confirmation email. Please check your inbox!", 'neonews-newsletter' ) ) );
        }

        $id = NeoNews_Newsletter_DB::add_subscriber( $email, $first_name, $ip );

        if ( ! $id ) {
            wp_send_json_error( array( 'message' => __( 'Something went wrong. Please try again.', 'neonews-newsletter' ) ) );
        }

        $subscriber = NeoNews_Newsletter_DB::get_by_email( $email );
        NeoNews_Newsletter_Mailer::send_confirmation( $email, $first_name, $subscriber->confirm_token );

        wp_send_json_success( array(
            'message' => __( "Almost there! We've sent a confirmation email — please check your inbox and click the link.", 'neonews-newsletter' ),
        ) );
    }

    /**
     * Handle ?nn_confirm=TOKEN and ?nn_unsub=TOKEN in URL.
     */
    public static function handle_url_actions() {
        // Confirmation.
        if ( isset( $_GET['nn_confirm'] ) ) {
            $token   = sanitize_text_field( wp_unslash( $_GET['nn_confirm'] ) );
            $success = NeoNews_Newsletter_DB::confirm_by_token( $token );

            if ( $success ) {
                // Get subscriber details for welcome email.
                global $wpdb;
                $row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                    $wpdb->prepare(
                        "SELECT email, first_name, confirm_token FROM %i WHERE confirm_token = %s LIMIT 1",
                        NeoNews_Newsletter_DB::table(),
                        $token
                    )
                );
                if ( $row ) {
                    NeoNews_Newsletter_Mailer::send_welcome( $row->email, $row->first_name, $row->confirm_token );
                }
                wp_safe_redirect( add_query_arg( 'nn_status', 'confirmed', home_url( '/' ) ) );
            } else {
                wp_safe_redirect( add_query_arg( 'nn_status', 'already_confirmed', home_url( '/' ) ) );
            }
            exit;
        }

        // Unsubscribe.
        if ( isset( $_GET['nn_unsub'] ) ) {
            $token = sanitize_text_field( wp_unslash( $_GET['nn_unsub'] ) );
            NeoNews_Newsletter_DB::unsubscribe( $token );
            wp_safe_redirect( add_query_arg( 'nn_status', 'unsubscribed', home_url( '/' ) ) );
            exit;
        }

        // Show toast notification on homepage after redirect.
        if ( isset( $_GET['nn_status'] ) && is_front_page() ) {
            add_action( 'wp_footer', array( __CLASS__, 'render_status_toast' ) );
        }
    }

    /**
     * Render a toast notification based on nn_status param.
     */
    public static function render_status_toast() {
        $status = sanitize_key( $_GET['nn_status'] ?? '' );
        $messages = array(
            'confirmed'        => array( 'type' => 'success', 'text' => __( "🎉 You're confirmed! Welcome to our daily briefing.", 'neonews-newsletter' ) ),
            'already_confirmed'=> array( 'type' => 'info',    'text' => __( 'Your subscription is already active.', 'neonews-newsletter' ) ),
            'unsubscribed'     => array( 'type' => 'info',    'text' => __( "You've been unsubscribed. Sorry to see you go!", 'neonews-newsletter' ) ),
        );

        if ( ! isset( $messages[ $status ] ) ) {
            return;
        }

        $msg  = $messages[ $status ];
        $type = $msg['type'];
        $text = esc_html( $msg['text'] );
        $bg   = 'success' === $type ? '#2d6a4f' : '#1d3557';
        ?>
        <div id="nn-sub-toast" style="position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:<?php echo esc_attr( $bg ); ?>;color:#fff;padding:14px 28px;border-radius:8px;font-size:15px;font-weight:500;z-index:99999;box-shadow:0 4px 20px rgba(0,0,0,0.2);max-width:90vw;text-align:center;">
            <?php echo $text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
        <script>
        setTimeout(function(){
            var t = document.getElementById('nn-sub-toast');
            if(t){ t.style.transition='opacity 0.5s'; t.style.opacity='0'; setTimeout(function(){ t.remove(); }, 500); }
        }, 5000);
        </script>
        <?php
    }

    /**
     * Inject nonce into footer for JS access.
     */
    public static function inject_nonce() {
        if ( is_admin() ) {
            return;
        }
        ?>
        <script>
        if(typeof neonewsData !== 'undefined'){
            neonewsData.subscribeNonce = <?php echo wp_json_encode( wp_create_nonce( 'nn_subscribe_nonce' ) ); ?>;
            neonewsData.subscribeAction = 'nn_subscribe';
            neonewsData.ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
        }
        </script>
        <?php
    }

    /**
     * Get visitor IP address.
     *
     * @return string
     */
    private static function get_ip() {
        $keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
        foreach ( $keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
                // Take first IP if comma-separated.
                $ip = trim( explode( ',', $ip )[0] );
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                    return $ip;
                }
            }
        }
        return '';
    }
}
