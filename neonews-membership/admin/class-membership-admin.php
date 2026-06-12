<?php
/**
 * Membership Admin
 *
 * @package NeoNews_Membership
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Membership Admin Class
 */
class NeoNews_Membership_Admin {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_init', array( $this, 'handle_key_actions' ) );
        add_action( 'admin_init', array( $this, 'handle_admin_actions' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta' ), 10, 2 );
        add_filter( 'manage_posts_columns', array( $this, 'add_premium_column' ) );
        add_action( 'manage_posts_custom_column', array( $this, 'render_premium_column' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Admin styles for posts list premium toggle.
     *
     * @param string $hook Hook suffix.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'edit.php' === $hook ) {
            wp_add_inline_style(
                'dashicons',
                '.nn-premium-toggle{display:inline-flex;align-items:center;gap:4px;padding:4px 8px;border-radius:4px;text-decoration:none;font-size:12px;font-weight:600;line-height:1.4;border:1px solid transparent;}'
                . '.nn-premium-toggle .dashicons{font-size:16px;width:16px;height:16px;}'
                . '.nn-premium-toggle-on{color:#b32d2e;background:#fcf0f1;border-color:#f1aeb5;}'
                . '.nn-premium-toggle-on:hover{color:#8a2424;background:#f8d7da;}'
                . '.nn-premium-toggle-off{color:#2271b1;background:#f0f6fc;border-color:#c3d9ed;}'
                . '.nn-premium-toggle-off:hover{color:#135e96;background:#dbeafe;}'
                . '.column-nn_premium{width:110px;}'
            );
            return;
        }

        if ( false === strpos( $hook, 'neonews-membership' ) ) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script(
            'neonews-membership-admin',
            NEONEWS_MEMBERSHIP_URL . 'admin/js/admin.js',
            array( 'jquery' ),
            NEONEWS_MEMBERSHIP_VERSION,
            true
        );
        wp_add_inline_style(
            'dashicons',
            '.nn-membership-media-field{display:flex;flex-wrap:wrap;align-items:flex-start;gap:12px;}'
            . '.nn-membership-media-preview{width:72px;height:72px;border:1px solid #c3c4c7;border-radius:8px;background:#f6f7f7;display:flex;align-items:center;justify-content:center;overflow:hidden;}'
            . '.nn-membership-media-preview img{max-width:100%;max-height:100%;object-fit:contain;display:block;}'
            . '.nn-membership-media-actions{display:flex;flex-direction:column;gap:6px;}'
        );
    }

    public function add_admin_menu() {
        add_submenu_page(
            'neonews-settings',
            __( 'Membership', 'neonews-membership' ),
            __( 'Membership', 'neonews-membership' ),
            'manage_options',
            'neonews-membership',
            array( $this, 'render_settings_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'neonews_membership_group',
            'neonews_membership_settings',
            array(
                'sanitize_callback' => array( $this, 'sanitize_settings' ),
            )
        );
    }

    public function handle_key_actions() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( isset( $_POST['neonews_generate_key'] ) && check_admin_referer( 'neonews_generate_key' ) ) {
            $days       = absint( $_POST['key_duration_days'] ?? NeoNews_Membership::get_settings( 'key_default_duration_days' ) );
            $valid_days = absint( $_POST['key_valid_days'] ?? 0 );
            $label      = sanitize_text_field( wp_unslash( $_POST['key_batch_label'] ?? '' ) );
            $count      = max( 1, min( 20, absint( $_POST['key_generate_count'] ?? 1 ) ) );

            $generated = array();
            for ( $i = 0; $i < $count; $i++ ) {
                $key = NeoNews_Activation_Keys::generate_key( $days, $label, $valid_days );
                if ( ! is_wp_error( $key ) ) {
                    $generated[] = $key;
                }
            }

            set_transient( 'neonews_generated_keys', $generated, MINUTE_IN_SECONDS );
            wp_safe_redirect( add_query_arg( array( 'page' => 'neonews-membership', 'tab' => 'keys', 'generated' => 1 ), admin_url( 'admin.php' ) ) );
            exit;
        }

        if ( isset( $_GET['revoke_key'] ) && check_admin_referer( 'neonews_revoke_key_' . absint( $_GET['revoke_key'] ) ) ) {
            NeoNews_Activation_Keys::revoke_key( absint( $_GET['revoke_key'] ) );
            wp_safe_redirect( add_query_arg( array( 'page' => 'neonews-membership', 'tab' => 'keys' ), admin_url( 'admin.php' ) ) );
            exit;
        }
    }

    public function sanitize_settings( $input ) {
        $previous = NeoNews_Membership::get_settings();
        if ( ! is_array( $input ) ) {
            return $previous;
        }

        $sanitized = $previous;
        $tab       = sanitize_key( $input['membership_settings_tab'] ?? 'general' );

        $bool = static function ( $key ) use ( $input ) {
            return ! empty( $input[ $key ] );
        };

        switch ( $tab ) {
            case 'badges':
                $sanitized['show_badge_on_avatar']      = $bool( 'show_badge_on_avatar' );
                $sanitized['show_tag_profile']          = $bool( 'show_tag_profile' );
                $sanitized['show_tag_account']          = $bool( 'show_tag_account' );
                $sanitized['show_badge_comments']       = $bool( 'show_badge_comments' );
                $sanitized['show_expiry_timer_profile'] = $bool( 'show_expiry_timer_profile' );
                $sanitized['show_expiry_timer_account'] = $bool( 'show_expiry_timer_account' );
                $sanitized['show_expiry_icon_header']   = $bool( 'show_expiry_icon_header' );
                $sanitized['basic_badge_label']         = sanitize_text_field( wp_unslash( $input['basic_badge_label'] ?? $previous['basic_badge_label'] ) );
                $sanitized['basic_tag_label']           = sanitize_text_field( wp_unslash( $input['basic_tag_label'] ?? $previous['basic_tag_label'] ) );
                $sanitized['premium_badge_label']       = sanitize_text_field( wp_unslash( $input['premium_badge_label'] ?? $previous['premium_badge_label'] ) );
                $sanitized['premium_badge_image_id']    = absint( $input['premium_badge_image_id'] ?? $previous['premium_badge_image_id'] );
                $sanitized['basic_badge_image_id']      = absint( $input['basic_badge_image_id'] ?? $previous['basic_badge_image_id'] );
                $sanitized['premium_tag_label']         = sanitize_text_field( wp_unslash( $input['premium_tag_label'] ?? $previous['premium_tag_label'] ) );
                $sanitized['expiry_reminder_days']       = max( 1, absint( $input['expiry_reminder_days'] ?? $previous['expiry_reminder_days'] ) );
                break;

            case 'plan':
                $sanitized['enroll_note']             = sanitize_textarea_field( wp_unslash( $input['enroll_note'] ?? $previous['enroll_note'] ) );
                $sanitized['plan_title']              = sanitize_text_field( wp_unslash( $input['plan_title'] ?? $previous['plan_title'] ) );
                $sanitized['plan_price']              = sanitize_text_field( wp_unslash( $input['plan_price'] ?? $previous['plan_price'] ) );
                $sanitized['plan_features']           = sanitize_textarea_field( wp_unslash( $input['plan_features'] ?? $previous['plan_features'] ) );
                $sanitized['contact_email']           = sanitize_email( $input['contact_email'] ?? $previous['contact_email'] );
                $sanitized['contact_whatsapp_number']   = preg_replace( '/[^0-9]/', '', (string) ( $input['contact_whatsapp_number'] ?? $previous['contact_whatsapp_number'] ) );
                $sanitized['whatsapp_enroll_message'] = sanitize_text_field( wp_unslash( $input['whatsapp_enroll_message'] ?? $previous['whatsapp_enroll_message'] ) );
                $sanitized['email_enroll_subject']    = sanitize_text_field( wp_unslash( $input['email_enroll_subject'] ?? $previous['email_enroll_subject'] ) );
                $sanitized['email_enroll_body']       = sanitize_textarea_field( wp_unslash( $input['email_enroll_body'] ?? $previous['email_enroll_body'] ) );
                break;

            case 'content':
                $sanitized['enable_blur_lock']       = $bool( 'enable_blur_lock' );
                $sanitized['lock_blur_strength']     = max( 2, min( 16, absint( $input['lock_blur_strength'] ?? $previous['lock_blur_strength'] ) ) );
                $sanitized['premium_excerpt_length'] = absint( $input['premium_excerpt_length'] ?? $previous['premium_excerpt_length'] );
                break;

            case 'popup':
                $sanitized['enable_upgrade_popup']   = $bool( 'enable_upgrade_popup' );
                $sanitized['popup_delay_seconds']    = max( 5, absint( $input['popup_delay_seconds'] ?? $previous['popup_delay_seconds'] ) );
                $sanitized['popup_interval_hours']   = max( 1, absint( $input['popup_interval_hours'] ?? $previous['popup_interval_hours'] ) );
                $sanitized['popup_title']            = sanitize_text_field( wp_unslash( $input['popup_title'] ?? $previous['popup_title'] ) );
                $sanitized['popup_message']          = sanitize_textarea_field( wp_unslash( $input['popup_message'] ?? $previous['popup_message'] ) );
                $sanitized['show_home_premium_section'] = $bool( 'show_home_premium_section' );
                $sanitized['home_premium_title']     = sanitize_text_field( wp_unslash( $input['home_premium_title'] ?? $previous['home_premium_title'] ) );
                $sanitized['home_premium_count']     = max( 1, min( 12, absint( $input['home_premium_count'] ?? $previous['home_premium_count'] ) ) );
                break;

            case 'exclusive':
                $layouts = array( 'rich', 'compact', 'magazine' );
                $styles  = array( 'elevated', 'minimal', 'gradient' );
                $layout  = sanitize_key( $input['exclusive_card_layout'] ?? $previous['exclusive_card_layout'] );
                $style   = sanitize_key( $input['exclusive_card_style'] ?? $previous['exclusive_card_style'] );
                $sanitized['exclusive_page_count']        = max( 1, min( 24, absint( $input['exclusive_page_count'] ?? $previous['exclusive_page_count'] ) ) );
                $sanitized['exclusive_page_subtitle']     = sanitize_text_field( wp_unslash( $input['exclusive_page_subtitle'] ?? $previous['exclusive_page_subtitle'] ) );
                $sanitized['exclusive_badge_label']       = sanitize_text_field( wp_unslash( $input['exclusive_badge_label'] ?? $previous['exclusive_badge_label'] ) );
                $sanitized['exclusive_card_layout']       = in_array( $layout, $layouts, true ) ? $layout : 'rich';
                $sanitized['exclusive_card_style']        = in_array( $style, $styles, true ) ? $style : 'elevated';
                $sanitized['exclusive_show_category']     = $bool( 'exclusive_show_category' );
                $sanitized['exclusive_show_excerpt']      = $bool( 'exclusive_show_excerpt' );
                $sanitized['exclusive_show_author']       = $bool( 'exclusive_show_author' );
                $sanitized['exclusive_show_date']         = $bool( 'exclusive_show_date' );
                $sanitized['exclusive_show_reading_time'] = $bool( 'exclusive_show_reading_time' );
                $sanitized['exclusive_show_views']        = $bool( 'exclusive_show_views' );
                $sanitized['exclusive_show_views_overlay']= $bool( 'exclusive_show_views_overlay' );
                $sanitized['exclusive_show_likes']        = $bool( 'exclusive_show_likes' );
                $sanitized['exclusive_show_comments']     = $bool( 'exclusive_show_comments' );
                $sanitized['exclusive_show_engagement']   = $bool( 'exclusive_show_engagement' );
                $sanitized['exclusive_excerpt_words']     = max( 8, min( 50, absint( $input['exclusive_excerpt_words'] ?? $previous['exclusive_excerpt_words'] ) ) );
                break;

            case 'payments':
                $sanitized['payment_gateway_enabled'] = $bool( 'payment_gateway_enabled' );
                $sanitized['stripe_mode']             = sanitize_key( $input['stripe_mode'] ?? $previous['stripe_mode'] );
                $sanitized['stripe_test_public_key']  = sanitize_text_field( $input['stripe_test_public_key'] ?? $previous['stripe_test_public_key'] );
                $sanitized['stripe_test_secret_key']  = sanitize_text_field( $input['stripe_test_secret_key'] ?? $previous['stripe_test_secret_key'] );
                $sanitized['stripe_live_public_key']  = sanitize_text_field( $input['stripe_live_public_key'] ?? $previous['stripe_live_public_key'] );
                $sanitized['stripe_live_secret_key']  = sanitize_text_field( $input['stripe_live_secret_key'] ?? $previous['stripe_live_secret_key'] );
                break;

            default:
                $sanitized['enable_paywall']            = $bool( 'enable_paywall' );
                $sanitized['hide_ads_for_premium']      = $bool( 'hide_ads_for_premium' );
                $sanitized['free_article_limit']        = absint( $input['free_article_limit'] ?? $previous['free_article_limit'] );
                $sanitized['free_article_period']       = sanitize_key( $input['free_article_period'] ?? $previous['free_article_period'] );
                $sanitized['exclusive_page_slug']       = sanitize_title( $input['exclusive_page_slug'] ?? $previous['exclusive_page_slug'] );
                $sanitized['key_default_duration_days'] = max( 1, absint( $input['key_default_duration_days'] ?? $previous['key_default_duration_days'] ) );
                $sanitized['key_prefix']                = sanitize_text_field( $input['key_prefix'] ?? $previous['key_prefix'] );
                if ( ! empty( $input['exclusive_page_slug'] ) ) {
                    neonews_membership_ensure_exclusive_page();
                    if ( function_exists( 'neonews_sync_primary_nav_menus' ) ) {
                        neonews_sync_primary_nav_menus();
                    }
                }
                break;
        }

        return $sanitized;
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $tab      = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
        $tabs     = array(
            'general'  => __( 'General', 'neonews-membership' ),
            'badges'   => __( 'Badges & Timer', 'neonews-membership' ),
            'keys'     => __( 'Activation Keys', 'neonews-membership' ),
            'members'  => __( 'Premium Members', 'neonews-membership' ),
            'plan'     => __( 'Plan & Contact', 'neonews-membership' ),
            'content'  => __( 'Content & Lock', 'neonews-membership' ),
            'popup'    => __( 'Popup & Home', 'neonews-membership' ),
            'exclusive' => __( 'Exclusive Display', 'neonews-membership' ),
            'payments' => __( 'Payment Gateway', 'neonews-membership' ),
        );
        if ( ! isset( $tabs[ $tab ] ) ) {
            $tab = 'general';
        }

        $settings      = NeoNews_Membership::get_settings();
        $premium_count = NeoNews_Membership_Levels::get_premium_count();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <div class="nn-membership-stats" style="background:#fff;padding:20px;margin-bottom:20px;border:1px solid #ccd0d4;border-radius:4px;">
                <p><strong><?php esc_html_e( 'Premium members:', 'neonews-membership' ); ?></strong> <?php echo esc_html( number_format_i18n( $premium_count ) ); ?></p>
            </div>

            <nav class="nav-tab-wrapper">
                <?php foreach ( $tabs as $slug => $label ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-membership&tab=' . $slug ) ); ?>" class="nav-tab<?php echo $tab === $slug ? ' nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
                <?php endforeach; ?>
            </nav>

            <?php if ( 'keys' === $tab ) : ?>
                <?php $this->render_keys_tab(); ?>
            <?php elseif ( 'members' === $tab ) : ?>
                <?php $this->render_members_tab(); ?>
            <?php else : ?>
                <form action="options.php" method="post" style="margin-top:20px;">
                    <?php settings_fields( 'neonews_membership_group' ); ?>
                    <?php
                    switch ( $tab ) {
                        case 'badges':
                            $this->render_badges_fields( $settings );
                            break;
                        case 'plan':
                            $this->render_plan_fields( $settings );
                            break;
                        case 'content':
                            $this->render_content_fields( $settings );
                            break;
                        case 'popup':
                            $this->render_popup_fields( $settings );
                            break;
                        case 'exclusive':
                            $this->render_exclusive_fields( $settings );
                            break;
                        case 'payments':
                            $this->render_payments_fields( $settings );
                            break;
                        default:
                            $this->render_general_fields( $settings );
                    }
                    ?>
                    <input type="hidden" name="neonews_membership_settings[membership_settings_tab]" value="<?php echo esc_attr( $tab ); ?>" />
                    <?php submit_button(); ?>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_general_fields( $s ) {
        if ( isset( $_GET['exclusive_ready'] ) ) {
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Exclusive page created/updated and navigation synced.', 'neonews-membership' ) . '</p></div>';
        }
        if ( isset( $_GET['premium_seeded'] ) ) {
            $count = isset( $_GET['count'] ) ? absint( $_GET['count'] ) : 0;
            echo '<div class="notice notice-success"><p>' . esc_html( sprintf( _n( '%d post marked as premium.', '%d posts marked as premium.', $count, 'neonews-membership' ), $count ) ) . '</p></div>';
        }
        ?>
        <table class="form-table">
            <tr><th><?php esc_html_e( 'Enable paywall', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[enable_paywall]" value="1" <?php checked( ! empty( $s['enable_paywall'] ) ); ?> /> <?php esc_html_e( 'Restrict premium posts', 'neonews-membership' ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Hide ads for premium', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[hide_ads_for_premium]" value="1" <?php checked( ! empty( $s['hide_ads_for_premium'] ) ); ?> /> <?php esc_html_e( 'Premium members see no ads', 'neonews-membership' ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Free article limit', 'neonews-membership' ); ?></th><td><input type="number" name="neonews_membership_settings[free_article_limit]" value="<?php echo esc_attr( $s['free_article_limit'] ); ?>" min="0" class="small-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Free period', 'neonews-membership' ); ?></th><td><select name="neonews_membership_settings[free_article_period]"><option value="day" <?php selected( $s['free_article_period'], 'day' ); ?>><?php esc_html_e( 'Day', 'neonews-membership' ); ?></option><option value="week" <?php selected( $s['free_article_period'], 'week' ); ?>><?php esc_html_e( 'Week', 'neonews-membership' ); ?></option><option value="month" <?php selected( $s['free_article_period'], 'month' ); ?>><?php esc_html_e( 'Month', 'neonews-membership' ); ?></option></select></td></tr>
            <tr><th><?php esc_html_e( 'Exclusive page slug', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[exclusive_page_slug]" value="<?php echo esc_attr( $s['exclusive_page_slug'] ); ?>" class="regular-text" /><p class="description"><?php esc_html_e( 'Page auto-created with Exclusive News template.', 'neonews-membership' ); ?></p></td></tr>
            <tr><th><?php esc_html_e( 'Default key duration (days)', 'neonews-membership' ); ?></th><td><input type="number" name="neonews_membership_settings[key_default_duration_days]" value="<?php echo esc_attr( $s['key_default_duration_days'] ); ?>" min="1" class="small-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Activation key prefix', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[key_prefix]" value="<?php echo esc_attr( $s['key_prefix'] ); ?>" class="small-text" maxlength="6" /></td></tr>
        </table>
        <p style="margin-top:1rem;">
            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=neonews-membership&neonews_setup_exclusive=1' ), 'neonews_setup_exclusive' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Create / Update Exclusive Page & Sync Menu', 'neonews-membership' ); ?></a>
            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=neonews-membership&neonews_seed_premium=1' ), 'neonews_seed_premium' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Seed Demo Premium Posts', 'neonews-membership' ); ?></a>
            <a href="<?php echo esc_url( NeoNews_Membership_Frontend::get_exclusive_page_url() ); ?>" class="button button-link" target="_blank"><?php esc_html_e( 'View Exclusive page', 'neonews-membership' ); ?></a>
        </p>
        <p class="description"><?php esc_html_e( 'Seed marks the 4 most recent published posts as premium and adds them to the homepage exclusive section.', 'neonews-membership' ); ?></p>
        <?php
    }

    /**
     * Premium members list with revoke.
     */
    private function render_members_tab() {
        if ( isset( $_GET['revoked'] ) ) {
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Premium access revoked.', 'neonews-membership' ) . '</p></div>';
        }

        $users = NeoNews_Membership_Levels::get_premium_users();
        ?>
        <div style="margin-top:20px;background:#fff;padding:20px;border:1px solid #ccd0d4;border-radius:4px;">
            <h2><?php esc_html_e( 'Premium members', 'neonews-membership' ); ?></h2>
            <p><?php esc_html_e( 'Revoke access immediately — removes premium badge and exclusive content access.', 'neonews-membership' ); ?></p>
            <?php if ( empty( $users ) ) : ?>
                <p><?php esc_html_e( 'No premium members yet.', 'neonews-membership' ); ?></p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead><tr><th><?php esc_html_e( 'User', 'neonews-membership' ); ?></th><th><?php esc_html_e( 'Email', 'neonews-membership' ); ?></th><th><?php esc_html_e( 'Expires', 'neonews-membership' ); ?></th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ( $users as $user ) : ?>
                        <?php $expiry = NeoNews_Membership_Levels::get_premium_expiry( $user->ID ); ?>
                        <tr>
                            <td><a href="<?php echo esc_url( get_edit_user_link( $user->ID ) ); ?>"><?php echo esc_html( $user->display_name ); ?></a></td>
                            <td><?php echo esc_html( $user->user_email ); ?></td>
                            <td><?php echo $expiry ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expiry ) ) ) : esc_html__( 'Lifetime', 'neonews-membership' ); ?></td>
                            <td><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=neonews-membership&tab=members&revoke_user=' . $user->ID ), 'neonews_revoke_user_' . $user->ID ) ); ?>" class="button button-small" onclick="return confirm('<?php echo esc_js( __( 'Revoke premium access for this user?', 'neonews-membership' ) ); ?>');"><?php esc_html_e( 'Revoke', 'neonews-membership' ); ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Admin GET actions — exclusive page setup and revoke user.
     */
    public function handle_admin_actions() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( isset( $_GET['neonews_setup_exclusive'] ) && check_admin_referer( 'neonews_setup_exclusive' ) ) {
            neonews_membership_ensure_exclusive_page();
            if ( function_exists( 'neonews_sync_primary_nav_menus' ) ) {
                neonews_sync_primary_nav_menus();
            }
            wp_safe_redirect( add_query_arg( array( 'page' => 'neonews-membership', 'exclusive_ready' => '1' ), admin_url( 'admin.php' ) ) );
            exit;
        }

        if ( isset( $_GET['neonews_seed_premium'] ) && check_admin_referer( 'neonews_seed_premium' ) ) {
            $count = function_exists( 'neonews_membership_seed_premium_posts' ) ? neonews_membership_seed_premium_posts( 4 ) : 0;
            update_option( 'neonews_premium_demo_seeded', 1 );
            wp_safe_redirect(
                add_query_arg(
                    array(
                        'page'           => 'neonews-membership',
                        'premium_seeded' => '1',
                        'count'          => $count,
                    ),
                    admin_url( 'admin.php' )
                )
            );
            exit;
        }

        if ( isset( $_GET['neonews_toggle_premium'], $_GET['post_id'] ) ) {
            $post_id = absint( $_GET['post_id'] );
            if ( ! $post_id || ! check_admin_referer( 'neonews_toggle_premium_' . $post_id ) ) {
                return;
            }
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }

            if ( NeoNews_Premium_Content::is_premium_post( $post_id ) ) {
                update_post_meta( $post_id, '_neonews_premium_post', '' );
                update_post_meta( $post_id, '_neonews_premium_on_home', '' );
            } else {
                update_post_meta( $post_id, '_neonews_premium_post', '1' );
                update_post_meta( $post_id, '_neonews_premium_on_home', '1' );
            }

            wp_safe_redirect( remove_query_arg( array( 'neonews_toggle_premium', 'post_id', '_wpnonce' ), wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php' ) ) );
            exit;
        }

        if ( isset( $_GET['revoke_user'] ) && check_admin_referer( 'neonews_revoke_user_' . absint( $_GET['revoke_user'] ) ) ) {
            NeoNews_Membership_Levels::remove_premium_status( absint( $_GET['revoke_user'] ) );
            wp_safe_redirect( add_query_arg( array( 'page' => 'neonews-membership', 'tab' => 'members', 'revoked' => '1' ), admin_url( 'admin.php' ) ) );
            exit;
        }
    }

    /**
     * Render media upload field for avatar badge PNG.
     *
     * @param string $field Setting key.
     * @param int    $attachment_id Attachment ID.
     * @param string $label Field label.
     */
    private function render_badge_image_field( $field, $attachment_id, $label ) {
        $url = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'thumbnail' ) : '';
        ?>
        <div class="nn-membership-media-field">
            <div class="nn-membership-media-preview">
                <?php if ( $url ) : ?>
                    <img src="<?php echo esc_url( $url ); ?>" alt="" />
                <?php endif; ?>
            </div>
            <div class="nn-membership-media-actions">
                <input type="hidden" name="neonews_membership_settings[<?php echo esc_attr( $field ); ?>]" id="nn-membership-<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( $attachment_id ); ?>" />
                <button type="button" class="button nn-membership-media-select" data-target="nn-membership-<?php echo esc_attr( $field ); ?>"><?php echo esc_html( $label ); ?></button>
                <button type="button" class="button-link nn-membership-media-clear" data-target="nn-membership-<?php echo esc_attr( $field ); ?>"><?php esc_html_e( 'Remove image', 'neonews-membership' ); ?></button>
            </div>
        </div>
        <?php
    }

    private function render_badges_fields( $s ) {
        ?>
        <table class="form-table">
            <tr><th><?php esc_html_e( 'Show badge on avatar', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[show_badge_on_avatar]" value="1" <?php checked( ! empty( $s['show_badge_on_avatar'] ) ); ?> /></label><p class="description"><?php esc_html_e( 'Displays a small badge on the header profile icon and profile photo.', 'neonews-membership' ); ?></p></td></tr>
            <tr>
                <th><?php esc_html_e( 'Premium badge image', 'neonews-membership' ); ?></th>
                <td>
                    <?php $this->render_badge_image_field( 'premium_badge_image_id', absint( $s['premium_badge_image_id'] ?? 0 ), __( 'Upload PNG', 'neonews-membership' ) ); ?>
                    <p class="description"><?php esc_html_e( 'Optional PNG shown on premium member avatars. Recommended: square transparent PNG, about 64×64px.', 'neonews-membership' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Basic badge image', 'neonews-membership' ); ?></th>
                <td>
                    <?php $this->render_badge_image_field( 'basic_badge_image_id', absint( $s['basic_badge_image_id'] ?? 0 ), __( 'Upload PNG', 'neonews-membership' ) ); ?>
                    <p class="description"><?php esc_html_e( 'Optional PNG for basic (non-premium) members. Leave empty to use the default icon.', 'neonews-membership' ); ?></p>
                </td>
            </tr>
            <tr><th><?php esc_html_e( 'Show tag on Profile', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[show_tag_profile]" value="1" <?php checked( ! empty( $s['show_tag_profile'] ) ); ?> /></label></td></tr>
            <tr><th><?php esc_html_e( 'Show tag on My Activity', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[show_tag_account]" value="1" <?php checked( ! empty( $s['show_tag_account'] ) ); ?> /></label></td></tr>
            <tr><th><?php esc_html_e( 'Show badge in comments', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[show_badge_comments]" value="1" <?php checked( ! empty( $s['show_badge_comments'] ) ); ?> /></label></td></tr>
            <tr><th><?php esc_html_e( 'Basic badge label', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[basic_badge_label]" value="<?php echo esc_attr( $s['basic_badge_label'] ); ?>" class="regular-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Basic tag label', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[basic_tag_label]" value="<?php echo esc_attr( $s['basic_tag_label'] ); ?>" class="regular-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Premium badge label', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[premium_badge_label]" value="<?php echo esc_attr( $s['premium_badge_label'] ); ?>" class="regular-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Premium tag label', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[premium_tag_label]" value="<?php echo esc_attr( $s['premium_tag_label'] ); ?>" class="regular-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Expiry reminder (days)', 'neonews-membership' ); ?></th><td><input type="number" name="neonews_membership_settings[expiry_reminder_days]" value="<?php echo esc_attr( $s['expiry_reminder_days'] ); ?>" min="1" class="small-text" /><p class="description"><?php esc_html_e( 'Show timer when this many days or fewer remain.', 'neonews-membership' ); ?></p></td></tr>
            <tr><th><?php esc_html_e( 'Expiry timer on Profile', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[show_expiry_timer_profile]" value="1" <?php checked( ! empty( $s['show_expiry_timer_profile'] ) ); ?> /></label></td></tr>
            <tr><th><?php esc_html_e( 'Expiry timer on My Activity', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[show_expiry_timer_account]" value="1" <?php checked( ! empty( $s['show_expiry_timer_account'] ) ); ?> /></label></td></tr>
            <tr><th><?php esc_html_e( 'Red warning on avatar badge', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[show_expiry_icon_header]" value="1" <?php checked( ! empty( $s['show_expiry_icon_header'] ) ); ?> /></label></td></tr>
        </table>
        <?php
    }

    private function render_plan_fields( $s ) {
        ?>
        <table class="form-table">
            <tr><th><?php esc_html_e( 'Enrollment note', 'neonews-membership' ); ?></th><td><textarea name="neonews_membership_settings[enroll_note]" rows="3" class="large-text"><?php echo esc_textarea( $s['enroll_note'] ); ?></textarea></td></tr>
            <tr><th><?php esc_html_e( 'Plan title', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[plan_title]" value="<?php echo esc_attr( $s['plan_title'] ); ?>" class="regular-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Plan price text', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[plan_price]" value="<?php echo esc_attr( $s['plan_price'] ); ?>" class="regular-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Plan features (one per line)', 'neonews-membership' ); ?></th><td><textarea name="neonews_membership_settings[plan_features]" rows="5" class="large-text"><?php echo esc_textarea( $s['plan_features'] ); ?></textarea></td></tr>
            <tr><th><?php esc_html_e( 'Contact email', 'neonews-membership' ); ?></th><td><input type="email" name="neonews_membership_settings[contact_email]" value="<?php echo esc_attr( $s['contact_email'] ); ?>" class="regular-text" /></td></tr>
            <tr><th><?php esc_html_e( 'WhatsApp number', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[contact_whatsapp_number]" value="<?php echo esc_attr( $s['contact_whatsapp_number'] ); ?>" class="regular-text" placeholder="15551234567" /></td></tr>
            <tr><th><?php esc_html_e( 'WhatsApp message template', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[whatsapp_enroll_message]" value="<?php echo esc_attr( $s['whatsapp_enroll_message'] ); ?>" class="large-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Email subject template', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[email_enroll_subject]" value="<?php echo esc_attr( $s['email_enroll_subject'] ); ?>" class="large-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Email body template', 'neonews-membership' ); ?></th><td><textarea name="neonews_membership_settings[email_enroll_body]" rows="4" class="large-text"><?php echo esc_textarea( $s['email_enroll_body'] ); ?></textarea></td></tr>
        </table>
        <?php
    }

    private function render_content_fields( $s ) {
        ?>
        <table class="form-table">
            <tr><th><?php esc_html_e( 'Blur lock preview', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[enable_blur_lock]" value="1" <?php checked( ! empty( $s['enable_blur_lock'] ) ); ?> /> <?php esc_html_e( 'Show blurry locked preview for basic users on premium posts', 'neonews-membership' ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Blur strength (px)', 'neonews-membership' ); ?></th><td><input type="number" name="neonews_membership_settings[lock_blur_strength]" value="<?php echo esc_attr( $s['lock_blur_strength'] ); ?>" min="2" max="16" class="small-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Paywall excerpt length', 'neonews-membership' ); ?></th><td><input type="number" name="neonews_membership_settings[premium_excerpt_length]" value="<?php echo esc_attr( $s['premium_excerpt_length'] ); ?>" min="50" class="small-text" /><p class="description"><?php esc_html_e( 'Used when blur lock is off.', 'neonews-membership' ); ?></p></td></tr>
        </table>
        <?php
    }

    private function render_exclusive_fields( $s ) {
        ?>
        <p class="description"><?php esc_html_e( 'Control how exclusive/premium posts look on the homepage section and Exclusive page.', 'neonews-membership' ); ?></p>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Card layout', 'neonews-membership' ); ?></th>
                <td>
                    <select name="neonews_membership_settings[exclusive_card_layout]">
                        <option value="rich" <?php selected( $s['exclusive_card_layout'], 'rich' ); ?>><?php esc_html_e( 'Rich — image, excerpt, meta & engagement', 'neonews-membership' ); ?></option>
                        <option value="magazine" <?php selected( $s['exclusive_card_layout'], 'magazine' ); ?>><?php esc_html_e( 'Magazine — horizontal cards', 'neonews-membership' ); ?></option>
                        <option value="compact" <?php selected( $s['exclusive_card_layout'], 'compact' ); ?>><?php esc_html_e( 'Compact — smaller tiles', 'neonews-membership' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Card style', 'neonews-membership' ); ?></th>
                <td>
                    <select name="neonews_membership_settings[exclusive_card_style]">
                        <option value="elevated" <?php selected( $s['exclusive_card_style'], 'elevated' ); ?>><?php esc_html_e( 'Elevated — shadow on hover', 'neonews-membership' ); ?></option>
                        <option value="gradient" <?php selected( $s['exclusive_card_style'], 'gradient' ); ?>><?php esc_html_e( 'Gradient — gold accent border', 'neonews-membership' ); ?></option>
                        <option value="minimal" <?php selected( $s['exclusive_card_style'], 'minimal' ); ?>><?php esc_html_e( 'Minimal — flat border', 'neonews-membership' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr><th><?php esc_html_e( 'Exclusive badge label', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[exclusive_badge_label]" value="<?php echo esc_attr( $s['exclusive_badge_label'] ); ?>" class="regular-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Excerpt length (words)', 'neonews-membership' ); ?></th><td><input type="number" name="neonews_membership_settings[exclusive_excerpt_words]" value="<?php echo esc_attr( $s['exclusive_excerpt_words'] ); ?>" min="8" max="50" class="small-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Exclusive page post count', 'neonews-membership' ); ?></th><td><input type="number" name="neonews_membership_settings[exclusive_page_count]" value="<?php echo esc_attr( $s['exclusive_page_count'] ); ?>" min="1" max="24" class="small-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Exclusive page subtitle', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[exclusive_page_subtitle]" value="<?php echo esc_attr( $s['exclusive_page_subtitle'] ); ?>" class="large-text" /></td></tr>
        </table>

        <h2><?php esc_html_e( 'Card content', 'neonews-membership' ); ?></h2>
        <table class="form-table">
            <tr><th><?php esc_html_e( 'Category chip', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[exclusive_show_category]" value="1" <?php checked( ! empty( $s['exclusive_show_category'] ) ); ?> /> <?php esc_html_e( 'Show category on image', 'neonews-membership' ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Excerpt', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[exclusive_show_excerpt]" value="1" <?php checked( ! empty( $s['exclusive_show_excerpt'] ) ); ?> /> <?php esc_html_e( 'Show post excerpt (rich & magazine layouts)', 'neonews-membership' ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Author', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[exclusive_show_author]" value="1" <?php checked( ! empty( $s['exclusive_show_author'] ) ); ?> /> <?php esc_html_e( 'Show author avatar & name', 'neonews-membership' ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Publish date', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[exclusive_show_date]" value="1" <?php checked( ! empty( $s['exclusive_show_date'] ) ); ?> /> <?php esc_html_e( 'Show publish date', 'neonews-membership' ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Reading time', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[exclusive_show_reading_time]" value="1" <?php checked( ! empty( $s['exclusive_show_reading_time'] ) ); ?> /> <?php esc_html_e( 'Show estimated read time', 'neonews-membership' ); ?></label></td></tr>
        </table>

        <h2><?php esc_html_e( 'Engagement stats', 'neonews-membership' ); ?></h2>
        <table class="form-table">
            <tr><th><?php esc_html_e( 'Engagement row', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[exclusive_show_engagement]" value="1" <?php checked( ! empty( $s['exclusive_show_engagement'] ) ); ?> /> <?php esc_html_e( 'Show engagement row on cards', 'neonews-membership' ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Views', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[exclusive_show_views]" value="1" <?php checked( ! empty( $s['exclusive_show_views'] ) ); ?> /> <?php esc_html_e( 'Show view count', 'neonews-membership' ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Views on image', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[exclusive_show_views_overlay]" value="1" <?php checked( ! empty( $s['exclusive_show_views_overlay'] ) ); ?> /> <?php esc_html_e( 'Show views badge over thumbnail', 'neonews-membership' ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Likes', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[exclusive_show_likes]" value="1" <?php checked( ! empty( $s['exclusive_show_likes'] ) ); ?> /> <?php esc_html_e( 'Show like count', 'neonews-membership' ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Comments', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[exclusive_show_comments]" value="1" <?php checked( ! empty( $s['exclusive_show_comments'] ) ); ?> /> <?php esc_html_e( 'Show comment count', 'neonews-membership' ); ?></label></td></tr>
        </table>
        <?php
    }

    private function render_popup_fields( $s ) {
        ?>
        <table class="form-table">
            <tr><th><?php esc_html_e( 'Enable upgrade popup', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[enable_upgrade_popup]" value="1" <?php checked( ! empty( $s['enable_upgrade_popup'] ) ); ?> /></label></td></tr>
            <tr><th><?php esc_html_e( 'Popup delay (seconds)', 'neonews-membership' ); ?></th><td><input type="number" name="neonews_membership_settings[popup_delay_seconds]" value="<?php echo esc_attr( $s['popup_delay_seconds'] ); ?>" min="5" class="small-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Popup interval (hours)', 'neonews-membership' ); ?></th><td><input type="number" name="neonews_membership_settings[popup_interval_hours]" value="<?php echo esc_attr( $s['popup_interval_hours'] ); ?>" min="1" class="small-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Popup title', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[popup_title]" value="<?php echo esc_attr( $s['popup_title'] ); ?>" class="large-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Popup message', 'neonews-membership' ); ?></th><td><textarea name="neonews_membership_settings[popup_message]" rows="3" class="large-text"><?php echo esc_textarea( $s['popup_message'] ); ?></textarea></td></tr>
            <tr><th><?php esc_html_e( 'Home premium section', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[show_home_premium_section]" value="1" <?php checked( ! empty( $s['show_home_premium_section'] ) ); ?> /></label></td></tr>
            <tr><th><?php esc_html_e( 'Home premium title', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[home_premium_title]" value="<?php echo esc_attr( $s['home_premium_title'] ); ?>" class="regular-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Home premium count', 'neonews-membership' ); ?></th><td><input type="number" name="neonews_membership_settings[home_premium_count]" value="<?php echo esc_attr( $s['home_premium_count'] ); ?>" min="1" max="12" class="small-text" /></td></tr>
        </table>
        <?php
    }

    private function render_payments_fields( $s ) {
        ?>
        <p><?php esc_html_e( 'Payment gateway integration scope — connect Stripe or another provider later. Manual activation keys work independently.', 'neonews-membership' ); ?></p>
        <table class="form-table">
            <tr><th><?php esc_html_e( 'Enable payment gateway', 'neonews-membership' ); ?></th><td><label><input type="checkbox" name="neonews_membership_settings[payment_gateway_enabled]" value="1" <?php checked( ! empty( $s['payment_gateway_enabled'] ) ); ?> /> <?php esc_html_e( 'Reserved for future automated checkout', 'neonews-membership' ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Stripe mode', 'neonews-membership' ); ?></th><td><select name="neonews_membership_settings[stripe_mode]"><option value="test" <?php selected( $s['stripe_mode'], 'test' ); ?>>Test</option><option value="live" <?php selected( $s['stripe_mode'], 'live' ); ?>>Live</option></select></td></tr>
            <tr><th><?php esc_html_e( 'Test public key', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[stripe_test_public_key]" value="<?php echo esc_attr( $s['stripe_test_public_key'] ); ?>" class="large-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Test secret key', 'neonews-membership' ); ?></th><td><input type="password" name="neonews_membership_settings[stripe_test_secret_key]" value="<?php echo esc_attr( $s['stripe_test_secret_key'] ); ?>" class="large-text" autocomplete="new-password" /></td></tr>
            <tr><th><?php esc_html_e( 'Live public key', 'neonews-membership' ); ?></th><td><input type="text" name="neonews_membership_settings[stripe_live_public_key]" value="<?php echo esc_attr( $s['stripe_live_public_key'] ); ?>" class="large-text" /></td></tr>
            <tr><th><?php esc_html_e( 'Live secret key', 'neonews-membership' ); ?></th><td><input type="password" name="neonews_membership_settings[stripe_live_secret_key]" value="<?php echo esc_attr( $s['stripe_live_secret_key'] ); ?>" class="large-text" autocomplete="new-password" /></td></tr>
        </table>
        <?php
    }

    private function render_keys_tab() {
        $settings  = NeoNews_Membership::get_settings();
        $generated = get_transient( 'neonews_generated_keys' );
        if ( isset( $_GET['generated'] ) && $generated ) {
            echo '<div class="notice notice-success"><p><strong>' . esc_html__( 'Generated keys:', 'neonews-membership' ) . '</strong> ';
            echo esc_html( implode( ', ', $generated ) );
            echo '</p></div>';
            delete_transient( 'neonews_generated_keys' );
        }
        ?>
        <div style="margin-top:20px;background:#fff;padding:20px;border:1px solid #ccd0d4;border-radius:4px;max-width:720px;">
            <h2><?php esc_html_e( 'Generate activation keys', 'neonews-membership' ); ?></h2>
            <form method="post">
                <?php wp_nonce_field( 'neonews_generate_key' ); ?>
                <p><label><?php esc_html_e( 'Premium duration (days)', 'neonews-membership' ); ?><br />
                <input type="number" name="key_duration_days" value="<?php echo esc_attr( $settings['key_default_duration_days'] ); ?>" min="1" class="small-text" /></label></p>
                <p><label><?php esc_html_e( 'Key valid for (days, 0 = never expires)', 'neonews-membership' ); ?><br />
                <input type="number" name="key_valid_days" value="0" min="0" class="small-text" /></label></p>
                <p><label><?php esc_html_e( 'Batch label (optional)', 'neonews-membership' ); ?><br />
                <input type="text" name="key_batch_label" class="regular-text" /></label></p>
                <p><label><?php esc_html_e( 'Number of keys', 'neonews-membership' ); ?><br />
                <input type="number" name="key_generate_count" value="1" min="1" max="20" class="small-text" /></label></p>
                <p><label><?php esc_html_e( 'Default key prefix', 'neonews-membership' ); ?> — <?php echo esc_html( $settings['key_prefix'] ); ?> (change in General tab via settings save on another tab, or add to keys settings)</label></p>
                <button type="submit" name="neonews_generate_key" class="button button-primary"><?php esc_html_e( 'Generate Keys', 'neonews-membership' ); ?></button>
            </form>
        </div>

        <h2 style="margin-top:2rem;"><?php esc_html_e( 'Recent keys', 'neonews-membership' ); ?></h2>
        <table class="widefat striped">
            <thead><tr><th><?php esc_html_e( 'Key', 'neonews-membership' ); ?></th><th><?php esc_html_e( 'Days', 'neonews-membership' ); ?></th><th><?php esc_html_e( 'Status', 'neonews-membership' ); ?></th><th><?php esc_html_e( 'Used by', 'neonews-membership' ); ?></th><th></th></tr></thead>
            <tbody>
            <?php foreach ( NeoNews_Activation_Keys::get_recent_keys( 30 ) as $row ) : ?>
                <tr>
                    <td><code><?php echo esc_html( $row->key_code ); ?></code></td>
                    <td><?php echo esc_html( $row->duration_days ); ?></td>
                    <td><?php echo esc_html( $row->status ); ?></td>
                    <td><?php echo $row->used_by ? esc_html( get_userdata( $row->used_by )->display_name ?? '#' . $row->used_by ) : '—'; ?></td>
                    <td><?php if ( 'active' === $row->status ) : ?><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'neonews-membership', 'tab' => 'keys', 'revoke_key' => $row->id ), admin_url( 'admin.php' ) ), 'neonews_revoke_key_' . $row->id ) ); ?>"><?php esc_html_e( 'Revoke', 'neonews-membership' ); ?></a><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    public function add_meta_boxes() {
        add_meta_box(
            'neonews_premium_post',
            __( 'Premium Content', 'neonews-membership' ),
            array( $this, 'render_meta_box' ),
            'post',
            'side',
            'high'
        );
    }

    public function render_meta_box( $post ) {
        wp_nonce_field( 'neonews_save_premium_meta', 'neonews_premium_meta_nonce' );
        $is_premium = get_post_meta( $post->ID, '_neonews_premium_post', true );
        $on_home    = get_post_meta( $post->ID, '_neonews_premium_on_home', true );
        ?>
        <p><label><input type="checkbox" name="_neonews_premium_post" value="1" <?php checked( $is_premium, '1' ); ?> /> <?php esc_html_e( 'Premium / exclusive post', 'neonews-membership' ); ?></label></p>
        <p><label><input type="checkbox" name="_neonews_premium_on_home" value="1" <?php checked( $on_home, '1' ); ?> /> <?php esc_html_e( 'Show in homepage Premium Picks', 'neonews-membership' ); ?></label></p>
        <p class="description"><?php esc_html_e( 'Basic users see a locked blurry preview. Premium members get full access.', 'neonews-membership' ); ?></p>
        <?php
    }

    public function save_meta( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || 'post' !== $post->post_type ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        if ( ! isset( $_POST['neonews_premium_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['neonews_premium_meta_nonce'] ) ), 'neonews_save_premium_meta' ) ) {
            return;
        }
        update_post_meta( $post_id, '_neonews_premium_post', isset( $_POST['_neonews_premium_post'] ) ? '1' : '' );
        update_post_meta( $post_id, '_neonews_premium_on_home', isset( $_POST['_neonews_premium_on_home'] ) ? '1' : '' );
    }

    public function add_premium_column( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;
            if ( 'title' === $key ) {
                $new_columns['nn_premium'] = __( 'Premium', 'neonews-membership' );
            }
        }
        return $new_columns;
    }

    public function render_premium_column( $column, $post_id ) {
        if ( 'nn_premium' !== $column ) {
            return;
        }

        $is_premium = NeoNews_Premium_Content::is_premium_post( $post_id );
        $toggle_url = wp_nonce_url(
            add_query_arg(
                array(
                    'neonews_toggle_premium' => 1,
                    'post_id'                => $post_id,
                ),
                admin_url( 'edit.php' )
            ),
            'neonews_toggle_premium_' . $post_id
        );

        if ( $is_premium ) {
            echo '<a href="' . esc_url( $toggle_url ) . '" class="nn-premium-toggle nn-premium-toggle-on" title="' . esc_attr__( 'Click to remove premium', 'neonews-membership' ) . '">';
            echo '<span class="dashicons dashicons-lock" aria-hidden="true"></span>';
            echo esc_html__( 'Premium', 'neonews-membership' );
            if ( '1' === get_post_meta( $post_id, '_neonews_premium_on_home', true ) ) {
                echo ' <span aria-hidden="true" title="' . esc_attr__( 'Shown on homepage', 'neonews-membership' ) . '">★</span>';
            }
            echo '</a>';
            return;
        }

        echo '<a href="' . esc_url( $toggle_url ) . '" class="nn-premium-toggle nn-premium-toggle-off" title="' . esc_attr__( 'Click to mark as premium', 'neonews-membership' ) . '">';
        echo '<span class="dashicons dashicons-unlock" aria-hidden="true"></span>';
        echo esc_html__( 'Free', 'neonews-membership' );
        echo '</a>';
    }
}
