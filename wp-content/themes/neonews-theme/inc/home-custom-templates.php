<?php
/**
 * Homepage custom section templates (no HTML required).
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template registry for admin + front-end render.
 *
 * @return array<string, array>
 */
function neonews_home_custom_templates() {
    return array(
        'newsletter_cta' => array(
            'label'       => __( 'Newsletter signup strip', 'neonews' ),
            'description' => __( 'Gradient card with email field — uses your site newsletter handler.', 'neonews' ),
            'icon'        => '✉️',
            'fields'      => array(
                'headline' => array(
                    'type'    => 'text',
                    'label'   => __( 'Headline', 'neonews' ),
                    'default' => __( 'Get the morning briefing', 'neonews' ),
                ),
                'subtext'  => array(
                    'type'    => 'textarea',
                    'label'   => __( 'Short description', 'neonews' ),
                    'default' => __( 'Top stories in your inbox — free, unsubscribe anytime.', 'neonews' ),
                ),
            ),
        ),
        'promo_split'    => array(
            'label'       => __( 'Promo split (image + CTA)', 'neonews' ),
            'description' => __( 'Side-by-side promo with button — great for memberships or apps.', 'neonews' ),
            'icon'        => '🎯',
            'fields'      => array(
                'headline'   => array( 'type' => 'text', 'label' => __( 'Headline', 'neonews' ), 'default' => __( 'Go deeper with Premium', 'neonews' ) ),
                'subtext'    => array( 'type' => 'textarea', 'label' => __( 'Body text', 'neonews' ), 'default' => __( 'Unlock exclusive analysis and ad-light reading.', 'neonews' ) ),
                'btn_label'  => array( 'type' => 'text', 'label' => __( 'Button label', 'neonews' ), 'default' => __( 'See plans', 'neonews' ) ),
                'btn_url'    => array( 'type' => 'url', 'label' => __( 'Button link', 'neonews' ), 'default' => '' ),
                'image_id'   => array( 'type' => 'number', 'label' => __( 'Image (media ID)', 'neonews' ), 'default' => 0 ),
            ),
        ),
        'stats_strip'    => array(
            'label'       => __( 'Stats highlight bar', 'neonews' ),
            'description' => __( 'Up to four numbers that count up when scrolled into view.', 'neonews' ),
            'icon'        => '📊',
            'fields'      => array(
                'stat1_value' => array( 'type' => 'text', 'label' => __( 'Stat 1 — number', 'neonews' ), 'default' => '2.4M' ),
                'stat1_label' => array( 'type' => 'text', 'label' => __( 'Stat 1 — label', 'neonews' ), 'default' => __( 'Monthly readers', 'neonews' ) ),
                'stat2_value' => array( 'type' => 'text', 'label' => __( 'Stat 2 — number', 'neonews' ), 'default' => '120+' ),
                'stat2_label' => array( 'type' => 'text', 'label' => __( 'Stat 2 — label', 'neonews' ), 'default' => __( 'Countries', 'neonews' ) ),
                'stat3_value' => array( 'type' => 'text', 'label' => __( 'Stat 3 — number', 'neonews' ), 'default' => '24/7' ),
                'stat3_label' => array( 'type' => 'text', 'label' => __( 'Stat 3 — label', 'neonews' ), 'default' => __( 'Live coverage', 'neonews' ) ),
                'stat4_value' => array( 'type' => 'text', 'label' => __( 'Stat 4 — number (optional)', 'neonews' ), 'default' => '' ),
                'stat4_label' => array( 'type' => 'text', 'label' => __( 'Stat 4 — label', 'neonews' ), 'default' => '' ),
            ),
        ),
        'pull_quote'     => array(
            'label'       => __( 'Editor’s pull quote', 'neonews' ),
            'description' => __( 'Large quote block with attribution and optional link.', 'neonews' ),
            'icon'        => '💬',
            'fields'      => array(
                'quote'       => array( 'type' => 'textarea', 'label' => __( 'Quote', 'neonews' ), 'default' => __( 'News should inform, not overwhelm.', 'neonews' ) ),
                'attribution' => array( 'type' => 'text', 'label' => __( 'Attribution', 'neonews' ), 'default' => __( '— Editor in Chief', 'neonews' ) ),
                'link_url'    => array( 'type' => 'url', 'label' => __( 'Optional link', 'neonews' ), 'default' => '' ),
                'link_label'  => array( 'type' => 'text', 'label' => __( 'Link label', 'neonews' ), 'default' => __( 'Our mission', 'neonews' ) ),
            ),
        ),
        'topic_chips'    => array(
            'label'       => __( 'Topic chips (categories)', 'neonews' ),
            'description' => __( 'Interactive pills linking to category archives — comma-separated slugs.', 'neonews' ),
            'icon'        => '🏷️',
            'fields'      => array(
                'slugs' => array(
                    'type'        => 'text',
                    'label'       => __( 'Category slugs', 'neonews' ),
                    'default'     => 'politics,business,technology,sports',
                    'placeholder' => 'politics,business,technology',
                ),
            ),
        ),
        'quick_links'    => array(
            'label'       => __( 'Quick link cards', 'neonews' ),
            'description' => __( 'One link per line: Title | https://url', 'neonews' ),
            'icon'        => '🔗',
            'fields'      => array(
                'links' => array(
                    'type'    => 'textarea',
                    'label'   => __( 'Links', 'neonews' ),
                    'default' => "About us | /\nContact | /contact\nAdvertise | /advertise",
                ),
            ),
        ),
        'video_spotlight' => array(
            'label'       => __( 'Video spotlight', 'neonews' ),
            'description' => __( 'Single YouTube embed with title and blurb.', 'neonews' ),
            'icon'        => '▶️',
            'fields'      => array(
                'headline'     => array( 'type' => 'text', 'label' => __( 'Title', 'neonews' ), 'default' => __( 'Watch now', 'neonews' ) ),
                'subtext'      => array( 'type' => 'textarea', 'label' => __( 'Description', 'neonews' ), 'default' => '' ),
                'youtube_url'  => array( 'type' => 'url', 'label' => __( 'YouTube URL', 'neonews' ), 'default' => '' ),
            ),
        ),
        'cta_strip'       => array(
            'label'       => __( 'Call-to-action strip', 'neonews' ),
            'description' => __( 'Full-width banner with headline, short text, and button.', 'neonews' ),
            'icon'        => '⚡',
            'fields'      => array(
                'headline'  => array( 'type' => 'text', 'label' => __( 'Headline', 'neonews' ), 'default' => __( 'Support independent journalism', 'neonews' ) ),
                'subtext'   => array( 'type' => 'textarea', 'label' => __( 'Short text', 'neonews' ), 'default' => __( 'Become a member and read without limits.', 'neonews' ) ),
                'btn_label' => array( 'type' => 'text', 'label' => __( 'Button label', 'neonews' ), 'default' => __( 'Join today', 'neonews' ) ),
                'btn_url'   => array( 'type' => 'url', 'label' => __( 'Button link', 'neonews' ), 'default' => '' ),
            ),
        ),
    );
}

