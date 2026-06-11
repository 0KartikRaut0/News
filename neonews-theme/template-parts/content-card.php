<?php

/**

 * Template part for displaying post cards — rich layout

 *

 * @package NeoNews

 */



if ( ! defined( 'ABSPATH' ) ) {

    exit;

}



$categories = get_the_category();

$author_id  = (int) get_the_author_meta( 'ID' );

?>



<article id="post-<?php the_ID(); ?>" <?php post_class( 'nn-post-card nn-card-rich' ); ?>>

    <div class="nn-card-rich-inner">

        <div class="nn-post-thumbnail nn-card-rich-media">

            <?php if ( has_post_thumbnail() ) : ?>

                <a href="<?php the_permalink(); ?>">

                    <?php the_post_thumbnail( 'neonews-card', array( 'loading' => 'lazy' ) ); ?>

                </a>

            <?php else : ?>

                <a href="<?php the_permalink(); ?>" class="nn-post-thumbnail-placeholder">

                    <span class="nn-card-rich-fallback"></span>

                </a>

            <?php endif; ?>

            <span class="nn-card-views-overlay"><?php neonews_the_views_badge(); ?></span>

        </div>



        <div class="nn-post-content nn-card-rich-body">

            <div class="nn-card-rich-top">

                <?php if ( ! empty( $categories ) ) : ?>

                    <a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>" class="nn-post-category">

                        <?php echo esc_html( $categories[0]->name ); ?>

                    </a>

                <?php endif; ?>

            </div>



            <h3 class="nn-post-title">

                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

            </h3>



            <p class="nn-post-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>



            <div class="nn-card-author-row">

                <?php echo neonews_get_user_avatar_html( $author_id, 28 ); ?>

                <span><?php echo esc_html( get_the_author() ); ?></span>

                <span class="nn-meta-sep">·</span>

                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>

                <span class="nn-meta-sep">·</span>

                <span><?php echo esc_html( neonews_get_reading_time() ); ?> <?php esc_html_e( 'min', 'neonews' ); ?></span>

            </div>



            <?php neonews_post_engagement_bar( get_the_ID(), true ); ?>

        </div>

    </div>

</article>


