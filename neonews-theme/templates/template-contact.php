<?php
/**
 * Template Name: Contact Us
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$sent = isset( $_GET['contact'] ) && 'sent' === $_GET['contact'];
?>

<div class="nn-container nn-page-fancy">
    <?php while ( have_posts() ) : the_post(); ?>
        <header class="nn-page-hero-fancy nn-reveal">
            <h1><?php the_title(); ?></h1>
            <p><?php esc_html_e( 'We would love to hear from you.', 'neonews' ); ?></p>
        </header>

        <div class="nn-contact-grid nn-reveal">
            <div class="nn-page-card">
                <div class="nn-page-content nn-single-content">
                    <?php the_content(); ?>
                </div>
                <ul class="nn-contact-info">
                    <li><strong><?php esc_html_e( 'Email', 'neonews' ); ?></strong> hello@<?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></li>
                    <li><strong><?php esc_html_e( 'Editorial', 'neonews' ); ?></strong> news@<?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></li>
                    <li><strong><?php esc_html_e( 'Press', 'neonews' ); ?></strong> press@<?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></li>
                </ul>
                <?php if ( neonews_show_contact_social() ) : ?>
                <div class="nn-contact-social-wrap">
                    <h4><?php esc_html_e( 'Follow us', 'neonews' ); ?></h4>
                    <?php neonews_render_social_icons( 'contact' ); ?>
                </div>
                <?php endif; ?>
                <?php neonews_render_contact_whatsapp_block(); ?>
            </div>

            <div class="nn-page-card nn-contact-form">
                <?php if ( $sent ) : ?>
                    <p class="nn-form-success"><?php esc_html_e( 'Thank you! Your message has been sent.', 'neonews' ); ?></p>
                <?php endif; ?>
                <h3><?php esc_html_e( 'Send a message', 'neonews' ); ?></h3>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'neonews_contact', 'neonews_contact_nonce' ); ?>
                    <input type="hidden" name="action" value="neonews_contact_form" />
                    <label class="nn-visually-hidden" for="nn-contact-name"><?php esc_html_e( 'Name', 'neonews' ); ?></label>
                    <input type="text" id="nn-contact-name" name="contact_name" class="nn-form-input" placeholder="<?php esc_attr_e( 'Your name', 'neonews' ); ?>" required />
                    <label class="nn-visually-hidden" for="nn-contact-email"><?php esc_html_e( 'Email', 'neonews' ); ?></label>
                    <input type="email" id="nn-contact-email" name="contact_email" class="nn-form-input" placeholder="<?php esc_attr_e( 'Email address', 'neonews' ); ?>" required />
                    <label class="nn-visually-hidden" for="nn-contact-message"><?php esc_html_e( 'Message', 'neonews' ); ?></label>
                    <textarea id="nn-contact-message" name="contact_message" class="nn-form-textarea" rows="5" placeholder="<?php esc_attr_e( 'Your message…', 'neonews' ); ?>" required></textarea>
                    <button type="submit" class="nn-btn"><?php esc_html_e( 'Send Message', 'neonews' ); ?></button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