/**
 * Default config for a template.
 *
 * @param string $template_id Template key.
 * @return array
 */
function neonews_home_custom_template_defaults( $template_id ) {
    $registry = neonews_home_custom_templates();
    if ( ! isset( $registry[ $template_id ] ) ) {
        return array();
    }
    $out = array();
    foreach ( $registry[ $template_id ]['fields'] as $key => $field ) {
        $out[ $key ] = $field['default'] ?? '';
    }
    return $out;
}

/**
 * Sanitize template config from admin.
 *
 * @param string $template_id Template.
 * @param array  $raw         Raw config.
 * @return array
 */
function neonews_sanitize_custom_template_config( $template_id, $raw ) {
    $registry = neonews_home_custom_templates();
    if ( ! isset( $registry[ $template_id ] ) || ! is_array( $raw ) ) {
        return array();
    }

    $defaults = neonews_home_custom_template_defaults( $template_id );
    $clean    = $defaults;

    foreach ( $registry[ $template_id ]['fields'] as $key => $field ) {
        $val = $raw[ $key ] ?? $defaults[ $key ] ?? '';
        switch ( $field['type'] ) {
            case 'url':
                $clean[ $key ] = esc_url_raw( (string) $val );
                break;
            case 'number':
                $clean[ $key ] = absint( $val );
                break;
            case 'textarea':
                $clean[ $key ] = sanitize_textarea_field( (string) $val );
                break;
            default:
                $clean[ $key ] = sanitize_text_field( (string) $val );
        }
    }

    return $clean;
}

