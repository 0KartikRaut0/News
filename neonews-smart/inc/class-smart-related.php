<?php
/**
 * Smart related posts (local scoring).
 *
 * @package NeoNews_Smart
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Smart_Related {

    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        add_filter( 'neonews_related_posts_query_args', array( $this, 'filter_related_args' ), 10, 2 );
        add_action( 'neonews_home_after_hero', array( $this, 'render_trending_smart' ), 18 );
    }

    /**
     * @param array $args    Query args.
     * @param int   $post_id Post ID.
     * @return array
     */
    public function filter_related_args( $args, $post_id ) {
        if ( ! NeoNews_Smart::is_enabled( 'smart_related' ) || ! $post_id ) {
            return $args;
        }

        $ids = self::get_related_ids( $post_id, (int) ( $args['posts_per_page'] ?? 3 ) );
        if ( empty( $ids ) ) {
            return $args;
        }

        $args['post__in'] = $ids;
        $args['orderby']  = 'post__in';
        unset( $args['category__in'] );

        return $args;
    }

    /**
     * @param int $post_id Post ID.
     * @param int $count   Count.
     * @return int[]
     */
    public static function get_related_ids( $post_id, $count = 3 ) {
        $count = max( 1, absint( $count ) );

        $pool = get_posts(
            array(
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => 40,
                'post__not_in'   => array( $post_id ),
                'category__in'   => wp_get_post_categories( $post_id ),
                'fields'         => 'ids',
            )
        );

        if ( empty( $pool ) ) {
            $pool = get_posts(
                array(
                    'post_type'      => 'post',
                    'post_status'    => 'publish',
                    'posts_per_page' => 40,
                    'post__not_in'   => array( $post_id ),
                    'fields'         => 'ids',
                )
            );
        }

        if ( empty( $pool ) ) {
            return array();
        }

        $scored = array();
        foreach ( $pool as $id ) {
            $scored[ $id ] = NeoNews_Smart_Helpers::score_related_post( $post_id, $id );
        }

        arsort( $scored );
        return array_slice( array_map( 'intval', array_keys( $scored ) ), 0, $count );
    }

    public function render_trending_smart() {
        if ( ! NeoNews_Smart::is_enabled( 'smart_related' ) || ! is_front_page() ) {
            return;
        }

        $query = new WP_Query(
            array(
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => 4,
                'meta_key'       => '_neonews_post_views',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC',
            )
        );

        if ( ! $query->have_posts() ) {
            return;
        }
        ?>
        <section class="nn-container nn-smart-trending nn-reveal">
            <header class="nn-section-head">
                <h2><span class="nn-smart-badge"><?php esc_html_e( 'Smart', 'neonews-smart' ); ?></span> <?php esc_html_e( 'Trending Now', 'neonews-smart' ); ?></h2>
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
