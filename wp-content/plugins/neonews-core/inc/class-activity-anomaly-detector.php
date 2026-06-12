<?php
/**
 * Suspicious activity detection and email alerts.
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Activity anomaly detector.
 */
class NeoNews_Activity_Anomaly_Detector {

    /**
     * @var NeoNews_Activity_Anomaly_Detector|null
     */
    private static $instance = null;

    /**
     * Get instance.
     *
     * @return NeoNews_Activity_Anomaly_Detector
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        if ( ! NeoNews_User_Activity_Log::is_enabled() ) {
            return;
        }

        add_action( 'neonews_user_activity_logged', array( $this, 'analyze_event' ), 10, 4 );
    }

    /**
     * Are email alerts enabled?
     *
     * @return bool
     */
    public static function alerts_enabled() {
        return (bool) NeoNews_Core::get_settings( 'enable_activity_alerts' );
    }

    /**
     * Are webhook alerts enabled?
     *
     * @return bool
     */
    public static function webhook_enabled() {
        return (bool) NeoNews_Core::get_settings( 'enable_activity_webhook' ) && self::get_webhook_url();
    }

    /**
     * Any notification channel active.
     *
     * @return bool
     */
    public static function notifications_enabled() {
        return self::alerts_enabled() || self::webhook_enabled();
    }

    /**
     * Alert recipient email.
     *
     * @return string
     */
    public static function get_alert_email() {
        $email = NeoNews_Core::get_settings( 'activity_alert_email' );
        if ( ! $email || ! is_email( $email ) ) {
            $email = get_option( 'admin_email' );
        }
        return sanitize_email( $email );
    }

    /**
     * Webhook URL.
     *
     * @return string
     */
    public static function get_webhook_url() {
        $url = NeoNews_Core::get_settings( 'activity_webhook_url' );
        if ( ! $url ) {
            return '';
        }
        return esc_url_raw( $url );
    }

    /**
     * Webhook type: generic or slack.
     *
     * @return string
     */
    public static function get_webhook_type() {
        $type = NeoNews_Core::get_settings( 'activity_webhook_type' );
        return in_array( $type, array( 'generic', 'slack' ), true ) ? $type : 'generic';
    }

    /**
     * Send a test notification through configured channels.
     *
     * @return array
     */
    public static function send_test_notification() {
        $user = wp_get_current_user();

        $alert = array(
            'rule'    => 'test',
            'subject' => __( '[NewsPulse] Test activity alert', 'neonews-core' ),
            'message' => sprintf(
                /* translators: %s: username */
                __( 'This is a test notification triggered by %s. If you received this, your alert channels are configured correctly.', 'neonews-core' ),
                $user->user_login
            ),
            'key'     => 'test_' . time(),
        );

        return self::get_instance()->dispatch_alert(
            $alert,
            array(
                'skip_throttle' => true,
                'force_email'   => true,
                'force_webhook' => true,
                'is_test'       => true,
            )
        );
    }