/**
 * Merge saved config with defaults.
 *
 * @param array $cs Section row.
 * @return array
 */
function neonews_get_custom_template_config( $cs ) {
    $tid = sanitize_key( $cs['template'] ?? '' );
    if ( ! $tid ) {
        return array();
    }
    return wp_parse_args(
        is_array( $cs['config'] ?? null ) ? $cs['config'] : array(),
        neonews_home_custom_template_defaults( $tid )
    );
}

/**
 * Render a template custom section.
 *
 * @param array $cs Section config.
 */
function neonews_render_home_custom_template( $cs ) {
    $tid = sanitize_key( $cs['template'] ?? '' );
    $registry = neonews_home_custom_templates();
    if ( ! $tid || ! isset( $registry[ $tid ] ) ) {
        return;
    }

    $title  = ! empty( $cs['title'] ) ? $cs['title'] : $registry[ $tid ]['label'];
    $config = neonews_get_custom_template_config( $cs );

    echo '<section class="nn-custom-section nn-hs-template nn-hs-' . esc_attr( $tid ) . ' nn-reveal">';
    $show_heading = ! empty( $cs['show_title'] ) && '' !== trim( (string) ( $cs['title'] ?? '' ) );
    if ( $show_heading ) {
        echo '<header class="nn-section-header"><h2 class="nn-section-title">' . esc_html( $title ) . '</h2></header>';
    }

    switch ( $tid ) {
        case 'newsletter_cta':
            neonews_render_hs_template_newsletter( $config );
            break;
        case 'promo_split':
            neonews_render_hs_template_promo_split( $config );
            break;
        case 'stats_strip':
            neonews_render_hs_template_stats( $config );
            break;
        case 'pull_quote':
            neonews_render_hs_template_pull_quote( $config );
            break;
        case 'topic_chips':
            neonews_render_hs_template_topic_chips( $config );
            break;
        case 'quick_links':
            neonews_render_hs_template_quick_links( $config );
            break;
        case 'video_spotlight':
            neonews_render_hs_template_video( $config );
            break;
        case 'cta_strip':
            neonews_render_hs_template_cta_strip( $config );
            break;
    }

    echo '</section>';
}

/**
 * @param array $c Config.
 */
