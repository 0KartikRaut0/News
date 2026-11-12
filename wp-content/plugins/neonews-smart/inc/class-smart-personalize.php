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
        if ( ! NeoNews_Smart::is_enabled( 'personalized' ) || ! is_front_page() ) {
            return;
        }

        $user_id    = get_current_user_id();
        $cat_ids    = array();
        $subtitle = __( 'Stories picked from your homepage topics', 'neonews-smart' );

        if ( $user_id ) {
            $cat_ids = NeoNews_Smart_Helpers::get_user_top_categories( $user_id, 3 );
            if ( ! empty( $cat_ids ) ) {
                $subtitle = __( 'Based on your reading history', 'neonews-smart' );
            }
        }

        if ( empty( $cat_ids ) && function_exists( 'neonews_get_setting' ) ) {
            $slug_raw = (string) neonews_get_setting( 'home_category_slugs', '' );
            $slugs    = array_filter( array_map( 'trim', explode( ',', $slug_raw ) ) );
            foreach ( array_slice( $slugs, 0, 4 ) as $slug ) {
                $term = get_category_by_slug( $slug );
                if ( $term && ! is_wp_error( $term ) ) {
                    $cat_ids[] = (int) $term->term_id;
                }
            }
        }

        $read_ids = $user_id ? NeoNews_Smart_Helpers::get_user_read_post_ids( $user_id, 50 ) : array();

        $args = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 4,
            'post__not_in'   => $read_ids,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        );
        if ( ! empty( $cat_ids ) ) {
            $args['category__in'] = $cat_ids;
        }

        $query = new WP_Query( $args );

        if ( ! $query->have_posts() ) {
            unset( $args['category__in'], $args['post__not_in'] );
            $query = new WP_Query( $args );
        }

        if ( ! $query->have_posts() ) {
            return;
        }
        ?>
        <section class="nn-container nn-smart-for-you nn-reveal">
            <header class="nn-section-header">
                <h2 class="nn-section-title"><span class="nn-smart-badge"><?php esc_html_e( 'For you', 'neonews-smart' ); ?></span> <?php esc_html_e( 'Recommended for you', 'neonews-smart' ); ?></h2>
                <span class="nn-section-sub"><?php echo esc_html( $subtitle ); ?></span>
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
