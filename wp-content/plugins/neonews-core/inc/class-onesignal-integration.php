<?php
/**
 * OneSignal Integration
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * OneSignal Integration Class
 */
class NeoNews_OneSignal_Integration {

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
        add_action( 'wp_head', array( $this, 'enqueue_onesignal_script' ) );
        add_action( 'publish_post', array( $this, 'send_notification_on_publish' ), 10, 2 );
        add_action( 'neonews_post_marked_breaking', array( $this, 'send_breaking_notification' ), 10, 2 );
    }

    /**
     * Enqueue OneSignal script
     */
    public function enqueue_onesignal_script() {
        $app_id = NeoNews_Core::get_settings( 'onesignal_app_id' );
        
        if ( empty( $app_id ) ) {
            return;
        }

        ?>
        <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
        <script>
            window.OneSignalDeferred = window.OneSignalDeferred || [];
            OneSignalDeferred.push(async function(OneSignal) {
                await OneSignal.init({
                    appId: <?php echo wp_json_encode( $app_id ); ?>,
                    allowLocalhostAsSecureOrigin: true,
                    notifyButton: {
                        enable: true,
                        size: 'medium',
                        position: 'bottom-right',
                        showCredit: false,
                        text: {
                            'tip.state.unsubscribed': <?php echo wp_json_encode( __( 'Subscribe to notifications', 'neonews-core' ) ); ?>,
                            'tip.state.subscribed': <?php echo wp_json_encode( __( "You're subscribed to notifications", 'neonews-core' ) ); ?>,
                            'tip.state.blocked': <?php echo wp_json_encode( __( "You've blocked notifications", 'neonews-core' ) ); ?>,
                            'message.prenotify': <?php echo wp_json_encode( __( 'Click to subscribe to notifications', 'neonews-core' ) ); ?>,
                            'message.action.subscribed': <?php echo wp_json_encode( __( 'Thanks for subscribing!', 'neonews-core' ) ); ?>,
                            'message.action.resubscribed': <?php echo wp_json_encode( __( "You're subscribed to notifications", 'neonews-core' ) ); ?>,
                            'message.action.unsubscribed': <?php echo wp_json_encode( __( "You won't receive notifications again", 'neonews-core' ) ); ?>,
                            'dialog.main.title': <?php echo wp_json_encode( __( 'Manage Site Notifications', 'neonews-core' ) ); ?>,
                            'dialog.main.button.subscribe': <?php echo wp_json_encode( __( 'SUBSCRIBE', 'neonews-core' ) ); ?>,
                            'dialog.main.button.unsubscribe': <?php echo wp_json_encode( __( 'UNSUBSCRIBE', 'neonews-core' ) ); ?>,
                            'dialog.blocked.title': <?php echo wp_json_encode( __( 'Unblock Notifications', 'neonews-core' ) ); ?>,
                            'dialog.blocked.message': <?php echo wp_json_encode( __( 'Follow these instructions to allow notifications:', 'neonews-core' ) ); ?>
                        }
                    }
                });
            });
        </script>
        <?php
    }

    /**
     * Send notification on post publish
     */
    public function send_notification_on_publish( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        $should_notify = get_post_meta( $post_id, '_neonews_push_notification', true );
        
        if ( '1' !== $should_notify ) {
            return;
        }

        delete_post_meta( $post_id, '_neonews_push_notification' );

        $this->send_notification(
            $post->post_title,
            wp_trim_words( $post->post_content, 20, '...' ),
            get_permalink( $post_id ),
            $post_id
        );
    }

    /**
     * Send notification for breaking news
     */
    public function send_breaking_notification( $post_id, $post ) {
        $this->send_notification(
            sprintf(
                /* translators: %s: post title */
                __( 'BREAKING: %s', 'neonews-core' ),
                $post->post_title
            ),
            wp_trim_words( $post->post_content, 20, '...' ),
            get_permalink( $post_id ),
            $post_id,
            true
        );
    }

    /**
     * Send push notification via OneSignal API
     */
    private function send_notification( $heading, $content, $url, $post_id, $is_breaking = false ) {
        $app_id = NeoNews_Core::get_settings( 'onesignal_app_id' );
        $rest_key = NeoNews_Core::get_settings( 'onesignal_rest_key' );

        if ( empty( $app_id ) || empty( $rest_key ) ) {
            return false;
        }

        $notification_sent = get_post_meta( $post_id, '_neonews_notification_sent', true );
        if ( $notification_sent && ! $is_breaking ) {
            return false;
        }

        $payload = array(
            'app_id'            => $app_id,
            'included_segments' => array( 'Subscribed Users' ),
            'headings'          => array( 'en' => $heading ),
            'contents'          => array( 'en' => wp_strip_all_tags( $content ) ),
            'url'               => $url,
        );

        if ( has_post_thumbnail( $post_id ) ) {
            $image_url = get_the_post_thumbnail_url( $post_id, 'medium' );
            if ( $image_url ) {
                $payload['big_picture'] = $image_url;
                $payload['chrome_web_image'] = $image_url;
            }
        }

        $response = wp_remote_post(
            'https://onesignal.com/api/v1/notifications',
            array(
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Basic ' . $rest_key,
                ),
                'body'    => wp_json_encode( $payload ),
                'timeout' => 30,
            )
        );

        if ( is_wp_error( $response ) ) {
            error_log( 'NeoNews OneSignal Error: ' . $response->get_error_message() );
            return false;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        
        if ( 200 === $response_code || 201 === $response_code ) {
            update_post_meta( $post_id, '_neonews_notification_sent', current_time( 'mysql' ) );
            return true;
        }

        $body = wp_remote_retrieve_body( $response );
        error_log( 'NeoNews OneSignal Error Response: ' . $body );
        
        return false;
    }

    /**
     * Check if OneSignal is configured
     */
    public static function is_configured() {
        $app_id = NeoNews_Core::get_settings( 'onesignal_app_id' );
        return ! empty( $app_id );
    }
}
