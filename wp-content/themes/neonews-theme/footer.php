<?php
/**
 * The footer template — NewsPulse layout
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$settings = neonews_get_settings();
$copyright = get_theme_mod( 'neonews_footer_text', '' );
if ( empty( $copyright ) && ! empty( $settings['footer_copyright'] ) ) {
    $copyright = $settings['footer_copyright'];
}
?>
</main>

<footer id="colophon" class="nn-footer nn-footer-pulse" role="contentinfo">
    <div class="nn-footer-main">
        <div class="nn-container">
            <div class="nn-footer-grid nn-footer-pulse-grid">
                <div class="nn-footer-brand">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nn-footer-logo">
                        <span class="nn-logo-mark" aria-hidden="true"><?php echo esc_html( neonews_get_setting( 'logo_mark', '✦' ) ); ?></span>
                        <span><?php bloginfo( 'name' ); ?></span>
                    </a>
                    <?php if ( ! empty( $settings['footer_tagline'] ) ) : ?>
                        <p><?php echo esc_html( $settings['footer_tagline'] ); ?></p>
                    <?php endif; ?>
                    <?php if ( neonews_is_feature_enabled( 'show_footer_social' ) && neonews_has_social_profile_links() ) : ?>
                    <div class="nn-footer-social">
                        <?php neonews_render_social_icons( 'footer' ); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ( neonews_is_feature_enabled( 'show_footer_sections' ) ) : ?>
                <div class="nn-footer-col">
                    <h4 class="nn-footer-label"><?php esc_html_e( 'Sections', 'neonews' ); ?></h4>
                    <ul class="nn-footer-menu">
                        <?php
                        foreach ( neonews_get_footer_categories() as $slug ) {
                            $cat = get_category_by_slug( $slug );
                            if ( $cat ) {
                                echo '<li><a href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a></li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if ( neonews_is_feature_enabled( 'show_footer_company_pages' ) ) : ?>
                <div class="nn-footer-col">
                    <h4 class="nn-footer-label"><?php esc_html_e( 'Company', 'neonews' ); ?></h4>
                    <ul class="nn-footer-menu">
                        <?php
                        foreach ( neonews_get_footer_company_slugs() as $slug ) {
                            $page = get_page_by_path( $slug );
                            if ( $page ) {
                                echo '<li><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( get_the_title( $page ) ) . '</a></li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if ( neonews_is_feature_enabled( 'show_footer_newsletter' ) ) : ?>
                <div class="nn-footer-col nn-footer-newsletter">
                    <h4 class="nn-footer-label"><?php echo esc_html( $settings['footer_newsletter_title'] ); ?></h4>
                    <?php if ( ! empty( $settings['footer_newsletter_text'] ) ) : ?>
                        <p><?php echo esc_html( $settings['footer_newsletter_text'] ); ?></p>
                    <?php endif; ?>
                    <form class="nn-newsletter-form nn-footer-newsletter-form nn-sub-form" data-nn-subscribe="1" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post">
                        <?php if ( class_exists( 'NeoNews_Newsletter_Ajax' ) ) : ?>
                            <input type="hidden" name="action" value="nn_subscribe">
                            <input type="hidden" name="nonce" value=""><!-- filled by JS -->
                        <?php endif; ?>
                        <input type="email" name="email" placeholder="<?php esc_attr_e( 'Your email address', 'neonews' ); ?>" aria-label="<?php esc_attr_e( 'Email', 'neonews' ); ?>" required autocomplete="email" />
                        <button type="submit" class="nn-btn nn-btn-join"><?php esc_html_e( 'Join', 'neonews' ); ?></button>
                    </form>
                    <p class="nn-sub-feedback" aria-live="polite" style="display:none;margin-top:8px;font-size:13px;color:#ccc;"></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="nn-footer-bottom">
        <div class="nn-container nn-footer-bottom-inner">
            <p class="nn-footer-copy">
                <?php
                if ( $copyright ) {
                    echo wp_kses_post( $copyright );
                } else {
                    printf(
                        esc_html__( '© %1$s %2$s. All rights reserved.', 'neonews' ),
                        esc_html( date_i18n( 'Y' ) ),
                        esc_html( get_bloginfo( 'name' ) )
                    );
                }
                ?>
            </p>
            <?php if ( neonews_is_feature_enabled( 'show_footer_legal' ) || neonews_cookie_banner_enabled() ) : ?>
            <nav class="nn-footer-legal" aria-label="<?php esc_attr_e( 'Legal', 'neonews' ); ?>">
                <?php if ( neonews_is_feature_enabled( 'show_footer_legal' ) ) : ?>
                <?php
                foreach ( neonews_get_legal_links() as $slug => $label ) {
                    $url = neonews_get_legal_page_url( $slug );
                    echo '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
                }
                ?>
                <?php endif; ?>
                <?php if ( neonews_cookie_banner_enabled() ) : ?>
                ?>
                <button type="button" class="nn-cookie-reopen nn-footer-cookie-btn" aria-label="<?php esc_attr_e( 'Manage cookie preferences', 'neonews' ); ?>"><?php esc_html_e( 'Cookie Preferences', 'neonews' ); ?></button>
                <?php endif; ?>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</footer>

<div id="nn-page-transition" class="nn-page-transition" aria-hidden="true"></div>

<?php wp_footer(); ?>

</body>
</html>
