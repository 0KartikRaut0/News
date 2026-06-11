<?php
/**
 * Personalized homepage (activity log, local).
 *
 * @package NeoNews_Smart
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Smart_Personalize {

    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        add_action( 'neonews_home_after_hero', array( $this, 'render_section' ), 22 );
    }

    public function render_section() {
        if ( ! NeoNews_Smart::is_enabled( 'personalized' ) || ! is_user_logged_in() || ! is_front_page() ) {
            return;
        }

        $user_id = get_current_user_id();
        $cat_ids = NeoNews_Smart_Helpers::get_user_top_categories( $user_id, 3 );

        if ( empty( $cat_ids ) ) {
            return;
        }

        $read_ids = NeoNews_Smart_Helpers::get_user_read_post_ids( $user_id, 50 );

        $query = new WP_Query(
            array(
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => 4,
                'post__not_in'   => $read_ids,
                'category__in'   => $cat_ids,
                'orderby'        => 'date',
            )
        );

        if ( ! $query->have_posts() ) {
            return;
        }
        ?>
        <section class="nn-container nn-smart-for-you nn-reveal">
            <header class="nn-section-head">
                <h2><span class="nn-smart-badge"><?php esc_html_e( 'For you', 'neonews-smart' ); ?></span> <?php esc_html_e( 'Recommended picks', 'neonews-smart' ); ?></h2>
                <span class="nn-section-link"><?php esc_html_e( 'Based on your reading history', 'neonews-smart' ); ?></span>
            </header>
            <div class="nn-post-grid">
                <?php
                while ( $query->have_posts() ) :
                    $query->the_post();
                    get_template_part( 'template-parts/content', 'card' );
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        </section>
        <?php
    }
}