    /**
     * Analyze a newly logged event.
     *
     * @param int    $insert_id  Row ID.
     * @param string $event_type Event type.
     * @param int    $user_id    User ID.
     * @param array  $context    Context.
     */
    public function analyze_event( $insert_id, $event_type, $user_id, $context ) {
        if ( ! self::notifications_enabled() ) {
            return;
        }

        $user_id = absint( $user_id );
        $alerts  = array();

        if ( 'login' === $event_type && $user_id ) {
            $login_count = NeoNews_User_Activity_Log::count_recent_events(
                array(
                    'user_id'    => $user_id,
                    'event_type' => 'login',
                    'minutes'    => 15,
                )
            );

            if ( $login_count >= 5 ) {
                $alerts[] = array(
                    'rule'    => 'login_burst_user',
                    'subject' => __( '[NewsPulse] Repeated logins detected', 'neonews-core' ),
                    'message' => sprintf(
                        /* translators: 1: username, 2: count */
                        __( 'User #%1$d logged in %2$d times within 15 minutes. This may indicate credential sharing or a compromised account.', 'neonews-core' ),
                        $user_id,
                        $login_count
                    ),
                    'key'     => 'user_' . $user_id,
                );
            }

            $ip_hash  = NeoNews_User_Activity_Log::get_current_ip_hash();
            $ip_count = NeoNews_User_Activity_Log::count_recent_events(
                array(
                    'event_type' => 'login',
                    'ip_hash'    => $ip_hash,
                    'minutes'    => 15,
                )
            );

            if ( $ip_count >= 8 ) {
                $alerts[] = array(
                    'rule'    => 'login_burst_ip',
                    'subject' => __( '[NewsPulse] Login burst from same network', 'neonews-core' ),
                    'message' => sprintf(
                        /* translators: %d: login count */
                        __( '%d login events from the same network fingerprint occurred within 15 minutes.', 'neonews-core' ),
                        $ip_count
                    ),
                    'key'     => 'ip_' . substr( $ip_hash, 0, 12 ),
                );
            }

            if ( NeoNews_Core::get_settings( 'activity_alert_admin_login' ) ) {
                $user = get_userdata( $user_id );
                if ( $user && in_array( 'administrator', (array) $user->roles, true ) ) {
                    $alerts[] = array(
                        'rule'    => 'admin_login',
                        'subject' => __( '[NewsPulse] Administrator login', 'neonews-core' ),
                        'message' => sprintf(
                            /* translators: %s: username */
                            __( 'Administrator account "%s" just logged in.', 'neonews-core' ),
                            $user->user_login
                        ),
                        'key'     => 'admin_' . $user_id . '_' . gmdate( 'Ymd' ),
                    );
                }
            }
        }

        if ( 'post_read' === $event_type && $user_id ) {
            $read_count = NeoNews_User_Activity_Log::count_recent_events(
                array(
                    'user_id'    => $user_id,
                    'event_type' => 'post_read',
                    'minutes'    => 60,
                )
            );

            if ( $read_count >= 80 ) {
                $alerts[] = array(
                    'rule'    => 'read_flood',
                    'subject' => __( '[NewsPulse] Unusual reading activity', 'neonews-core' ),
                    'message' => sprintf(
                        /* translators: 1: user id, 2: count */
                        __( 'User #%1$d recorded %2$d article reads within one hour.', 'neonews-core' ),
                        $user_id,
                        $read_count
                    ),
                    'key'     => 'read_' . $user_id,
                );
            }
        }

        if ( 'article_submit' === $event_type && $user_id ) {
            $submit_count = NeoNews_User_Activity_Log::count_recent_events(
                array(
                    'user_id'    => $user_id,
                    'event_type' => 'article_submit',
                    'minutes'    => 60,
                )
            );

            if ( $submit_count >= 3 ) {
                $alerts[] = array(
                    'rule'    => 'submit_spam',
                    'subject' => __( '[NewsPulse] Rapid article submissions', 'neonews-core' ),
                    'message' => sprintf(
                        /* translators: 1: user id, 2: count */
                        __( 'User #%1$d submitted %2$d articles within one hour.', 'neonews-core' ),
                        $user_id,
                        $submit_count
                    ),
                    'key'     => 'submit_' . $user_id,
                );
            }
        }

        /**
         * Filter detected alerts before notifications are sent.
         *
         * @param array  $alerts     Alert payloads.
         * @param string $event_type Event type.
         * @param int    $user_id    User ID.
         */
        $alerts = apply_filters( 'neonews_activity_alerts', $alerts, $event_type, $user_id );

        foreach ( $alerts as $alert ) {
            $this->dispatch_alert( $alert );
        }
    }

    /**
     * Dispatch alert to email and/or webhook.
     *
     * @param array $alert Alert payload.
     * @param array $args  Options: skip_throttle, force_email, force_webhook, is_test.
     * @return array Channel results.
     */
    public function dispatch_alert( $alert, $args = array() ) {
        $args = wp_parse_args(
            $args,
            array(
                'skip_throttle' => false,
                'force_email'   => false,
                'force_webhook' => false,
                'is_test'       => false,
            )
        );

        if ( empty( $alert['rule'] ) || empty( $alert['message'] ) ) {
            return array();
        }

        if ( ! $args['skip_throttle'] ) {
            $throttle_key = 'nn_act_alert_' . sanitize_key( $alert['rule'] ) . '_' . sanitize_key( $alert['key'] ?? 'global' );
            if ( get_transient( $throttle_key ) ) {
                return array();
            }
        }

        $results = array(
            'email'   => array( 'attempted' => false, 'success' => false, 'to' => '' ),
            'webhook' => array( 'attempted' => false, 'success' => false, 'type' => '' ),
        );

        $send_email = $args['force_email'] || self::alerts_enabled();
        if ( $send_email ) {
            $results['email'] = $this->send_email( $alert );
        }

        $webhook_url = self::get_webhook_url();
        $send_webhook = $webhook_url && ( $args['force_webhook'] || self::webhook_enabled() );
        if ( $send_webhook ) {
            $results['webhook'] = $this->send_webhook( $alert, $args['is_test'] );
        }

        if ( ! $args['skip_throttle'] && ( $results['email']['success'] || $results['webhook']['success'] ) ) {
            set_transient( $throttle_key, 1, HOUR_IN_SECONDS );
        }

        return $results;
    }

