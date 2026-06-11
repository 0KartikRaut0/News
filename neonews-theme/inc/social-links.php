<?php
/**
 * Social profile links & WhatsApp integration.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sanitized WhatsApp digits-only number from settings.
 *
 * @return string
 */
function neonews_get_whatsapp_number() {
    $raw = neonews_get_setting( 'whatsapp_number', '' );
    return preg_replace( '/[^0-9]/', '', (string) $raw );
}

/**
 * Whether WhatsApp number is configured.
 *
 * @return bool
 */
function neonews_has_whatsapp_number() {
    return strlen( neonews_get_whatsapp_number() ) >= 8;
}

/**
 * Show social icons in header date/time bar.
 *
 * @return bool
 */
function neonews_show_header_social() {
    return neonews_is_feature_enabled( 'show_header_social' ) && neonews_has_social_profile_links();
}

/**
 * Show social icons on Contact page.
 *
 * @return bool
 */
function neonews_show_contact_social() {
    return neonews_is_feature_enabled( 'show_contact_social' ) && neonews_has_social_profile_links();
}

/**
 * Show WhatsApp share on posts (site-wide setting).
 *
 * @return bool
 */
function neonews_show_post_whatsapp() {
    return neonews_is_feature_enabled( 'show_post_whatsapp' ) && neonews_is_feature_enabled( 'show_post_shares' );
}

/**
 * Whether this post should show social share buttons.
 *
 * @param int|null $post_id Post ID.
 * @return bool
 */
function neonews_post_allows_social_share( $post_id = null ) {
    if ( ! neonews_is_feature_enabled( 'show_post_shares' ) ) {
        return false;
    }

    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    if ( ! $post_id ) {
        return false;
    }

    $meta = get_post_meta( $post_id, '_neonews_show_social_share', true );

    return ( '' === $meta || '1' === $meta );
}

/**
 * Whether this post should show the WhatsApp share button.
 *
 * @param int|null $post_id Post ID.
 * @return bool
 */
function neonews_post_allows_whatsapp_share( $post_id = null ) {
    if ( ! neonews_show_post_whatsapp() || ! neonews_post_allows_social_share( $post_id ) ) {
        return false;
    }

    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    if ( ! $post_id ) {
        return false;
    }

    $meta = get_post_meta( $post_id, '_neonews_show_whatsapp_share', true );

    return ( '' === $meta || '1' === $meta );
}

/**
 * Show WhatsApp on Contact page.
 *
 * @return bool
 */
function neonews_show_contact_whatsapp() {
    return neonews_is_feature_enabled( 'show_contact_whatsapp' ) && neonews_has_whatsapp_number();
}

/**
 * Show WhatsApp on Advertise page.
 *
 * @return bool
 */
function neonews_show_advertise_whatsapp() {
    return neonews_is_feature_enabled( 'show_advertise_whatsapp' ) && neonews_has_whatsapp_number();
}

/**
 * Show WhatsApp on Careers page.
 *
 * @return bool
 */
function neonews_show_careers_whatsapp() {
    return neonews_is_feature_enabled( 'show_careers_whatsapp' ) && neonews_has_whatsapp_number();
}

/**
 * Configured social profile URLs.
 *
 * @return array<int, array{key:string,url:string,label:string,letter:string}>
 */
function neonews_get_social_profile_links() {
    $settings = neonews_get_settings();
    $links    = array(
        array(
            'key'    => 'x',
            'url'    => $settings['social_x'] ?? '',
            'label'  => 'X',
            'letter' => '𝕏',
        ),
        array(
            'key'    => 'facebook',
            'url'    => $settings['social_facebook'] ?? '',
            'label'  => 'Facebook',
            'letter' => 'f',
        ),
        array(
            'key'    => 'linkedin',
            'url'    => $settings['social_linkedin'] ?? '',
            'label'  => 'LinkedIn',
            'letter' => 'in',
        ),
        array(
            'key'    => 'instagram',
            'url'    => $settings['social_instagram'] ?? '',
            'label'  => 'Instagram',
            'letter' => '◎',
        ),
    );

    $out = array();
    foreach ( $links as $link ) {
        if ( ! empty( $link['url'] ) ) {
            $out[] = $link;
        }
    }

    return $out;
}

/**
 * Any social profile URL set.
 *
 * @return bool
 */
function neonews_has_social_profile_links() {
    return ! empty( neonews_get_social_profile_links() );
}

/**
 * WhatsApp share URL for a post.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function neonews_get_whatsapp_share_url( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    $text = wp_strip_all_tags( get_the_title( $post_id ) ) . ' ' . get_permalink( $post_id );

    return 'https://wa.me/?text=' . rawurlencode( $text );
}

/**
 * WhatsApp chat URL with a prefilled message.
 *
 * @param string $message Prefilled chat text.
 * @return string
 */
function neonews_get_whatsapp_chat_url( $message ) {
    $number = neonews_get_whatsapp_number();
    if ( ! $number ) {
        return '';
    }

    return 'https://wa.me/' . $number . '?text=' . rawurlencode( $message );
}

/**
 * WhatsApp chat URL for Contact page.
 *
 * @return string
 */
