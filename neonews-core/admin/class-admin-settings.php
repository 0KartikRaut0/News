<?php
/**
 * Admin Settings
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin Settings Class
 */
class NeoNews_Admin_Settings {

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
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 20 );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_post_neonews_test_activity_alert', array( $this, 'handle_test_alert' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'neonews-settings',
            __( 'Core Features', 'neonews-core' ),
            __( 'Core Features', 'neonews-core' ),
            'manage_options',
            'neonews-core-settings',
            array( $this, 'render_settings_page' )
        );

        add_submenu_page(
            'neonews-settings',
            __( 'Pending Submissions', 'neonews-core' ),
            __( 'Pending Submissions', 'neonews-core' ),
            'edit_others_posts',
            'edit.php?post_status=pending&post_type=post'
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'neonews_core_settings_group',
            'neonews_core_settings',
            array(
                'sanitize_callback' => array( $this, 'sanitize_settings' ),
            )
        );

        add_settings_section(
            'neonews_general_section',
            __( 'General Settings', 'neonews-core' ),
            array( $this, 'render_general_section' ),
            'neonews-core-settings'
        );

        add_settings_field(
            'enable_submission',
            __( 'Enable News Submission', 'neonews-core' ),
            array( $this, 'render_checkbox_field' ),
            'neonews-core-settings',
            'neonews_general_section',
            array(
                'id'          => 'enable_submission',
                'description' => __( 'Allow users to submit news articles from the frontend.', 'neonews-core' ),
            )
        );

        add_settings_field(
            'enable_view_counter',
            __( 'Enable View Counter', 'neonews-core' ),
            array( $this, 'render_checkbox_field' ),
            'neonews-core-settings',
            'neonews_general_section',
            array(
                'id'          => 'enable_view_counter',
                'description' => __( 'Track post views with bot protection.', 'neonews-core' ),
            )
        );

        add_settings_field(
            'enable_activity_tracking',
            __( 'Enable Activity Tracking', 'neonews-core' ),
            array( $this, 'render_checkbox_field' ),
            'neonews-core-settings',
            'neonews_general_section',
            array(
                'id'          => 'enable_activity_tracking',
                'description' => __( 'Track user logins, reads, likes, shares, and profile changes.', 'neonews-core' ),
            )
        );

        add_settings_section(
            'neonews_activity_alerts_section',
            __( 'Activity Alerts', 'neonews-core' ),
            array( $this, 'render_activity_alerts_section' ),
            'neonews-core-settings'
        );

        add_settings_field(
            'enable_activity_alerts',
            __( 'Email Suspicious Activity Alerts', 'neonews-core' ),
            array( $this, 'render_checkbox_field' ),
            'neonews-core-settings',
            'neonews_activity_alerts_section',
            array(
                'id'          => 'enable_activity_alerts',
                'description' => __( 'Send email when login bursts, read floods, or rapid submissions are detected.', 'neonews-core' ),
            )
        );

        add_settings_field(
            'activity_alert_email',
            __( 'Alert Email', 'neonews-core' ),
            array( $this, 'render_email_field' ),
            'neonews-core-settings',
            'neonews_activity_alerts_section',
            array(
                'id'          => 'activity_alert_email',
                'description' => __( 'Recipient for activity alerts. Defaults to the site admin email.', 'neonews-core' ),
            )
        );

        add_settings_field(
            'activity_alert_admin_login',
            __( 'Alert on Administrator Login', 'neonews-core' ),
            array( $this, 'render_checkbox_field' ),
            'neonews-core-settings',
            'neonews_activity_alerts_section',
            array(
                'id'          => 'activity_alert_admin_login',
                'description' => __( 'Notify when an administrator account logs in (once per day per account).', 'neonews-core' ),
            )
        );

        add_settings_field(
            'enable_activity_webhook',
            __( 'Enable Webhook / Slack Alerts', 'neonews-core' ),
            array( $this, 'render_checkbox_field' ),
            'neonews-core-settings',
            'neonews_activity_alerts_section',
            array(
                'id'          => 'enable_activity_webhook',
                'description' => __( 'Also POST alerts to a webhook URL (works alongside email).', 'neonews-core' ),
            )
        );

        add_settings_field(
            'activity_webhook_type',
            __( 'Webhook Type', 'neonews-core' ),
            array( $this, 'render_select_field' ),
            'neonews-core-settings',
            'neonews_activity_alerts_section',
            array(
                'id'          => 'activity_webhook_type',
                'description' => __( 'Choose Generic JSON for custom endpoints, or Slack for Incoming Webhooks.', 'neonews-core' ),
                'options'     => array(
                    'generic' => __( 'Generic JSON webhook', 'neonews-core' ),
                    'slack'   => __( 'Slack Incoming Webhook', 'neonews-core' ),
                ),
            )
        );

        add_settings_field(
            'activity_webhook_url',
            __( 'Webhook URL', 'neonews-core' ),
            array( $this, 'render_url_field' ),
            'neonews-core-settings',
            'neonews_activity_alerts_section',
            array(
                'id'          => 'activity_webhook_url',
                'description' => __( 'Slack: Apps → Incoming Webhooks → copy URL. Generic: any HTTPS endpoint that accepts JSON POST.', 'neonews-core' ),
            )
        );

        add_settings_section(
            'neonews_onesignal_section',
            __( 'OneSignal Push Notifications', 'neonews-core' ),
            array( $this, 'render_onesignal_section' ),
            'neonews-core-settings'
        );

        add_settings_field(
            'onesignal_app_id',
            __( 'OneSignal App ID', 'neonews-core' ),
            array( $this, 'render_text_field' ),
            'neonews-core-settings',
            'neonews_onesignal_section',
            array(
                'id'          => 'onesignal_app_id',
                'description' => __( 'Your OneSignal App ID (found in OneSignal dashboard).', 'neonews-core' ),
            )
        );

        add_settings_field(
            'onesignal_rest_key',
            __( 'OneSignal REST API Key', 'neonews-core' ),
            array( $this, 'render_password_field' ),
            'neonews-core-settings',
            'neonews_onesignal_section',
            array(
                'id'          => 'onesignal_rest_key',
                'description' => __( 'Your OneSignal REST API Key (found in OneSignal dashboard > Settings > Keys).', 'neonews-core' ),
            )
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings( $input ) {
        $previous  = NeoNews_Core::get_settings();
        $sanitized = $previous;

        $sanitized['enable_submission'] = ! empty( $input['enable_submission'] );
        $sanitized['enable_view_counter'] = ! empty( $input['enable_view_counter'] );
        $sanitized['enable_activity_tracking'] = ! empty( $input['enable_activity_tracking'] );
        $sanitized['enable_activity_alerts'] = ! empty( $input['enable_activity_alerts'] );
        $sanitized['activity_alert_email'] = sanitize_email( $input['activity_alert_email'] ?? '' );
        $sanitized['activity_alert_admin_login'] = ! empty( $input['activity_alert_admin_login'] );
        $sanitized['enable_activity_webhook'] = ! empty( $input['enable_activity_webhook'] );
        $sanitized['activity_webhook_type'] = in_array( $input['activity_webhook_type'] ?? '', array( 'generic', 'slack' ), true )
            ? $input['activity_webhook_type']
            : 'generic';
        $sanitized['activity_webhook_url'] = esc_url_raw( $input['activity_webhook_url'] ?? '' );
        $sanitized['onesignal_app_id'] = sanitize_text_field( $input['onesignal_app_id'] ?? '' );
        $sanitized['onesignal_rest_key'] = sanitize_text_field( $input['onesignal_rest_key'] ?? '' );

        return $sanitized;
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $this->render_test_alert_notices();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <form action="options.php" method="post">
                <?php
                settings_fields( 'neonews_core_settings_group' );
                do_settings_sections( 'neonews-core-settings' );
                submit_button();
                ?>
            </form>

            <div class="nn-admin-info nn-activity-test-box">
                <h2><?php esc_html_e( 'Test Activity Alerts', 'neonews-core' ); ?></h2>
                <p class="description"><?php esc_html_e( 'Save your settings first, then send a test notification to your email and webhook URL.', 'neonews-core' ); ?></p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="nn-activity-test-form">
                    <?php wp_nonce_field( 'neonews_test_activity_alert' ); ?>
                    <input type="hidden" name="action" value="neonews_test_activity_alert" />
                    <?php submit_button( __( 'Send test alert', 'neonews-core' ), 'secondary', 'submit', false ); ?>
                </form>
            </div>

            <div class="nn-admin-info">
                <h2><?php esc_html_e( 'Quick Links', 'neonews-core' ); ?></h2>
                <ul>
                    <li>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_status=pending&post_type=post' ) ); ?>">
                            <?php esc_html_e( 'View Pending Submissions', 'neonews-core' ); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-user-activity' ) ); ?>">
                            <?php esc_html_e( 'User Activity Log', 'neonews-core' ); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url( admin_url( 'users.php?role=nn_reporter' ) ); ?>">
                            <?php esc_html_e( 'View Reporters', 'neonews-core' ); ?>
                        </a>
                    </li>
                </ul>

                <h3><?php esc_html_e( 'REST API', 'neonews-core' ); ?></h3>
                <p><code><?php echo esc_html( rest_url( 'neonews/v1/activity' ) ); ?></code></p>
                <p class="description"><?php esc_html_e( 'Requires administrator login (Application Password or cookie auth). See /activity/stats and /activity/types.', 'neonews-core' ); ?></p>

                <h3><?php esc_html_e( 'Shortcodes', 'neonews-core' ); ?></h3>
                <p><code>[neonews_submit_form]</code> - <?php esc_html_e( 'Frontend article submission form for reporters.', 'neonews-core' ); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Render general section
     */
    public function render_general_section() {
        echo '<p>' . esc_html__( 'Configure the core features of NeoNews.', 'neonews-core' ) . '</p>';
    }

    /**
     * Render activity alerts section.
     */
    public function render_activity_alerts_section() {
        echo '<p>' . esc_html__( 'Get notified when unusual patterns are detected in the activity log. Use the test button below the settings form to verify delivery.', 'neonews-core' ) . '</p>';
    }

    /**
     * Handle test alert button.
     */
    public function handle_test_alert() {
        if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'neonews_test_activity_alert' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'neonews-core' ) );
        }

        $results = NeoNews_Activity_Anomaly_Detector::send_test_notification();

        set_transient( 'neonews_test_alert_' . get_current_user_id(), $results, MINUTE_IN_SECONDS );

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'          => 'neonews-core-settings',
                    'nn_alert_test' => '1',
                ),
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    /**
     * Show admin notices after test alert.
     */
    private function render_test_alert_notices() {
        if ( empty( $_GET['nn_alert_test'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            return;
        }

        $results = get_transient( 'neonews_test_alert_' . get_current_user_id() );
        delete_transient( 'neonews_test_alert_' . get_current_user_id() );

        if ( ! is_array( $results ) ) {
            return;
        }

        if ( ! empty( $results['email']['attempted'] ) ) {
            $class = ! empty( $results['email']['success'] ) ? 'notice-success' : 'notice-error';
            $msg   = ! empty( $results['email']['success'] )
                ? sprintf(
                    /* translators: %s: email address */
                    __( 'Test email sent to %s.', 'neonews-core' ),
                    $results['email']['to']
                )
                : sprintf(
                    /* translators: %s: email address */
                    __( 'Test email failed to send to %s. Check your WordPress mail configuration.', 'neonews-core' ),
                    $results['email']['to']
                );
            echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
        }

        if ( ! empty( $results['webhook']['attempted'] ) ) {
            $class      = ! empty( $results['webhook']['success'] ) ? 'notice-success' : 'notice-error';
            $type_label = ( 'slack' === ( $results['webhook']['type'] ?? '' ) ) ? 'Slack' : __( 'Generic webhook', 'neonews-core' );
            $msg        = ! empty( $results['webhook']['success'] )
                ? sprintf(
                    /* translators: %s: webhook type label */
                    __( 'Test %s notification delivered successfully.', 'neonews-core' ),
                    $type_label
                )
                : sprintf(
                    /* translators: 1: webhook type, 2: error message */
                    __( 'Webhook delivery failed (%1$s). %2$s', 'neonews-core' ),
                    $type_label,
                    ! empty( $results['webhook']['error'] ) ? $results['webhook']['error'] : __( 'Check the URL and server response.', 'neonews-core' )
                );
            echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
        } elseif ( ! NeoNews_Activity_Anomaly_Detector::get_webhook_url() ) {
            echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__( 'No webhook URL configured — only email was tested.', 'neonews-core' ) . '</p></div>';
        }
    }

    /**
     * Render OneSignal section
     */
    public function render_onesignal_section() {
        echo '<p>' . esc_html__( 'Configure OneSignal for push notifications. Leave empty to disable.', 'neonews-core' ) . '</p>';
        echo '<p><a href="https://onesignal.com/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Get your free OneSignal account', 'neonews-core' ) . '</a></p>';
    }

    /**
     * Render checkbox field
     */
    public function render_checkbox_field( $args ) {
        $settings = NeoNews_Core::get_settings();
        $value = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : false;
        ?>
        <label>
            <input type="checkbox" 
                   name="neonews_core_settings[<?php echo esc_attr( $args['id'] ); ?>]" 
                   value="1" 
                   <?php checked( $value, true ); ?>>
            <?php echo esc_html( $args['description'] ); ?>
        </label>
        <?php
    }

    /**
     * Render text field
     */
    public function render_text_field( $args ) {
        $settings = NeoNews_Core::get_settings();
        $value = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : '';
        ?>
        <input type="text" 
               id="<?php echo esc_attr( $args['id'] ); ?>"
               name="neonews_core_settings[<?php echo esc_attr( $args['id'] ); ?>]" 
               value="<?php echo esc_attr( $value ); ?>"
               class="regular-text">
        <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php
    }

    /**
     * Render email field
     */
    public function render_email_field( $args ) {
        $settings = NeoNews_Core::get_settings();
        $value    = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : '';
        ?>
        <input type="email"
               id="<?php echo esc_attr( $args['id'] ); ?>"
               name="neonews_core_settings[<?php echo esc_attr( $args['id'] ); ?>]"
               value="<?php echo esc_attr( $value ); ?>"
               class="regular-text">
        <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php
    }

    /**
     * Render select field.
     */
    public function render_select_field( $args ) {
        $settings = NeoNews_Core::get_settings();
        $value    = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : 'generic';
        ?>
        <select id="<?php echo esc_attr( $args['id'] ); ?>" name="neonews_core_settings[<?php echo esc_attr( $args['id'] ); ?>]">
            <?php foreach ( $args['options'] as $opt_value => $label ) : ?>
                <option value="<?php echo esc_attr( $opt_value ); ?>" <?php selected( $value, $opt_value ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php
    }

    /**
     * Render URL field.
     */
    public function render_url_field( $args ) {
        $settings = NeoNews_Core::get_settings();
        $value    = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : '';
        ?>
        <input type="url"
               id="<?php echo esc_attr( $args['id'] ); ?>"
               name="neonews_core_settings[<?php echo esc_attr( $args['id'] ); ?>]"
               value="<?php echo esc_attr( $value ); ?>"
               class="large-text"
               placeholder="https://">
        <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php
    }

    /**
     * Render password field
     */
    public function render_password_field( $args ) {
        $settings = NeoNews_Core::get_settings();
        $value = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : '';
        ?>
        <input type="password" 
               id="<?php echo esc_attr( $args['id'] ); ?>"
               name="neonews_core_settings[<?php echo esc_attr( $args['id'] ); ?>]" 
               value="<?php echo esc_attr( $value ); ?>"
               class="regular-text">
        <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php
    }
}