    /**
     * Send email alert.
     *
     * @param array $alert Alert payload.
     * @return array
     */
    private function send_email( $alert ) {
        $email   = self::get_alert_email();
        $subject = $alert['subject'] ?? __( '[NewsPulse] Suspicious activity', 'neonews-core' );
        $body    = $this->build_plaintext_body( $alert );

        return array(
            'attempted' => true,
            'success'   => (bool) wp_mail( $email, $subject, $body ),
            'to'        => $email,
        );
    }

    /**
     * Send webhook alert (generic JSON or Slack).
     *
     * @param array $alert  Alert payload.
     * @param bool  $is_test Is test notification.
     * @return array
     */
    private function send_webhook( $alert, $is_test = false ) {
        $url  = self::get_webhook_url();
        $type = self::get_webhook_type();

        if ( ! $url ) {
            return array(
                'attempted' => false,
                'success'   => false,
                'type'      => $type,
            );
        }

        $review_url = admin_url( 'admin.php?page=neonews-user-activity' );
        $subject    = $alert['subject'] ?? __( '[NewsPulse] Suspicious activity', 'neonews-core' );
        $message    = $alert['message'];

        if ( 'slack' === $type ) {
            $payload = array(
                'text' => sprintf(
                    "*%s*\n%s\n<%s|%s>",
                    $subject,
                    $message,
                    $review_url,
                    __( 'Review activity log', 'neonews-core' )
                ),
            );
        } else {
            $payload = array(
                'event'      => 'neonews.activity_alert',
                'rule'       => $alert['rule'] ?? '',
                'subject'    => $subject,
                'message'    => $message,
                'site'       => get_bloginfo( 'name' ),
                'site_url'   => home_url( '/' ),
                'review_url' => $review_url,
                'is_test'    => $is_test,
                'timestamp'  => gmdate( 'c' ),
            );
        }

        /**
         * Filter webhook JSON payload before POST.
         *
         * @param array $payload Payload.
         * @param array $alert   Alert data.
         * @param string $type   Webhook type.
         */
        $payload = apply_filters( 'neonews_activity_webhook_payload', $payload, $alert, $type );

        $response = wp_remote_post(
            $url,
            array(
                'timeout' => 15,
                'headers' => array(
                    'Content-Type' => 'application/json; charset=utf-8',
                ),
                'body'    => wp_json_encode( $payload ),
            )
        );

        $success = ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) >= 200 && wp_remote_retrieve_response_code( $response ) < 300;

        return array(
            'attempted' => true,
            'success'   => $success,
            'type'      => $type,
            'error'     => is_wp_error( $response ) ? $response->get_error_message() : '',
        );
    }

    /**
     * Build plain-text alert body.
     *
     * @param array $alert Alert payload.
     * @return string
     */
    private function build_plaintext_body( $alert ) {
        $subject = $alert['subject'] ?? '';
        $body    = $alert['message'] . "\n\n";
        $body   .= sprintf(
            /* translators: %s: admin URL */
            __( 'Review activity: %s', 'neonews-core' ),
            admin_url( 'admin.php?page=neonews-user-activity' )
        );
        $body   .= "\n\n" . sprintf(
            /* translators: %s: site name */
            __( 'Site: %s', 'neonews-core' ),
            get_bloginfo( 'name' )
        );

        if ( ! empty( $alert['rule'] ) && 'test' !== $alert['rule'] ) {
            $body .= "\n" . sprintf(
                /* translators: %s: rule slug */
                __( 'Rule: %s', 'neonews-core' ),
                $alert['rule']
            );
        }

        return $body;
    }
}
