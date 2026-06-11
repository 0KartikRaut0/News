<?php
/**
 * Template Name: Exclusive News
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$settings = NeoNews_Membership::get_settings();
$count    = absint( $settings['exclusive_page_count'] ?? 12 );
$query    = NeoNews_Membership_Frontend::get_premium_posts_query( max( 1, $count ), false );
$subtitle = $settings['exclusive_page_subtitle'] ?? '';
?>

<div class="nn-container nn-page-fancy">
    <?php while ( have_posts() ) : the_post(); ?>
        <header class="nn-page-hero-fancy nn-reveal">
            <h1><?php the_title(); ?></h1>
            <?php if ( $subtitle ) : ?>
                <p><?php echo esc_html( $subtitle ); ?></p>
            <?php endif; ?>
        </header>

        <?php if ( get_the_content() ) : ?>
            <div class="nn-page-card nn-reveal">
                <div class="nn-page-content nn-single-content">
                    <?php the_content(); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ( ! is_user_logged_in() ) : ?>
            <div class="nn-page-card nn-membership-enroll nn-reveal" id="nn-membership-enroll">
                <h2><?php esc_html_e( 'Enroll to Premium', 'neonews-membership' ); ?></h2>
                <p><?php esc_html_e( 'Sign in to redeem your activation key or contact our team for premium access.', 'neonews-membership' ); ?></p>
                <button type="button" class="nn-btn nn-open-login"><?php esc_html_e( 'Sign In', 'neonews' ); ?></button>
            </div>
        <?php elseif ( ! neonews_is_user_premium() ) : ?>
            <div class="nn-reveal">
                <?php do_action( 'neonews_profile_membership_panel' ); ?>
            </div>
        <?php endif; ?>

        <div class="nn-exclusive-grid nn-reveal <?php echo esc_attr( NeoNews_Membership_Frontend::get_exclusive_grid_class() ); ?>">
            <?php
            if ( $query->have_posts() ) :
                while ( $query->have_posts() ) :
                    $query->the_post();
                    NeoNews_Membership_Frontend::render_premium_card( get_the_ID() );
                endwhile;
                wp_reset_postdata();
            else :
                ?>
                <p><?php esc_html_e( 'No exclusive stories yet. Mark posts as premium in the editor.', 'neonews-membership' ); ?></p>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
