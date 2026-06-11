<?php
/**
 * Premium ad-free gating.
 *
 * @package NeoNews_Membership
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hide ads for premium members when enabled.
 */
class NeoNews_Membership_Ads {

    /**
     * Constructor.
     */
    private function __construct() {
        add_filter( 'neonews_should_render_ad_zone', array( $this, 'maybe_hide_ads' ), 10, 2 );
    }

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
     * @param bool   $show    Whether to show ad.
     * @param string $zone_id Zone ID.
     * @return bool
     */
    public function maybe_hide_ads( $show, $zone_id ) {
        unset( $zone_id );

        if ( ! $show ) {
            return false;
        }

        if ( empty( NeoNews_Membership::get_settings( 'hide_ads_for_premium' ) ) ) {
            return true;
        }

        if ( ! is_user_logged_in() ) {
            return true;
        }

        return ! NeoNews_Membership_Levels::is_premium();
    }
}

/**
 * Whether ads should render for current user.
 *
 * @return bool
 */
function neonews_membership_should_show_ads() {
    $show = true;
    return (bool) apply_filters( 'neonews_should_render_ad_zone', $show, 'all' );
}
