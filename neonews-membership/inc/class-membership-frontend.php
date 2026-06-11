<?php
/**
 * Membership frontend UI — enrollment, keys, popup, exclusive content.
 *
 * @package NeoNews_Membership
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Frontend membership features.
 */
class NeoNews_Membership_Frontend {

    /**
     * @return self
     */
    public static function get_instance() {
        static $instance = null;
        if ( null === $instance ) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'template_redirect', array( $this, 'handle_key_redemption' ) );
        add_action( 'wp_footer', array( $this, 'render_upgrade_popup' ) );
        add_action( 'neonews_profile_membership_panel', array( $this, 'render_profile_membership_panel' ) );
        add_action( 'neonews_account_membership_panel', array( $this, 'render_account_membership_panel' ) );
        add_action( 'neonews_home_after_hero', array( $this, 'render_home_premium_section' ), 15 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 30 );
        add_filter( 'post_class', array( $this, 'premium_post_class' ), 10, 3 );
        add_filter( 'the_posts', array( $this, 'mark_locked_archive_posts' ), 10, 2 );
    }

    /**
     * Enqueue membership JS.
     */
    public function enqueue_scripts() {
        if ( ! function_exists( 'neonews_needs_membership_assets' ) || ! neonews_needs_membership_assets() ) {
            return;
        }

        wp_enqueue_script(
            'neonews-membership',
            NEONEWS_MEMBERSHIP_URL . 'public/js/membership.js',
            array(),
            NEONEWS_MEMBERSHIP_VERSION,
            true
        );

        $settings = NeoNews_Membership::get_settings();
        wp_localize_script(
            'neonews-membership',
            'neonewsMembership',
            array(
                'popupEnabled'   => ! empty( $settings['enable_upgrade_popup'] ),
                'popupDelay'     => absint( $settings['popup_delay_seconds'] ) * 1000,
                'popupInterval'  => absint( $settings['popup_interval_hours'] ) * 3600,
                'isPremium'      => NeoNews_Membership_Levels::is_premium(),
                'isLoggedIn'     => is_user_logged_in(),
                'popupTitle'     => $settings['popup_title'],
                'popupMessage'   => $settings['popup_message'],
                'upgradeUrl'     => self::get_enroll_url(),
                'dismissLabel'   => __( 'Not now', 'neonews-membership' ),
                'upgradeLabel'   => __( 'Become Premium', 'neonews-membership' ),
            )
        );
    }

    /**
     * Handle activation key POST on profile page.
     */
    public function handle_key_redemption() {
        if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
            return;
        }

        if ( ! is_user_logged_in() || empty( $_POST['neonews_activation_key_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['neonews_activation_key_nonce'] ) ), 'neonews_redeem_activation_key' ) ) {
            return;
        }

        $profile_page = get_page_by_path( 'my-profile' );
        $redirect     = $profile_page ? get_permalink( $profile_page ) : neonews_get_profile_url();

        if ( empty( $_POST['activation_key'] ) ) {
            wp_safe_redirect( add_query_arg( 'key_error', 'empty', $redirect ) );
            exit;
        }

        $result = NeoNews_Activation_Keys::redeem_key(
            sanitize_text_field( wp_unslash( $_POST['activation_key'] ) ),
            get_current_user_id()
        );

        if ( is_wp_error( $result ) ) {
            wp_safe_redirect( add_query_arg( 'key_error', rawurlencode( $result->get_error_code() ), $redirect ) );
            exit;
        }

        wp_safe_redirect( add_query_arg( 'key_success', '1', $redirect ) );
        exit;
    }

    /**
     * Profile membership panel with key redemption.
     */
    public function render_profile_membership_panel() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        $this->render_key_messages();
        NeoNews_Membership_Display::render_expiry_timer( 'profile' );

        if ( NeoNews_Membership_Levels::is_premium() ) {
            $this->render_premium_status_card();
            return;
        }

        $this->render_enroll_section( 'profile' );
    }

    /**
     * Account membership panel.
     */
    public function render_account_membership_panel() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        NeoNews_Membership_Display::render_expiry_timer( 'account' );

        if ( NeoNews_Membership_Levels::is_premium() ) {
            $this->render_premium_status_card( true );
            return;
        }

        $this->render_enroll_section( 'account' );
    }

    /**
     * Key redemption success/error messages.
     */
    private function render_key_messages() {
        if ( isset( $_GET['key_success'] ) ) {
            echo '<div class="nn-toast nn-toast-success nn-toast-visible">' . esc_html__( 'Premium activated successfully! Enjoy exclusive content.', 'neonews-membership' ) . '</div>';
        }

        if ( isset( $_GET['key_error'] ) ) {
            $code = sanitize_key( wp_unslash( $_GET['key_error'] ) );
            $messages = array(
                'invalid_key' => __( 'Invalid activation key. Please check and try again.', 'neonews-membership' ),
                'key_used'    => __( 'This activation key has already been used or revoked.', 'neonews-membership' ),
                'key_expired' => __( 'This activation key has expired.', 'neonews-membership' ),
                'too_many_attempts' => __( 'Too many failed attempts. Please wait 15 minutes and try again.', 'neonews-membership' ),
                'empty'       => __( 'Please enter an activation key.', 'neonews-membership' ),
            );
            $message = $messages[ $code ] ?? __( 'Could not activate premium. Please try again.', 'neonews-membership' );
            echo '<div class="nn-form-error nn-profile-error">' . esc_html( $message ) . '</div>';
        }
    }

    /**
     * @param bool $compact Compact layout.
     */
    private function render_premium_status_card( $compact = false ) {
        $settings = NeoNews_Membership::get_settings();
        $expiry   = NeoNews_Membership_Levels::get_premium_expiry();
        ?>
        <div class="nn-page-card nn-membership-status-card<?php echo $compact ? ' nn-membership-status-compact' : ''; ?>">
            <h2><?php echo esc_html( $settings['premium_tag_label'] ); ?></h2>
            <p><?php esc_html_e( 'You have full access to exclusive stories and premium benefits.', 'neonews-membership' ); ?></p>
            <?php if ( $expiry ) : ?>
                <p class="nn-membership-valid-until">
                    <?php
                    printf(
                        /* translators: %s: expiry date */
                        esc_html__( 'Valid until: %s', 'neonews-membership' ),
                        esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expiry ) ) )
                    );
                    ?>
                </p>
            <?php else : ?>
                <p class="nn-membership-valid-until"><?php esc_html_e( 'Lifetime premium access', 'neonews-membership' ); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * @param string $context profile|account.
     */
    private function render_enroll_section( $context ) {
        $settings = NeoNews_Membership::get_settings();
        $email    = $settings['contact_email'] ? $settings['contact_email'] : get_option( 'admin_email' );
        $wa_num   = preg_replace( '/[^0-9]/', '', (string) $settings['contact_whatsapp_number'] );
        if ( ! $wa_num && function_exists( 'neonews_get_whatsapp_number' ) ) {
            $wa_num = neonews_get_whatsapp_number();
        }
        $wa_msg = $settings['whatsapp_enroll_message'];
        $wa_url = $wa_num ? 'https://wa.me/' . $wa_num . '?text=' . rawurlencode( $wa_msg ) : '';

        $mailto = 'mailto:' . rawurlencode( $email )
            . '?subject=' . rawurlencode( $settings['email_enroll_subject'] )
            . '&body=' . rawurlencode( $settings['email_enroll_body'] );
        ?>
        <div class="nn-page-card nn-membership-enroll" id="nn-membership-enroll">
            <h2><?php esc_html_e( 'Enroll to Premium', 'neonews-membership' ); ?></h2>
            <p class="nn-membership-enroll-note"><?php echo esc_html( $settings['enroll_note'] ); ?></p>

            <div class="nn-membership-plan-card">
                <h3><?php echo esc_html( $settings['plan_title'] ); ?></h3>
                <?php if ( ! empty( $settings['plan_price'] ) ) : ?>
                    <p class="nn-membership-plan-price"><?php echo esc_html( $settings['plan_price'] ); ?></p>
                <?php endif; ?>
                <?php if ( ! empty( $settings['plan_features'] ) ) : ?>
                    <ul class="nn-membership-plan-features">
                        <?php foreach ( preg_split( '/[\r\n]+/', (string) $settings['plan_features'] ) as $feature ) : ?>
                            <?php $feature = trim( $feature ); ?>
                            <?php if ( $feature ) : ?>
                                <li><?php echo esc_html( $feature ); ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="nn-membership-contact-actions">
                <?php if ( $wa_url ) : ?>
                    <a href="<?php echo esc_url( $wa_url ); ?>" class="nn-btn nn-btn-whatsapp" target="_blank" rel="noopener noreferrer">
                        <?php esc_html_e( 'Contact on WhatsApp', 'neonews-membership' ); ?>
                    </a>
                <?php endif; ?>
                <a href="<?php echo esc_url( $mailto ); ?>" class="nn-btn nn-btn-outline">
                    <?php esc_html_e( 'Email our team', 'neonews-membership' ); ?>
                </a>
            </div>

            <?php if ( 'profile' === $context ) : ?>
                <form method="post" class="nn-membership-key-form">
                    <?php wp_nonce_field( 'neonews_redeem_activation_key', 'neonews_activation_key_nonce' ); ?>
                    <label for="nn-activation-key"><?php esc_html_e( 'Activation key', 'neonews-membership' ); ?></label>
                    <input type="text" id="nn-activation-key" name="activation_key" class="nn-form-input" placeholder="<?php esc_attr_e( 'Enter your premium activation key', 'neonews-membership' ); ?>" autocomplete="off" />
                    <button type="submit" class="nn-btn"><?php esc_html_e( 'Activate Premium', 'neonews-membership' ); ?></button>
                </form>
            <?php else : ?>
                <p class="nn-membership-key-hint">
                    <?php
                    printf(
                        /* translators: %s: profile page link */
                        esc_html__( 'Have an activation key? Redeem it on your %s.', 'neonews-membership' ),
                        '<a href="' . esc_url( self::get_profile_url() ) . '">' . esc_html__( 'Profile page', 'neonews-membership' ) . '</a>'
                    );
                    ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Upgrade popup for non-premium users.
     */
    public function render_upgrade_popup() {
        if ( is_admin() || NeoNews_Membership_Levels::is_premium() ) {
            return;
        }

        if ( empty( NeoNews_Membership::get_settings( 'enable_upgrade_popup' ) ) ) {
            return;
        }

        $settings = NeoNews_Membership::get_settings();
        ?>
        <div id="nn-membership-popup" class="nn-membership-popup" hidden aria-hidden="true">
            <div class="nn-membership-popup-backdrop" data-close-popup></div>
            <div class="nn-membership-popup-dialog" role="dialog" aria-modal="true" aria-labelledby="nn-membership-popup-title">
                <button type="button" class="nn-membership-popup-close" data-close-popup aria-label="<?php esc_attr_e( 'Close', 'neonews-membership' ); ?>">×</button>
                <div class="nn-membership-popup-icon">⭐</div>
                <h3 id="nn-membership-popup-title"><?php echo esc_html( $settings['popup_title'] ); ?></h3>
                <p><?php echo esc_html( $settings['popup_message'] ); ?></p>
                <div class="nn-membership-popup-actions">
                    <a href="<?php echo esc_url( self::get_enroll_url() ); ?>" class="nn-btn"><?php esc_html_e( 'Become Premium', 'neonews-membership' ); ?></a>
                    <button type="button" class="nn-btn nn-btn-outline" data-close-popup><?php esc_html_e( 'Not now', 'neonews-membership' ); ?></button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Homepage premium picks section.
     */
    public function render_home_premium_section() {
        if ( empty( NeoNews_Membership::get_settings( 'show_home_premium_section' ) ) ) {
            return;
        }

        $count  = absint( NeoNews_Membership::get_settings( 'home_premium_count' ) );
        $query  = self::get_premium_posts_query( $count, true );
        if ( ! $query->have_posts() ) {
            $query = self::get_premium_posts_query( $count, false );
        }
        if ( ! $query->have_posts() ) {
            return;
        }

        $settings      = NeoNews_Membership::get_settings();
        $title         = $settings['home_premium_title'];
        $is_logged_in  = is_user_logged_in();
        $is_premium    = NeoNews_Membership_Levels::is_premium();
        if ( ! $title ) {
            $title = __( 'Exclusive Content', 'neonews-membership' );
        }
        $exclusive_url = self::get_exclusive_page_url();
        ?>
        <section class="nn-container nn-home-premium nn-home-exclusive nn-reveal<?php echo $is_premium ? ' nn-home-exclusive-unlocked' : ' nn-home-exclusive-locked'; ?>">
            <header class="nn-section-head">
                <h2><span class="nn-nav-star" aria-hidden="true">★</span> <?php echo esc_html( $title ); ?></h2>
                <?php if ( $exclusive_url ) : ?>
                    <a href="<?php echo esc_url( $exclusive_url ); ?>" class="nn-section-link"><?php esc_html_e( 'View all exclusive →', 'neonews-membership' ); ?></a>
                <?php endif; ?>
            </header>
            <?php if ( ! $is_logged_in ) : ?>
                <p class="nn-home-exclusive-notice">
                    <?php esc_html_e( 'Sign in to preview exclusive stories. Premium members unlock full access.', 'neonews-membership' ); ?>
                    <button type="button" class="nn-btn nn-btn-sm nn-open-login"><?php esc_html_e( 'Sign In', 'neonews' ); ?></button>
                </p>
            <?php elseif ( ! $is_premium ) : ?>
                <p class="nn-home-exclusive-notice">
                    <?php esc_html_e( 'You are signed in as a basic member. Upgrade to premium to read exclusive stories without limits.', 'neonews-membership' ); ?>
                    <a href="<?php echo esc_url( self::get_enroll_url() ); ?>" class="nn-btn nn-btn-sm"><?php esc_html_e( 'Upgrade', 'neonews-membership' ); ?></a>
                </p>
            <?php else : ?>
                <p class="nn-home-exclusive-notice nn-home-exclusive-notice-premium"><?php esc_html_e( 'Your premium picks — full access unlocked.', 'neonews-membership' ); ?></p>
            <?php endif; ?>
            <div class="nn-home-premium-grid <?php echo esc_attr( self::get_exclusive_grid_class() ); ?>">
                <?php
                while ( $query->have_posts() ) :
                    $query->the_post();
                    self::render_premium_card( get_the_ID() );
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        </section>
        <?php
    }

    /**
     * Grid modifier class from exclusive display settings.
     *
     * @return string
     */
    public static function get_exclusive_grid_class() {
        $layout = sanitize_key( NeoNews_Membership::get_settings( 'exclusive_card_layout' ) ?: 'rich' );
        if ( ! in_array( $layout, array( 'rich', 'compact', 'magazine' ), true ) ) {
            $layout = 'rich';
        }
        $style = sanitize_key( NeoNews_Membership::get_settings( 'exclusive_card_style' ) ?: 'elevated' );
        if ( ! in_array( $style, array( 'elevated', 'minimal', 'gradient' ), true ) ) {
            $style = 'elevated';
        }
        return 'nn-exclusive-grid--' . $layout . ' nn-exclusive-style--' . $style;
    }

    /**
     * Whether an exclusive card field should render.
     *
     * @param string $key Setting key without prefix.
     * @return bool
     */
    public static function exclusive_card_show( $key ) {
        return ! empty( NeoNews_Membership::get_settings( 'exclusive_show_' . $key ) );
    }

    /**
     * @param int $post_id Post ID.
     */
    public static function render_exclusive_engagement( $post_id ) {
        if ( ! self::exclusive_card_show( 'engagement' ) ) {
            return;
        }

        $show_views    = self::exclusive_card_show( 'views' );
        $show_likes    = self::exclusive_card_show( 'likes' );
        $show_comments = self::exclusive_card_show( 'comments' );

        if ( ! $show_views && ! $show_likes && ! $show_comments ) {
            return;
        }

        $views    = function_exists( 'neonews_get_post_views' ) ? neonews_get_post_views( $post_id ) : 0;
        $comments = get_comments_number( $post_id );
        $likes    = (int) get_post_meta( $post_id, '_neonews_likes', true );
        $liked    = is_user_logged_in() && function_exists( 'neonews_user_liked_post' ) && neonews_user_liked_post( $post_id );
        ?>
        <div class="nn-exclusive-engage">
            <?php if ( $show_views ) : ?>
                <span class="nn-exclusive-engage-item" title="<?php esc_attr_e( 'Views', 'neonews-membership' ); ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <?php echo esc_html( function_exists( 'neonews_format_views' ) ? neonews_format_views( $views ) : number_format_i18n( $views ) ); ?>
                </span>
            <?php endif; ?>
            <?php if ( $show_comments ) : ?>
                <span class="nn-exclusive-engage-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    <?php echo esc_html( number_format_i18n( $comments ) ); ?>
                </span>
            <?php endif; ?>
            <?php if ( $show_likes ) : ?>
                <span class="nn-exclusive-engage-item<?php echo $liked ? ' nn-exclusive-engage-liked' : ''; ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="<?php echo $liked ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    <?php echo esc_html( number_format_i18n( $likes ) ); ?>
                </span>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Author / date meta row for exclusive cards.
     *
     * @param int $post_id Post ID.
     */
    public static function render_exclusive_meta_row( $post_id ) {
        $show_author = self::exclusive_card_show( 'author' );
        $show_date   = self::exclusive_card_show( 'date' );
        $show_time   = self::exclusive_card_show( 'reading_time' );

        if ( ! $show_author && ! $show_date && ! $show_time ) {
            return;
        }

        $author_id = (int) get_post_field( 'post_author', $post_id );
        ?>
        <div class="nn-premium-card-meta">
            <?php if ( $show_author ) : ?>
                <span class="nn-premium-card-author">
                    <?php
                    if ( function_exists( 'neonews_get_user_avatar_html' ) ) {
                        echo neonews_get_user_avatar_html( $author_id, 24, 'nn-premium-card-avatar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    }
                    ?>
                    <span><?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?></span>
                </span>
            <?php endif; ?>
            <?php if ( $show_date ) : ?>
                <time class="nn-premium-card-date" datetime="<?php echo esc_attr( get_the_date( 'c', $post_id ) ); ?>">
                    <?php echo esc_html( get_the_date( '', $post_id ) ); ?>
                </time>
            <?php endif; ?>
            <?php if ( $show_time && function_exists( 'neonews_get_reading_time' ) ) : ?>
                <span class="nn-premium-card-read">
                    <?php echo esc_html( neonews_get_reading_time( $post_id ) ); ?> <?php esc_html_e( 'min read', 'neonews-membership' ); ?>
                </span>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * @param int $post_id Post ID.
     */
    public static function render_premium_card( $post_id ) {
        $settings = NeoNews_Membership::get_settings();
        $layout   = sanitize_key( $settings['exclusive_card_layout'] ?? 'rich' );
        $style    = sanitize_key( $settings['exclusive_card_style'] ?? 'elevated' );
        if ( ! in_array( $layout, array( 'rich', 'compact', 'magazine' ), true ) ) {
            $layout = 'rich';
        }
        if ( ! in_array( $style, array( 'elevated', 'minimal', 'gradient' ), true ) ) {
            $style = 'elevated';
        }

        $locked     = NeoNews_Premium_Content::is_premium_post( $post_id ) && ! NeoNews_Membership_Levels::is_premium();
        $thumb      = get_the_post_thumbnail_url( $post_id, 'neonews-card' );
        $categories = get_the_category( $post_id );
        $badge      = $settings['exclusive_badge_label'] ?? __( 'Exclusive', 'neonews-membership' );
        $excerpt_len = max( 8, min( 50, absint( $settings['exclusive_excerpt_words'] ?? 20 ) ) );

        $classes = array(
            'nn-premium-card',
            'nn-premium-card--' . $layout,
            'nn-premium-card--' . $style,
        );
        if ( $locked ) {
            $classes[] = 'nn-premium-card-locked';
        }
        ?>
        <article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
            <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="nn-premium-card-link">
                <span class="nn-premium-card-thumb">
                    <?php if ( $thumb ) : ?>
                        <img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy" class="<?php echo $locked ? 'nn-lock-blur' : ''; ?>" />
                    <?php else : ?>
                        <span class="nn-premium-card-fallback" aria-hidden="true"></span>
                    <?php endif; ?>

                    <?php if ( self::exclusive_card_show( 'category' ) && ! empty( $categories ) ) : ?>
                        <span class="nn-premium-card-cat"><?php echo esc_html( $categories[0]->name ); ?></span>
                    <?php endif; ?>

                    <?php if ( self::exclusive_card_show( 'views_overlay' ) && self::exclusive_card_show( 'views' ) && function_exists( 'neonews_get_post_views' ) ) : ?>
                        <span class="nn-premium-card-views">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <?php echo esc_html( neonews_format_views( neonews_get_post_views( $post_id ) ) ); ?>
                        </span>
                    <?php endif; ?>

                    <span class="nn-premium-card-star" aria-hidden="true">★</span>

                    <?php if ( $locked ) : ?>
                        <span class="nn-premium-lock-overlay"><span class="nn-lock-icon">🔒</span></span>
                    <?php endif; ?>
                </span>

                <span class="nn-premium-card-body">
                    <span class="nn-premium-card-badge"><?php echo esc_html( $badge ); ?></span>
                    <span class="nn-premium-card-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>

                    <?php if ( self::exclusive_card_show( 'excerpt' ) && 'compact' !== $layout ) : ?>
                        <span class="nn-premium-card-excerpt"><?php echo esc_html( wp_trim_words( get_post_field( 'post_excerpt', $post_id ) ?: wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), $excerpt_len ) ); ?></span>
                    <?php endif; ?>

                    <?php self::render_exclusive_meta_row( $post_id ); ?>
                    <?php self::render_exclusive_engagement( $post_id ); ?>
                </span>
            </a>
        </article>
        <?php
    }

    /**
     * @param int  $count       Posts count.
     * @param bool $home_only   Only posts flagged for home.
     * @return WP_Query
     */
    public static function get_premium_posts_query( $count = 10, $home_only = false ) {
        $meta_query = array(
            array(
                'key'   => '_neonews_premium_post',
                'value' => '1',
            ),
        );

        if ( $home_only ) {
            $meta_query[] = array(
                'key'   => '_neonews_premium_on_home',
                'value' => '1',
            );
        }

        return new WP_Query(
            array(
                'post_type'      => 'post',
                'posts_per_page' => max( 1, absint( $count ) ),
                'post_status'    => 'publish',
                'meta_query'     => $meta_query,
            )
        );
    }

    /**
     * @param array    $classes Post classes.
     * @param string[] $class   Additional classes.
     * @param int      $post_id Post ID.
     * @return array
     */
    public function premium_post_class( $classes, $class, $post_id ) {
        if ( NeoNews_Premium_Content::is_premium_post( $post_id ) && ! NeoNews_Membership_Levels::is_premium() ) {
            $classes[] = 'nn-post-premium-locked';
        }
        return $classes;
    }

    /**
     * @param WP_Post[] $posts Posts.
     * @param WP_Query  $query Query.
     * @return WP_Post[]
     */
    public function mark_locked_archive_posts( $posts, $query ) {
        unset( $query );
        return $posts;
    }

    /**
     * @return string
     */
    public static function get_profile_url() {
        if ( function_exists( 'neonews_get_profile_url' ) ) {
            return neonews_get_profile_url();
        }
        $page = get_page_by_path( 'my-profile' );
        return $page ? get_permalink( $page ) : home_url( '/my-profile/' );
    }

    /**
     * @return string
     */
    public static function get_enroll_url() {
        return self::get_profile_url() . '#nn-membership-enroll';
    }

    /**
     * @return string
     */
    public static function get_exclusive_page_url() {
        $slug = sanitize_title( NeoNews_Membership::get_settings( 'exclusive_page_slug' ) );
        $page = get_page_by_path( $slug );
        return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
    }
}

/**
 * Exclusive page slug from settings.
 *
 * @return string
 */
function neonews_membership_get_exclusive_page_slug() {
    $slug = sanitize_title( NeoNews_Membership::get_settings( 'exclusive_page_slug' ) );
    return $slug ? $slug : 'exclusive';
}

/**
 * Get exclusive page ID without side effects.
 *
 * @return int
 */
function neonews_membership_get_exclusive_page_id() {
    $page = get_page_by_path( neonews_membership_get_exclusive_page_slug() );
    return $page ? (int) $page->ID : 0;
}

/**
 * Ensure exclusive news page exists (does not sync menus — call neonews_sync_primary_nav_menus separately).
 *
 * @return int Page ID or 0.
 */
function neonews_membership_ensure_exclusive_page() {
    static $running = false;

    if ( $running ) {
        return neonews_membership_get_exclusive_page_id();
    }

    $slug = neonews_membership_get_exclusive_page_slug();

    $content = '<div class="nn-exclusive-intro">'
        . '<p>' . esc_html__( 'Welcome to our premium newsroom — in-depth investigations, member-only analysis, and early access to breaking stories.', 'neonews-membership' ) . '</p>'
        . '<ul>'
        . '<li>' . esc_html__( 'Exclusive long-form reporting', 'neonews-membership' ) . '</li>'
        . '<li>' . esc_html__( 'Early access before public release', 'neonews-membership' ) . '</li>'
        . '<li>' . esc_html__( 'Ad-free reading for premium members', 'neonews-membership' ) . '</li>'
        . '</ul>'
        . '<p><strong>' . esc_html__( 'Not a member yet?', 'neonews-membership' ) . '</strong> '
        . esc_html__( 'Contact our team or redeem your activation key on your profile page.', 'neonews-membership' ) . '</p>'
        . '</div>';

    $existing = get_page_by_path( $slug );
    if ( $existing ) {
        if ( 'templates/template-exclusive.php' !== get_post_meta( $existing->ID, '_wp_page_template', true ) ) {
            update_post_meta( $existing->ID, '_wp_page_template', 'templates/template-exclusive.php' );
        }
        if ( 'publish' !== $existing->post_status ) {
            $running = true;
            wp_update_post(
                array(
                    'ID'          => $existing->ID,
                    'post_status' => 'publish',
                )
            );
            $running = false;
        }
        return (int) $existing->ID;
    }

    $running = true;
    $page_id = wp_insert_post(
        array(
            'post_title'   => __( 'Exclusive News', 'neonews-membership' ),
            'post_name'    => $slug,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => get_current_user_id() ?: 1,
        ),
        true
    );
    $running = false;

    if ( ! is_wp_error( $page_id ) ) {
        update_post_meta( $page_id, '_wp_page_template', 'templates/template-exclusive.php' );
        update_post_meta( $page_id, '_neonews_demo_content', '1' );
    }

    return is_wp_error( $page_id ) ? 0 : (int) $page_id;
}

/**
 * Create exclusive page on init only when missing.
 */
function neonews_membership_maybe_create_exclusive_page() {
    if ( neonews_membership_get_exclusive_page_id() ) {
        return;
    }
    neonews_membership_ensure_exclusive_page();
}

add_action( 'init', 'neonews_membership_maybe_create_exclusive_page', 14 );

/**
 * Count published posts marked as premium.
 *
 * @return int
 */
function neonews_membership_count_premium_posts() {
    $query = new WP_Query(
        array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'   => '_neonews_premium_post',
                    'value' => '1',
                ),
            ),
        )
    );

    return (int) $query->found_posts;
}

/**
 * Mark recent published posts as premium for demo/exclusive sections.
 *
 * @param int $count Number of posts to mark.
 * @return int Number of posts marked.
 */
function neonews_membership_seed_premium_posts( $count = 4 ) {
    $count = max( 1, absint( $count ) );

    $posts = get_posts(
        array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $count,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'fields'         => 'ids',
        )
    );

    if ( empty( $posts ) ) {
        return 0;
    }

    $marked = 0;
    foreach ( $posts as $post_id ) {
        update_post_meta( (int) $post_id, '_neonews_premium_post', '1' );
        update_post_meta( (int) $post_id, '_neonews_premium_on_home', '1' );
        $marked++;
    }

    return $marked;
}

/**
 * Auto-seed demo premium posts once when none exist.
 */
function neonews_membership_maybe_seed_premium_posts() {
    if ( get_option( 'neonews_premium_demo_seeded' ) ) {
        return;
    }

    if ( neonews_membership_count_premium_posts() > 0 ) {
        update_option( 'neonews_premium_demo_seeded', 1 );
        return;
    }

    $marked = neonews_membership_seed_premium_posts( 4 );
    if ( $marked > 0 ) {
        update_option( 'neonews_premium_demo_seeded', 1 );
    }
}

add_action( 'init', 'neonews_membership_maybe_seed_premium_posts', 16 );