function neonews_get_whatsapp_contact_url() {
    $message = neonews_get_setting(
        'whatsapp_contact_message',
        __( 'Hello, I would like to get in touch.', 'neonews' )
    );

    return neonews_get_whatsapp_chat_url( $message );
}

/**
 * WhatsApp chat URL for Advertise page.
 *
 * @return string
 */
function neonews_get_whatsapp_advertise_url() {
    $message = neonews_get_setting(
        'whatsapp_advertise_message',
        __( 'Hello, I am interested in advertising on your site.', 'neonews' )
    );

    return neonews_get_whatsapp_chat_url( $message );
}

/**
 * WhatsApp chat URL for Careers page.
 *
 * @return string
 */
function neonews_get_whatsapp_careers_url() {
    $message = neonews_get_setting(
        'whatsapp_careers_message',
        __( 'Hello, I would like to apply for a role at your newsroom.', 'neonews' )
    );

    return neonews_get_whatsapp_chat_url( $message );
}

/**
 * Inline WhatsApp SVG icon.
 *
 * @param int $size Icon size in px.
 * @return string
 */
function neonews_whatsapp_svg( $size = 16 ) {
    return sprintf(
        '<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.883 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',
        absint( $size )
    );
}

/**
 * Render social profile icon links.
 *
 * @param string $context header|footer|contact.
 */
function neonews_render_social_icons( $context = 'footer' ) {
    $links = neonews_get_social_profile_links();
    if ( empty( $links ) ) {
        return;
    }

    $class = 'nn-social-icons nn-social-icons-' . sanitize_html_class( $context );
    echo '<div class="' . esc_attr( $class ) . '" role="navigation" aria-label="' . esc_attr__( 'Social media', 'neonews' ) . '">';
    foreach ( $links as $link ) {
        printf(
            '<a href="%1$s" class="nn-social-icon nn-social-%2$s" target="_blank" rel="noopener noreferrer" aria-label="%3$s"><span aria-hidden="true">%4$s</span></a>',
            esc_url( $link['url'] ),
            esc_attr( $link['key'] ),
            esc_attr( $link['label'] ),
            esc_html( $link['letter'] )
        );
    }
    echo '</div>';
}

/**
 * Render WhatsApp share link for a post.
 *
 * @param int|null $post_id Post ID.
 */
function neonews_render_whatsapp_share_icon( $post_id = null ) {
    if ( ! neonews_post_allows_whatsapp_share( $post_id ) ) {
        return;
    }

    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    $url = neonews_get_whatsapp_share_url( $post_id );
    printf(
        '<a href="%1$s" class="nn-share-icon nn-share-whatsapp" target="_blank" rel="noopener noreferrer" data-share="whatsapp" data-post="%2$s" aria-label="%3$s">%4$s</a>',
        esc_url( $url ),
        esc_attr( $post_id ),
        esc_attr__( 'Share on WhatsApp', 'neonews' ),
        neonews_whatsapp_svg( 14 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    );
}

/**
 * Render a WhatsApp chat CTA block.
 *
 * @param array $args Block options.
 */
function neonews_render_whatsapp_cta_block( $args ) {
    $args = wp_parse_args(
        $args,
        array(
            'enabled'     => false,
            'url'         => '',
            'title'       => __( 'Chat on WhatsApp', 'neonews' ),
            'description' => '',
        )
    );

    if ( ! $args['enabled'] || empty( $args['url'] ) ) {
        return;
    }
    ?>
    <div class="nn-contact-whatsapp">
        <h4><?php echo esc_html( $args['title'] ); ?></h4>
        <?php if ( ! empty( $args['description'] ) ) : ?>
            <p><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif; ?>
        <a href="<?php echo esc_url( $args['url'] ); ?>" class="nn-btn nn-btn-whatsapp" target="_blank" rel="noopener noreferrer">
            <?php echo neonews_whatsapp_svg( 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php esc_html_e( 'Open WhatsApp', 'neonews' ); ?>
        </a>
    </div>
    <?php
}

/**
 * Render Contact page WhatsApp block.
 */
function neonews_render_contact_whatsapp_block() {
    neonews_render_whatsapp_cta_block(
        array(
            'enabled'     => neonews_show_contact_whatsapp(),
            'url'         => neonews_get_whatsapp_contact_url(),
            'description' => __( 'Message us directly — we usually reply within one business day.', 'neonews' ),
        )
    );
}

/**
 * Render Advertise page WhatsApp block.
 */
function neonews_render_advertise_whatsapp_block() {
    neonews_render_whatsapp_cta_block(
        array(
            'enabled'     => neonews_show_advertise_whatsapp(),
            'url'         => neonews_get_whatsapp_advertise_url(),
            'title'       => __( 'Talk to our ad team', 'neonews' ),
            'description' => __( 'Get rate cards, custom packages, and campaign ideas on WhatsApp.', 'neonews' ),
        )
    );
}

/**
 * Render Careers page WhatsApp block.
 */
function neonews_render_careers_whatsapp_block() {
    neonews_render_whatsapp_cta_block(
        array(
            'enabled'     => neonews_show_careers_whatsapp(),
            'url'         => neonews_get_whatsapp_careers_url(),
            'title'       => __( 'Apply via WhatsApp', 'neonews' ),
            'description' => __( 'Send your portfolio or ask about open roles — our hiring team is on WhatsApp.', 'neonews' ),
        )
    );
}
