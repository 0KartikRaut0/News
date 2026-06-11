<?php
/**
 * Membership badges, timers, and display helpers.
 *
 * @package NeoNews_Membership
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Membership display helpers.
 */
class NeoNews_Membership_Display {

    /**
     * @param int|null $user_id User ID.
     * @return bool
     */
    public static function is_premium_user( $user_id = null ) {
        return NeoNews_Membership_Levels::is_premium( $user_id );
    }

    /**
     * @param int|null $user_id User ID.
     * @return int|null Days until expiry, null if lifetime or not premium.
     */
    public static function get_days_until_expiry( $user_id = null ) {
        return NeoNews_Membership_Levels::get_days_until_expiry( $user_id );
    }

    /**
     * @param int|null $user_id User ID.
     * @return bool
     */
    public static function should_show_expiry_reminder( $user_id = null ) {
        if ( ! self::is_premium_user( $user_id ) ) {
            return false;
        }

        $days = self::get_days_until_expiry( $user_id );
        if ( null === $days ) {
            return false;
        }

        $threshold = absint( NeoNews_Membership::get_settings( 'expiry_reminder_days' ) );
        return $days <= $threshold;
    }

    /**
     * @param string $context avatar|tag|inline.
     * @param int|null $user_id User ID.
     */
    public static function render_plan_badge( $context = 'tag', $user_id = null ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id ) {
            return;
        }

        $is_premium = self::is_premium_user( $user_id );
        $settings     = NeoNews_Membership::get_settings();

        if ( 'avatar' === $context && empty( $settings['show_badge_on_avatar'] ) ) {
            return;
        }

        if ( 'tag' === $context ) {
            return;
        }

        $label = $is_premium
            ? (string) $settings['premium_badge_label']
            : (string) $settings['basic_badge_label'];

        $icon      = self::get_avatar_badge_icon_html( $is_premium, $settings );
        $uses_image = self::has_custom_badge_image( $is_premium, $settings );

        $class = $is_premium ? 'nn-badge-premium nn-avatar-plan-badge' : 'nn-badge-basic nn-avatar-plan-badge';
        if ( $uses_image ) {
            $class .= ' nn-avatar-plan-badge--image';
        }
        if ( self::should_show_expiry_reminder( $user_id ) && ! empty( $settings['show_expiry_icon_header'] ) ) {
            $class .= ' nn-avatar-plan-badge-warning';
        }

        printf(
            '<span class="nn-member-badge %1$s" title="%2$s"><span class="nn-plan-icon">%3$s</span><span class="nn-visually-hidden">%2$s</span></span>',
            esc_attr( $class ),
            esc_attr( $label ),
            $icon // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );
    }

    /**
     * Whether a custom PNG badge is configured for the plan.
     *
     * @param bool  $is_premium Premium user.
     * @param array $settings   Membership settings.
     * @return bool
     */
    private static function has_custom_badge_image( $is_premium, $settings ) {
        $key = $is_premium ? 'premium_badge_image_id' : 'basic_badge_image_id';
        return ! empty( $settings[ $key ] ) && wp_get_attachment_image_url( (int) $settings[ $key ], 'thumbnail' );
    }

    /**
     * Avatar badge icon markup — custom PNG or default SVG.
     *
     * @param bool  $is_premium Premium user.
     * @param array $settings   Membership settings.
     * @return string
     */
    private static function get_avatar_badge_icon_html( $is_premium, $settings ) {
        $key = $is_premium ? 'premium_badge_image_id' : 'basic_badge_image_id';
        $id  = absint( $settings[ $key ] ?? 0 );

        if ( $id ) {
            $url = wp_get_attachment_image_url( $id, 'thumbnail' );
            if ( $url ) {
                return sprintf(
                    '<img src="%s" alt="" class="nn-plan-badge-img" width="20" height="20" decoding="async" />',
                    esc_url( $url )
                );
            }
        }

        return $is_premium
            ? neonews_membership_crown_svg( 12 )
            : neonews_membership_basic_svg( 12 );
    }

    /**
     * @param string   $context profile|account|comment|inline.
     * @param int|null $user_id User ID.
     */
    public static function render_plan_tag( $context = 'profile', $user_id = null ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id ) {
            return;
        }

        $settings = NeoNews_Membership::get_settings();

        if ( 'profile' === $context && empty( $settings['show_tag_profile'] ) ) {
            return;
        }

        if ( 'account' === $context && empty( $settings['show_tag_account'] ) ) {
            return;
        }

        if ( 'comment' === $context && empty( $settings['show_badge_comments'] ) ) {
            return;
        }

        $is_premium = self::is_premium_user( $user_id );
        $label      = $is_premium
            ? (string) $settings['premium_tag_label']
            : (string) $settings['basic_tag_label'];
        $class      = $is_premium ? 'nn-badge-premium' : 'nn-badge-basic';
        $icon       = $is_premium
            ? neonews_membership_crown_svg( 14 )
            : neonews_membership_basic_svg( 14 );

        printf(
            '<span class="nn-member-badge nn-member-tag %1$s"><span class="nn-plan-icon">%2$s</span><span>%3$s</span></span>',
            esc_attr( $class ),
            $icon, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            esc_html( $label )
        );
    }

    /**
     * @param string $context profile|account.
     */
    public static function render_expiry_timer( $context = 'profile' ) {
        if ( ! is_user_logged_in() || ! self::should_show_expiry_reminder() ) {
            return;
        }

        $settings = NeoNews_Membership::get_settings();
        if ( 'profile' === $context && empty( $settings['show_expiry_timer_profile'] ) ) {
            return;
        }
        if ( 'account' === $context && empty( $settings['show_expiry_timer_account'] ) ) {
            return;
        }

        $days   = self::get_days_until_expiry();
        $expiry = NeoNews_Membership_Levels::get_premium_expiry();
        ?>
        <div class="nn-membership-expiry-timer" data-expiry="<?php echo esc_attr( $expiry ); ?>">
            <span class="nn-expiry-icon" aria-hidden="true">⏳</span>
            <div>
                <strong><?php esc_html_e( 'Premium plan expiring soon', 'neonews-membership' ); ?></strong>
                <p>
                    <?php
                    printf(
                        /* translators: %d: days remaining */
                        esc_html( _n( '%d day left on your premium plan.', '%d days left on your premium plan.', $days, 'neonews-membership' ) ),
                        absint( $days )
                    );
                    ?>
                </p>
                <span class="nn-expiry-countdown" data-days="<?php echo esc_attr( $days ); ?>"></span>
            </div>
        </div>
        <?php
    }

    /**
     * Wrap avatar HTML with plan badge overlay.
     *
     * @param string   $avatar Avatar HTML.
     * @param int|null $user_id User ID.
     * @return string
     */
    public static function wrap_avatar_with_badge( $avatar, $user_id = null ) {
        if ( ! NeoNews_Membership::get_settings( 'show_badge_on_avatar' ) ) {
            return $avatar;
        }

        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        ob_start();
        ?>
        <span class="nn-avatar-badge-wrap">
            <?php echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php self::render_plan_badge( 'avatar', $user_id ); ?>
        </span>
        <?php
        return ob_get_clean();
    }
}

/**
 * @param string   $context Context.
 * @param int|null $user_id User ID.
 */
function neonews_membership_render_plan_tag( $context = 'profile', $user_id = null ) {
    NeoNews_Membership_Display::render_plan_tag( $context, $user_id );
}

/**
 * @param string $context Context.
 */
function neonews_membership_render_expiry_timer( $context = 'profile' ) {
    NeoNews_Membership_Display::render_expiry_timer( $context );
}
