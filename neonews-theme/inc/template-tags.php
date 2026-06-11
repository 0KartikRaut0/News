<?php
/**
 * Custom template tags for the theme
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Display post date
 */
function neonews_posted_on() {
    $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
    
    if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
        $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
    }

    $time_string = sprintf(
        $time_string,
        esc_attr( get_the_date( DATE_W3C ) ),
        esc_html( get_the_date() ),
        esc_attr( get_the_modified_date( DATE_W3C ) ),
        esc_html( get_the_modified_date() )
    );

    printf(
        '<span class="posted-on">%s</span>',
        $time_string
    );
}

/**
 * Display post author
 */
function neonews_posted_by() {
    printf(
        '<span class="byline"><span class="author vcard"><a class="url fn n" href="%1$s">%2$s</a></span></span>',
        esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
        esc_html( get_the_author() )
    );
}

/**
 * Display entry footer (categories, tags, edit link)
 */
function neonews_entry_footer() {
    if ( 'post' === get_post_type() ) {
        $categories_list = get_the_category_list( esc_html__( ', ', 'neonews' ) );
        if ( $categories_list ) {
            printf(
                '<span class="cat-links">%1$s %2$s</span>',
                esc_html__( 'Posted in', 'neonews' ),
                $categories_list
            );
        }

        $tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'neonews' ) );
        if ( $tags_list ) {
            printf(
                '<span class="tags-links">%1$s %2$s</span>',
                esc_html__( 'Tagged', 'neonews' ),
                $tags_list
            );
        }
    }

    if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
        echo '<span class="comments-link">';
        comments_popup_link(
            sprintf(
                wp_kses(
                    /* translators: %s: post title */
                    __( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'neonews' ),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post( get_the_title() )
            )
        );
        echo '</span>';
    }

    edit_post_link(
        sprintf(
            wp_kses(
                /* translators: %s: post title */
                __( 'Edit <span class="screen-reader-text">%s</span>', 'neonews' ),
                array(
                    'span' => array(
                        'class' => array(),
                    ),
                )
            ),
            wp_kses_post( get_the_title() )
        ),
        '<span class="edit-link">',
        '</span>'
    );
}

/**
 * Display post thumbnail
 */
function neonews_post_thumbnail( $size = 'neonews-card' ) {
    if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
        return;
    }

    if ( is_singular() ) :
        ?>
        <figure class="nn-post-thumbnail">
            <?php the_post_thumbnail( $size ); ?>
        </figure>
        <?php
    else :
        ?>
        <a class="nn-post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
            <?php
            the_post_thumbnail(
                $size,
                array(
                    'alt' => the_title_attribute( array(
                        'echo' => false,
                    ) ),
                    'loading' => 'lazy',
                )
            );
            ?>
        </a>
        <?php
    endif;
}

/**
 * Display breadcrumbs
 */
function neonews_breadcrumbs() {
    if ( is_front_page() ) {
        return;
    }

    $separator = '<span class="nn-breadcrumb-separator">/</span>';
    
    echo '<nav class="nn-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'neonews' ) . '">';
    echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'neonews' ) . '</a>';
    echo wp_kses_post( $separator );

    if ( is_category() ) {
        $cat = get_queried_object();
        if ( $cat->parent ) {
            $parent_cat = get_category( $cat->parent );
            echo '<a href="' . esc_url( get_category_link( $parent_cat->term_id ) ) . '">' . esc_html( $parent_cat->name ) . '</a>';
            echo wp_kses_post( $separator );
        }
        echo '<span>' . esc_html( single_cat_title( '', false ) ) . '</span>';
    } elseif ( is_tag() ) {
        echo '<span>' . esc_html( single_tag_title( '', false ) ) . '</span>';
    } elseif ( is_author() ) {
        echo '<span>' . esc_html( get_the_author() ) . '</span>';
    } elseif ( is_search() ) {
        echo '<span>' . esc_html__( 'Search Results', 'neonews' ) . '</span>';
    } elseif ( is_single() ) {
        $categories = get_the_category();
        if ( ! empty( $categories ) ) {
            echo '<a href="' . esc_url( get_category_link( $categories[0]->term_id ) ) . '">' . esc_html( $categories[0]->name ) . '</a>';
            echo wp_kses_post( $separator );
        }
        echo '<span>' . esc_html( get_the_title() ) . '</span>';
    } elseif ( is_page() ) {
        global $post;
        if ( $post->post_parent ) {
            $ancestors = get_post_ancestors( $post->ID );
            $ancestors = array_reverse( $ancestors );
            foreach ( $ancestors as $ancestor ) {
                echo '<a href="' . esc_url( get_permalink( $ancestor ) ) . '">' . esc_html( get_the_title( $ancestor ) ) . '</a>';
                echo wp_kses_post( $separator );
            }
        }
        echo '<span>' . esc_html( get_the_title() ) . '</span>';
    } elseif ( is_archive() ) {
        echo '<span>' . esc_html( post_type_archive_title( '', false ) ) . '</span>';
    }

    echo '</nav>';
}

/**
 * Display social share buttons
 */
function neonews_social_share() {
    if ( ! is_singular( 'post' ) ) {
        return;
    }

    $post_url = rawurlencode( get_permalink() );
    $post_title = rawurlencode( get_the_title() );
    
    $share_links = array(
        'facebook' => array(
            'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $post_url,
            'label' => esc_html__( 'Share on Facebook', 'neonews' ),
            'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>',
        ),
        'twitter' => array(
            'url'   => 'https://twitter.com/intent/tweet?url=' . $post_url . '&text=' . $post_title,
            'label' => esc_html__( 'Share on Twitter', 'neonews' ),
            'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>',
        ),
        'linkedin' => array(
            'url'   => 'https://www.linkedin.com/shareArticle?mini=true&url=' . $post_url . '&title=' . $post_title,
            'label' => esc_html__( 'Share on LinkedIn', 'neonews' ),
            'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>',
        ),
        'email' => array(
            'url'   => 'mailto:?subject=' . $post_title . '&body=' . $post_url,
            'label' => esc_html__( 'Share via Email', 'neonews' ),
            'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
        ),
    );

    if ( function_exists( 'neonews_show_post_whatsapp' ) && neonews_post_allows_whatsapp_share() ) {
        $share_links['whatsapp'] = array(
            'url'   => neonews_get_whatsapp_share_url(),
            'label' => esc_html__( 'Share on WhatsApp', 'neonews' ),
            'icon'  => neonews_whatsapp_svg( 18 ),
        );
    }

    echo '<div class="nn-social-share">';
    echo '<span class="nn-share-label">' . esc_html__( 'Share:', 'neonews' ) . '</span>';
    
    foreach ( $share_links as $network => $data ) {
        printf(
            '<a href="%1$s" class="nn-share-btn nn-share-%2$s" target="_blank" rel="noopener noreferrer" aria-label="%3$s">%4$s</a>',
            esc_url( $data['url'] ),
            esc_attr( $network ),
            esc_attr( $data['label'] ),
            $data['icon']
        );
    }
    
    echo '</div>';
}
