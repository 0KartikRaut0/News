<?php
/**
 * Weekly digest email (no external AI).
 *
 * @package NeoNews_Smart
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Smart_Digest {

    const CRON_HOOK = 'neonews_smart_weekly_digest';

    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        add_action( self::CRON_HOOK, array( $this, 'send_digest' ) );
    }

    public static function schedule_cron() {
        if ( wp_next_scheduled( self::CRON_HOOK ) ) {
            return;
        }
        wp_schedule_event( self::next_run(), 'weekly', self::CRON_HOOK );
    }

    public static function unschedule_cron() {
        $ts = wp_next_scheduled( self::CRON_HOOK );
        if ( $ts ) {
            wp_unschedule_event( $ts, self::CRON_HOOK );
        }
    }

    /**
     * @return int
     */
    private static function next_run() {
        $settings = NeoNews_Smart::get_settings();
        $day      = min( 6, max( 0, absint( $settings['digest_day'] ) ) );
        $hour     = min( 23, max( 0, absint( $settings['digest_hour'] ) ) );

        $ts = strtotime( sprintf( 'next %s %02d:00:00', self::weekday_name( $day ), $hour ) );
        return $ts > time() ? $ts : strtotime( '+1 week', $ts );
    }

    /**
     * @param int $day 0=Sun … 6=Sat.
     * @return string
     */
    private static function weekday_name( $day ) {
        $names = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
        return $names[ $day ] ?? 'Monday';
    }

    public function send_digest() {
        if ( ! NeoNews_Smart::is_enabled( 'digest' ) ) {
            return;
        }

        $recipients = $this->get_recipients();
        if ( empty( $recipients ) ) {
            return;
        }

        $posts = get_posts(
            array(
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => 8,
                'date_query'     => array(
                    array(
                        'after' => '1 week ago',
                    ),
                ),
            )
        );

        if ( empty( $posts ) ) {
            return;
        }

        $lines = array();
        foreach ( $posts as $p ) {
            $glance = NeoNews_Smart_Helpers::get_story_glance( $p->ID );
            $lines[] = sprintf(
                "• %s\n  %s\n  %s",
                $p->post_title,
                $glance ? wp_trim_words( $glance, 20, '…' ) : '',
                get_permalink( $p )
            );
        }

        $body_intro = __( 'Here are this week\'s top stories from our newsroom:', 'neonews-smart' );
        $subject    = sprintf(
            /* translators: %s: site name */
            __( '[%s] Your weekly news digest', 'neonews-smart' ),
            get_bloginfo( 'name' )
        );

        foreach ( $recipients as $user ) {
            if ( empty( $user->user_email ) ) {
                continue;
            }
            $body = sprintf(
                "%s,\n\n%s\n\n%s\n\n— %s\n%s",
                $user->display_name,
                $body_intro,
                implode( "\n\n", $lines ),
                get_bloginfo( 'name' ),
                home_url( '/' )
            );
            wp_mail( $user->user_email, $subject, $body );
        }
    }

    /**
     * @return WP_User[]
     */
    private function get_recipients() {
        if ( function_exists( 'NeoNews_Membership_Levels' ) ) {
            $premium = NeoNews_Membership_Levels::get_premium_users();
            if ( ! empty( $premium ) ) {
                return $premium;
            }
        }

        $admins = get_users(
            array(
                'role'   => 'administrator',
                'number' => 20,
            )
        );

        return $admins;
    }
}
