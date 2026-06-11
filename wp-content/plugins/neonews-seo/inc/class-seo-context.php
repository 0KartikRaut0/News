<?php
/**
 * Resolve SEO context for the current request.
 *
 * @package NeoNews_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_SEO_Context {

    /** @var array|null */
    private static $context = null;

    /**
     * @return array
     */
    public static function get() {
        if ( null !== self::$context ) {
            return self::$context;
        }

        self::$context = self::build();
        self::$context = apply_filters( 'neonews_seo_context', self::$context );

        return self::$context;
    }

    /**
     * @return array
     */
    private static function build() {
        $settings = NeoNews_SEO::get_settings();
        $context  = array(
            'type'        => 'other',
            'object_id'   => 0,
            'object_type' => '',
            'title'       => wp_get_document_title(),
            'description' => '',
            'canonical'   => self::current_url(),
            'robots'      => array(
                'noindex'  => false,
                'nofollow' => false,
            ),
            'og_type'     => 'website',
            'og_image'    => self::default_image(),
            'og_image_w'  => 0,
            'og_image_h'  => 0,
            'twitter_card'=> $settings['twitter_card'] ?? 'summary_large_image',
            'breadcrumbs' => array(),
            'schema_type' => 'WebPage',
            'is_premium'  => false,
        );

        if ( is_front_page() || is_home() ) {
            return self::build_home( $context, $settings );
        }

        if ( is_singular() ) {
            return self::build_singular( $context, $settings );
        }

        if ( is_category() || is_tag() || is_tax() ) {
            return self::build_term( $context, $settings );
        }

        if ( is_author() ) {
            return self::build_author( $context, $settings );
        }

        if ( is_search() ) {
            return self::build_search( $context, $settings );
        }

        if ( is_post_type_archive() ) {
            return self::build_post_type_archive( $context, $settings );
        }

        if ( is_404() ) {
            $context['type']        = '404';
            $context['title']       = __( 'Page not found', 'neonews-seo' ) . ' ' . self::separator() . ' ' . get_bloginfo( 'name' );
            $context['description'] = __( 'The page you requested could not be found.', 'neonews-seo' );
            $context['robots']      = array( 'noindex' => true, 'nofollow' => true );
            $context['schema_type'] = 'WebPage';
            return $context;
        }

        if ( is_archive() ) {
            $context['type']        = 'archive';
            $context['title']       = wp_strip_all_tags( get_the_archive_title() ) . ' ' . self::separator() . ' ' . get_bloginfo( 'name' );
            $context['description'] = wp_strip_all_tags( get_the_archive_description() );
            $context['og_type']     = 'website';
            $context['schema_type'] = 'CollectionPage';
            self::apply_global_robots( $context, $settings );
            return $context;
        }

        return $context;
    }

    /**
     * @param array $context Context.
     * @param array $settings Settings.
     * @return array
     */
    private static function build_home( $context, $settings ) {
        $context['type']        = 'home';
        $context['og_type']     = 'website';
        $context['schema_type'] = 'WebSite';
        $context['canonical']   = home_url( '/' );

        $title = trim( (string) ( $settings['home_title'] ?? '' ) );
        if ( $title ) {
            $context['title'] = $title;
        } else {
            $context['title'] = get_bloginfo( 'name' );
            $tagline          = get_bloginfo( 'description' );
            if ( $tagline ) {
                $context['title'] .= ' ' . self::separator() . ' ' . $tagline;
            }
        }

        $desc = trim( (string) ( $settings['home_description'] ?? '' ) );
        $context['description'] = $desc ? $desc : get_bloginfo( 'description' );

        $context['breadcrumbs'] = array(
            array(
                'name' => self::home_label( $settings ),
                'url'  => home_url( '/' ),
            ),
        );

        return $context;
    }

    /**
     * @param array $context Context.
     * @param array $settings Settings.
     * @return array
     */
    private static function build_singular( $context, $settings ) {
        global $post;

        if ( ! $post instanceof WP_Post ) {
            return $context;
        }

        $context['type']        = 'singular';
        $context['object_id']   = $post->ID;
        $context['object_type'] = $post->post_type;
        $context['canonical']   = get_permalink( $post );

        $custom_title  = NeoNews_SEO_Meta::get_post( $post->ID, NeoNews_SEO_Meta::POST_TITLE );
        $custom_desc   = NeoNews_SEO_Meta::get_post( $post->ID, NeoNews_SEO_Meta::POST_DESCRIPTION );
        $custom_canon  = NeoNews_SEO_Meta::get_post( $post->ID, NeoNews_SEO_Meta::POST_CANONICAL );
        $custom_robots = NeoNews_SEO_Meta::get_post( $post->ID, NeoNews_SEO_Meta::POST_ROBOTS );

        if ( $custom_canon ) {
            $context['canonical'] = esc_url_raw( $custom_canon );
        }

        $context['title']       = $custom_title ? $custom_title : ( get_the_title( $post ) . ' ' . self::separator() . ' ' . get_bloginfo( 'name' ) );
        $context['description'] = $custom_desc ? $custom_desc : self::description_for_post( $post );

        if ( $custom_robots ) {
            $context['robots'] = NeoNews_SEO_Meta::parse_robots( $custom_robots );
        }

        if ( 'post' === $post->post_type ) {
            $context['og_type']     = 'article';
            $context['schema_type'] = 'NewsArticle';
            $context['is_premium']  = self::is_premium_post( $post->ID );
            $context['breadcrumbs'] = self::breadcrumbs_for_post( $post, $settings );
        } elseif ( 'page' === $post->post_type ) {
            $context['og_type']     = 'website';
            $context['schema_type'] = 'WebPage';
            $context['breadcrumbs'] = self::breadcrumbs_for_page( $post, $settings );

            if ( self::is_account_page( $post ) && ! empty( $settings['noindex_account_pages'] ) ) {
                $context['robots']['noindex'] = true;
            }
        }

        $og_image = absint( NeoNews_SEO_Meta::get_post( $post->ID, NeoNews_SEO_Meta::POST_OG_IMAGE ) );
        if ( $og_image ) {
            self::apply_image( $context, $og_image );
        } elseif ( has_post_thumbnail( $post ) ) {
            self::apply_image( $context, get_post_thumbnail_id( $post ) );
        }

        self::apply_global_robots( $context, $settings );

        return $context;
    }

    /**
     * @param array $context Context.
     * @param array $settings Settings.
     * @return array
     */
    private static function build_term( $context, $settings ) {
        $term = get_queried_object();
        if ( ! $term instanceof WP_Term ) {
            return $context;
        }

        $context['type']        = 'term';
        $context['object_id']   = $term->term_id;
        $context['object_type'] = $term->taxonomy;
        $context['og_type']     = 'website';
        $context['schema_type'] = 'CollectionPage';
        $context['canonical']   = get_term_link( $term );

        $custom_title  = NeoNews_SEO_Meta::get_term( $term->term_id, $term->taxonomy, NeoNews_SEO_Meta::TERM_TITLE );
        $custom_desc   = NeoNews_SEO_Meta::get_term( $term->term_id, $term->taxonomy, NeoNews_SEO_Meta::TERM_DESCRIPTION );
        $custom_robots = NeoNews_SEO_Meta::get_term( $term->term_id, $term->taxonomy, NeoNews_SEO_Meta::TERM_ROBOTS );

        $name = $term->name;
        $context['title'] = $custom_title ? $custom_title : ( $name . ' ' . self::separator() . ' ' . get_bloginfo( 'name' ) );

        $term_desc = term_description( $term->term_id, $term->taxonomy );
        $context['description'] = $custom_desc ? $custom_desc : wp_strip_all_tags( $term_desc );
        if ( ! $context['description'] ) {
            $context['description'] = sprintf(
                /* translators: %s: term name */
                __( 'Latest news and articles in %s', 'neonews-seo' ),
                $name
            );
        }

        if ( $custom_robots ) {
            $context['robots'] = NeoNews_SEO_Meta::parse_robots( $custom_robots );
        } elseif ( is_tag() && ! empty( $settings['noindex_tags'] ) ) {
            $context['robots']['noindex'] = true;
        } elseif ( is_category() && ! empty( $settings['noindex_categories'] ) ) {
            $context['robots']['noindex'] = true;
        }

        $context['breadcrumbs'] = array(
            array( 'name' => self::home_label( $settings ), 'url' => home_url( '/' ) ),
            array( 'name' => $name, 'url' => $context['canonical'] ),
        );

        self::apply_global_robots( $context, $settings );

        return $context;
    }

    /**
     * @param array $context Context.
     * @param array $settings Settings.
     * @return array
     */
    private static function build_author( $context, $settings ) {
        $author = get_queried_object();
        if ( ! $author instanceof WP_User ) {
            return $context;
        }

        $context['type']        = 'author';
        $context['object_id']   = $author->ID;
        $context['object_type'] = 'author';
        $context['og_type']     = 'profile';
        $context['schema_type'] = 'ProfilePage';
        $context['canonical']   = get_author_posts_url( $author->ID );

        $name = $author->display_name;
        $context['title'] = $name . ' ' . self::separator() . ' ' . get_bloginfo( 'name' );

        $bio = get_the_author_meta( 'description', $author->ID );
        $context['description'] = $bio ? $bio : sprintf(
            /* translators: %s: author name */
            __( 'Articles by %s', 'neonews-seo' ),
            $name
        );

        if ( ! empty( $settings['noindex_author'] ) ) {
            $context['robots']['noindex'] = true;
        }

        $context['breadcrumbs'] = array(
            array( 'name' => self::home_label( $settings ), 'url' => home_url( '/' ) ),
            array( 'name' => $name, 'url' => $context['canonical'] ),
        );

        self::apply_global_robots( $context, $settings );

        return $context;
    }

    /**
     * @param array $context Context.
     * @param array $settings Settings.
     * @return array
     */
    private static function build_search( $context, $settings ) {
        $query = get_search_query();
        $context['type']        = 'search';
        $context['title']       = sprintf(
            /* translators: %s: search query */
            __( 'Search results for "%s"', 'neonews-seo' ),
            $query
        ) . ' ' . self::separator() . ' ' . get_bloginfo( 'name' );
        $context['description'] = sprintf(
            /* translators: 1: search query, 2: site name */
            __( 'Search results for %1$s on %2$s', 'neonews-seo' ),
            $query,
            get_bloginfo( 'name' )
        );
        $context['og_type']     = 'website';
        $context['schema_type'] = 'SearchResultsPage';
        $context['canonical']   = get_search_link( $query );

        if ( ! empty( $settings['noindex_search'] ) ) {
            $context['robots']['noindex']  = true;
            $context['robots']['nofollow'] = true;
        }

        return $context;
    }

    /**
     * @param array $context Context.
     * @param array $settings Settings.
     * @return array
     */
    private static function build_post_type_archive( $context, $settings ) {
        $context['type']        = 'post_type_archive';
        $context['og_type']     = 'website';
        $context['schema_type'] = 'CollectionPage';
        $context['title']       = post_type_archive_title( '', false ) . ' ' . self::separator() . ' ' . get_bloginfo( 'name' );
        $context['description'] = get_bloginfo( 'description' );
        self::apply_global_robots( $context, $settings );
        return $context;
    }

    /**
     * @param array $context Context.
     * @param array $settings Settings.
     */
    private static function apply_global_robots( &$context, $settings ) {
        if ( is_paged() && ! empty( $settings['noindex_paged'] ) ) {
            $context['robots']['noindex'] = true;
        }

        if ( is_date() && ! empty( $settings['noindex_date'] ) ) {
            $context['robots']['noindex'] = true;
        }
    }

    /**
     * @param WP_Post $post Post.
     * @return string
     */
    public static function description_for_post( $post ) {
        $excerpt = get_post_field( 'post_excerpt', $post );
        if ( $excerpt ) {
            return wp_trim_words( wp_strip_all_tags( $excerpt ), 30, '' );
        }

        $glance = get_post_meta( $post->ID, '_neonews_smart_glance', true );
        if ( $glance ) {
            return wp_trim_words( wp_strip_all_tags( $glance ), 30, '' );
        }

        return wp_trim_words( wp_strip_all_tags( $post->post_content ), 30, '' );
    }

    /**
     * @param int $post_id Post ID.
     * @return bool
     */
    public static function is_premium_post( $post_id ) {
        if ( class_exists( 'NeoNews_Premium_Content' ) ) {
            return NeoNews_Premium_Content::is_premium_post( $post_id );
        }
        return false;
    }

    /**
     * @param WP_Post $post Post.
     * @param array   $settings Settings.
     * @return array<int,array{name:string,url:string}>
     */
    private static function breadcrumbs_for_post( $post, $settings ) {
        $crumbs = array(
            array( 'name' => self::home_label( $settings ), 'url' => home_url( '/' ) ),
        );

        $cats = get_the_category( $post->ID );
        if ( ! empty( $cats ) ) {
            $cat = $cats[0];
            $crumbs[] = array(
                'name' => $cat->name,
                'url'  => get_category_link( $cat->term_id ),
            );
        }

        $crumbs[] = array(
            'name' => get_the_title( $post ),
            'url'  => get_permalink( $post ),
        );

        return $crumbs;
    }

    /**
     * @param WP_Post $post Post.
     * @param array   $settings Settings.
     * @return array<int,array{name:string,url:string}>
     */
    private static function breadcrumbs_for_page( $post, $settings ) {
        $crumbs = array(
            array( 'name' => self::home_label( $settings ), 'url' => home_url( '/' ) ),
        );

        $ancestors = array_reverse( get_post_ancestors( $post ) );
        foreach ( $ancestors as $ancestor_id ) {
            $crumbs[] = array(
                'name' => get_the_title( $ancestor_id ),
                'url'  => get_permalink( $ancestor_id ),
            );
        }

        $crumbs[] = array(
            'name' => get_the_title( $post ),
            'url'  => get_permalink( $post ),
        );

        return $crumbs;
    }

    /**
     * @param WP_Post $post Page.
     * @return bool
     */
    private static function is_account_page( $post ) {
        $slug = $post->post_name;
        $keys = array( 'account', 'my-account', 'profile', 'login', 'register', 'sign-in', 'sign-up' );
        return in_array( $slug, $keys, true );
    }

    /**
     * @param array $context Context.
     * @param int   $attachment_id Attachment ID.
     */
    private static function apply_image( &$context, $attachment_id ) {
        $data = wp_get_attachment_image_src( $attachment_id, 'neonews-featured' );
        if ( ! $data ) {
            $data = wp_get_attachment_image_src( $attachment_id, 'full' );
        }
        if ( $data ) {
            $context['og_image']   = $data[0];
            $context['og_image_w'] = (int) $data[1];
            $context['og_image_h'] = (int) $data[2];
        }
    }

    /**
     * @return string
     */
    private static function default_image() {
        $settings = NeoNews_SEO::get_settings();
        $image_id = absint( $settings['default_og_image'] ?? 0 );
        if ( $image_id ) {
            $data = wp_get_attachment_image_src( $image_id, 'full' );
            return $data ? $data[0] : '';
        }
        $icon = get_site_icon_url( 512 );
        return $icon ? $icon : '';
    }

    /**
     * @return string
     */
    public static function separator() {
        $sep = NeoNews_SEO::get_settings( 'title_separator' );
        return $sep ? trim( (string) $sep ) : '|';
    }

    /**
     * @param array|null $settings Settings.
     * @return string
     */
    public static function home_label( $settings = null ) {
        if ( null === $settings ) {
            $settings = NeoNews_SEO::get_settings();
        }
        $label = trim( (string) ( $settings['breadcrumb_home_label'] ?? '' ) );
        return $label ? $label : __( 'Home', 'neonews-seo' );
    }

    /**
     * @return string
     */
    public static function current_url() {
        global $wp;
        if ( isset( $wp->request ) ) {
            return home_url( add_query_arg( array(), $wp->request ) );
        }
        return home_url( '/' );
    }

    /**
     * @param string $text Text.
     * @return string
     */
    public static function trim_description( $text ) {
        $text = wp_strip_all_tags( (string) $text );
        $text = preg_replace( '/\s+/', ' ', $text );
        if ( function_exists( 'mb_substr' ) ) {
            return mb_substr( $text, 0, 160 );
        }
        return substr( $text, 0, 160 );
    }
}