function neonews_render_hs_template_cta_strip( $c ) {
    $url = $c['btn_url'] ? $c['btn_url'] : home_url( '/' );
    ?>
    <div class="nn-hs-cta-strip">
        <div class="nn-hs-cta-strip-copy">
            <h3 class="nn-hs-cta-strip-title"><?php echo esc_html( $c['headline'] ); ?></h3>
            <?php if ( ! empty( $c['subtext'] ) ) : ?>
                <p class="nn-hs-cta-strip-sub"><?php echo esc_html( $c['subtext'] ); ?></p>
            <?php endif; ?>
        </div>
        <?php if ( ! empty( $c['btn_label'] ) ) : ?>
            <a href="<?php echo esc_url( $url ); ?>" class="nn-btn nn-hs-cta-strip-btn"><?php echo esc_html( $c['btn_label'] ); ?></a>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * @param array $c Config.
 */
function neonews_render_hs_template_newsletter( $c ) {
    $use_builtin = class_exists( 'NeoNews_Newsletter_Ajax' );
    ?>
    <div class="nn-hs-newsletter-card">
        <div class="nn-hs-newsletter-copy">
            <h3 class="nn-hs-newsletter-title"><?php echo esc_html( $c['headline'] ); ?></h3>
            <p class="nn-hs-newsletter-sub"><?php echo esc_html( $c['subtext'] ); ?></p>
        </div>
        <form class="nn-newsletter-form nn-sub-form nn-hs-newsletter-form" data-nn-subscribe="1" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post">
            <?php if ( $use_builtin ) : ?>
                <input type="hidden" name="action" value="nn_subscribe">
                <input type="hidden" name="nonce" value="">
            <?php endif; ?>
            <input type="email" name="email" placeholder="<?php esc_attr_e( 'Your email', 'neonews' ); ?>" required autocomplete="email" />
            <button type="submit" class="nn-btn nn-btn-join"><?php esc_html_e( 'Subscribe', 'neonews' ); ?></button>
        </form>
        <p class="nn-sub-feedback" aria-live="polite" style="display:none;"></p>
    </div>
    <?php
}

/**
 * @param array $c Config.
 */
function neonews_render_hs_template_promo_split( $c ) {
    $url = $c['btn_url'] ? $c['btn_url'] : home_url( '/' );
    ?>
    <div class="nn-hs-promo">
        <?php if ( ! empty( $c['image_id'] ) ) : ?>
            <div class="nn-hs-promo-media">
                <?php echo wp_get_attachment_image( (int) $c['image_id'], 'medium_large', false, array( 'class' => 'nn-hs-promo-img' ) ); ?>
            </div>
        <?php endif; ?>
        <div class="nn-hs-promo-body">
            <h3 class="nn-hs-promo-title"><?php echo esc_html( $c['headline'] ); ?></h3>
            <p class="nn-hs-promo-text"><?php echo esc_html( $c['subtext'] ); ?></p>
            <?php if ( ! empty( $c['btn_label'] ) ) : ?>
                <a href="<?php echo esc_url( $url ); ?>" class="nn-btn nn-hs-promo-btn"><?php echo esc_html( $c['btn_label'] ); ?></a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * @param array $c Config.
 */
function neonews_render_hs_template_stats( $c ) {
    $items = array();
    for ( $i = 1; $i <= 4; $i++ ) {
        $val = trim( (string) ( $c[ 'stat' . $i . '_value' ] ?? '' ) );
        $lab = trim( (string) ( $c[ 'stat' . $i . '_label' ] ?? '' ) );
        if ( '' === $val && '' === $lab ) {
            continue;
        }
        $items[] = array( 'value' => $val, 'label' => $lab );
    }
    if ( empty( $items ) ) {
        return;
    }
    echo '<div class="nn-hs-stats" data-nn-stats="1">';
    foreach ( $items as $item ) {
        echo '<div class="nn-hs-stat">';
        echo '<span class="nn-hs-stat-value" data-nn-stat-value="' . esc_attr( $item['value'] ) . '">' . esc_html( $item['value'] ) . '</span>';
        echo '<span class="nn-hs-stat-label">' . esc_html( $item['label'] ) . '</span>';
        echo '</div>';
    }
    echo '</div>';
}

/**
 * @param array $c Config.
 */
function neonews_render_hs_template_pull_quote( $c ) {
    ?>
    <blockquote class="nn-hs-quote">
        <p class="nn-hs-quote-text"><?php echo esc_html( $c['quote'] ); ?></p>
        <footer class="nn-hs-quote-footer">
            <cite><?php echo esc_html( $c['attribution'] ); ?></cite>
            <?php if ( ! empty( $c['link_url'] ) && ! empty( $c['link_label'] ) ) : ?>
                <a href="<?php echo esc_url( $c['link_url'] ); ?>" class="nn-hs-quote-link"><?php echo esc_html( $c['link_label'] ); ?> →</a>
            <?php endif; ?>
        </footer>
    </blockquote>
    <?php
}

/**
 * @param array $c Config.
 */
function neonews_render_hs_template_topic_chips( $c ) {
    $slugs = array_filter( array_map( 'trim', explode( ',', (string) ( $c['slugs'] ?? '' ) ) ) );
    if ( empty( $slugs ) ) {
        return;
    }
    echo '<div class="nn-hs-chips" role="list">';
    foreach ( $slugs as $slug ) {
        $term = get_category_by_slug( sanitize_title( $slug ) );
        if ( ! $term || is_wp_error( $term ) ) {
            continue;
        }
        $link = get_category_link( $term->term_id );
        echo '<a role="listitem" class="nn-hs-chip" href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a>';
    }
    echo '</div>';
}

/**
 * @param array $c Config.
 */
function neonews_render_hs_template_quick_links( $c ) {
    $lines = preg_split( '/\r\n|\r|\n/', (string) ( $c['links'] ?? '' ) );
    $cards = array();
    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( '' === $line ) {
            continue;
        }
        $parts = array_map( 'trim', explode( '|', $line, 2 ) );
        if ( count( $parts ) < 2 || '' === $parts[1] ) {
            continue;
        }
        $cards[] = array( 'title' => $parts[0], 'url' => $parts[1] );
    }
    if ( empty( $cards ) ) {
        return;
    }
    echo '<div class="nn-hs-quick-links">';
    foreach ( $cards as $card ) {
        echo '<a class="nn-hs-quick-card" href="' . esc_url( $card['url'] ) . '">';
        echo '<span class="nn-hs-quick-card-title">' . esc_html( $card['title'] ) . '</span>';
        echo '<span class="nn-hs-quick-card-arrow" aria-hidden="true">→</span>';
        echo '</a>';
    }
    echo '</div>';
}

/**
 * @param array $c Config.
 */
function neonews_render_hs_template_video( $c ) {
    if ( empty( $c['youtube_url'] ) || ! function_exists( 'neonews_parse_youtube_id' ) ) {
        return;
    }
    $id = neonews_parse_youtube_id( $c['youtube_url'] );
    if ( ! $id ) {
        return;
    }
    ?>
    <div class="nn-hs-video-wrap">
        <?php if ( ! empty( $c['headline'] ) ) : ?>
            <h3 class="nn-hs-video-title"><?php echo esc_html( $c['headline'] ); ?></h3>
        <?php endif; ?>
        <?php if ( ! empty( $c['subtext'] ) ) : ?>
            <p class="nn-hs-video-sub"><?php echo esc_html( $c['subtext'] ); ?></p>
        <?php endif; ?>
        <div class="nn-hs-video-embed">
            <iframe src="https://www.youtube.com/embed/<?php echo esc_attr( $id ); ?>?rel=0" title="<?php echo esc_attr( $c['headline'] ); ?>" allowfullscreen loading="lazy"></iframe>
        </div>
    </div>
    <?php
}

/**
 * Render posts feed (tag or category).
 *
 * @param array $cs Section config.
 */
function neonews_render_custom_home_posts_feed( $cs ) {
    $source = in_array( $cs['source'] ?? '', array( 'tag', 'category' ), true ) ? $cs['source'] : 'tag';
    $slug   = sanitize_title( $cs['slug'] ?? '' );
    $count  = max( 1, min( 20, absint( $cs['count'] ?? 4 ) ) );
    if ( ! $slug ) {
        return;
    }

    $query_args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'no_found_rows'  => true,
    );

    $see_all = $cs['see_all_url'] ?? '';
    $title   = ! empty( $cs['title'] ) ? $cs['title'] : '';

    if ( 'category' === $source ) {
        $term = get_category_by_slug( $slug );
        if ( ! $term ) {
            return;
        }
        $query_args['cat'] = (int) $term->term_id;
        if ( ! $see_all ) {
            $see_all = get_category_link( $term->term_id );
        }
        if ( ! $title ) {
            $title = $term->name;
        }
    } else {
        $tag = get_term_by( 'slug', $slug, 'post_tag' );
        if ( ! $tag ) {
            return;
        }
        $query_args['tag_id'] = (int) $tag->term_id;
        if ( ! $see_all ) {
            $see_all = get_tag_link( $tag->term_id );
        }
        if ( ! $title ) {
            $title = $tag->name;
        }
    }

    $query = new WP_Query( $query_args );
    if ( ! $query->have_posts() ) {
        wp_reset_postdata();
        return;
    }

    echo '<section class="nn-custom-section nn-custom-section-feed nn-reveal">';
    echo '<header class="nn-section-header">';
    echo '<h2 class="nn-section-title">' . esc_html( $title ) . '</h2>';
    if ( $see_all ) {
        echo '<a href="' . esc_url( $see_all ) . '" class="nn-section-link">' . esc_html__( 'See all', 'neonews' ) . ' →</a>';
    }
    echo '</header>';
    $layout = ! empty( $cs['layout'] ) ? neonews_sanitize_home_layout( 'posts', $cs['layout'], 'cards' ) : 'cards';
    neonews_render_home_posts_layout( $query, $layout );
    echo '</section>';
}
