<?php
/**
 * JSON-LD structured data.
 *
 * @package NeoNews_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_SEO_Schema {

    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        neonews_seo_load_context();

        add_action( 'wp_head', array( $this, 'output_schema' ), 6 );
    }

    public function output_schema() {
        if ( ! NeoNews_SEO::should_output() || ! NeoNews_SEO::get_settings( 'enable_schema' ) ) {
            return;
        }

        $graphs = array();

        if ( NeoNews_SEO::get_settings( 'enable_organization_schema' ) ) {
            $org = $this->organization_schema();
            if ( $org ) {
                $graphs[] = $org;
            }
        }

        $page = $this->page_schema();
        if ( $page ) {
            $graphs[] = $page;
        }

        if ( NeoNews_SEO::get_settings( 'enable_breadcrumb_schema' ) ) {
            $crumbs = $this->breadcrumb_schema();
            if ( $crumbs ) {
                $graphs[] = $crumbs;
            }
        }

        $graphs = apply_filters( 'neonews_seo_schema_graph', $graphs );
        if ( empty( $graphs ) ) {
            return;
        }

        $payload = count( $graphs ) > 1
            ? array( '@context' => 'https://schema.org', '@graph' => $graphs )
            : array_merge( array( '@context' => 'https://schema.org' ), $graphs[0] );

        printf(
            '<script type="application/ld+json">%s</script>' . "\n",
            wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
        );
    }

    /**
     * @return array|null
     */
    private function organization_schema() {
        $settings = NeoNews_SEO::get_settings();
        $name     = trim( (string) ( $settings['org_name'] ?? '' ) );
        if ( ! $name ) {
            $name = get_bloginfo( 'name' );
        }

        $schema = array(
            '@type' => 'Organization',
            '@id'   => home_url( '/#organization' ),
            'name'  => $name,
            'url'   => home_url( '/' ),
        );

        $logo_id = absint( $settings['org_logo'] ?? 0 );
        if ( ! $logo_id ) {
            $logo_id = absint( $settings['default_og_image'] ?? 0 );
        }
        if ( $logo_id ) {
            $logo = wp_get_attachment_image_src( $logo_id, 'full' );
            if ( $logo ) {
                $schema['logo'] = array(
                    '@type'  => 'ImageObject',
                    'url'    => esc_url( $logo[0] ),
                    'width'  => absint( $logo[1] ),
                    'height' => absint( $logo[2] ),
                );
            }
        } elseif ( get_site_icon_url( 512 ) ) {
            $schema['logo'] = array(
                '@type' => 'ImageObject',
                'url'   => esc_url( get_site_icon_url( 512 ) ),
            );
        }

        return $schema;
    }

    /**
     * @return array|null
     */
    private function page_schema() {
        $context = NeoNews_SEO_Context::get();
        $type    = $context['schema_type'] ?? 'WebPage';

        if ( 'WebSite' === $type && is_front_page() && NeoNews_SEO::get_settings( 'enable_website_schema' ) ) {
            return $this->website_schema( $context );
        }

        if ( 'NewsArticle' === $type && ! empty( $context['object_id'] ) ) {
            return $this->news_article_schema( (int) $context['object_id'], $context );
        }

        if ( 'ProfilePage' === $type && is_author() ) {
            return $this->author_schema();
        }

        return array(
            '@type'       => $type,
            '@id'         => esc_url( $context['canonical'] ) . '#webpage',
            'url'         => esc_url( $context['canonical'] ),
            'name'        => wp_strip_all_tags( $context['title'] ),
            'description' => NeoNews_SEO_Context::trim_description( $context['description'] ),
            'isPartOf'    => array(
                '@id' => home_url( '/#website' ),
            ),
        );
    }

    /**
     * @param array $context Context.
     * @return array
     */
    private function website_schema( $context ) {
        $schema = array(
            '@type'        => 'WebSite',
            '@id'          => home_url( '/#website' ),
            'url'          => home_url( '/' ),
            'name'         => get_bloginfo( 'name' ),
            'description'  => NeoNews_SEO_Context::trim_description( $context['description'] ),
            'publisher'    => array(
                '@id' => home_url( '/#organization' ),
            ),
            'potentialAction' => array(
                '@type'       => 'SearchAction',
                'target'      => array(
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => home_url( '/?s={search_term_string}' ),
                ),
                'query-input' => 'required name=search_term_string',
            ),
        );

        return $schema;
    }

    /**
     * @param int   $post_id Post ID.
     * @param array $context Context.
     * @return array
     */
    private function news_article_schema( $post_id, $context ) {
        $settings = NeoNews_SEO::get_settings();
        $desc     = NeoNews_SEO_Context::trim_description( $context['description'] );

        $schema = array(
            '@type'            => 'NewsArticle',
            '@id'              => get_permalink( $post_id ) . '#article',
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id'   => get_permalink( $post_id ),
            ),
            'headline'         => get_the_title( $post_id ),
            'description'      => $desc,
            'datePublished'    => get_the_date( 'c', $post_id ),
            'dateModified'     => get_the_modified_date( 'c', $post_id ),
            'author'           => array(
                '@type' => 'Person',
                'name'  => get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) ),
                'url'   => get_author_posts_url( (int) get_post_field( 'post_author', $post_id ) ),
            ),
            'publisher'        => array(
                '@id' => home_url( '/#organization' ),
            ),
            'isPartOf'         => array(
                '@id' => home_url( '/#website' ),
            ),
        );

        if ( has_post_thumbnail( $post_id ) ) {
            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );
            if ( $image ) {
                $schema['image'] = array(
                    '@type'  => 'ImageObject',
                    'url'    => esc_url( $image[0] ),
                    'width'  => absint( $image[1] ),
                    'height' => absint( $image[2] ),
                );
            }
        }

        if ( ! empty( $settings['article_section_category'] ) ) {
            $cats = get_the_category( $post_id );
            if ( ! empty( $cats ) ) {
                $schema['articleSection'] = $cats[0]->name;
            }
        }

        if ( ! empty( $settings['premium_paywall_schema'] ) && ! empty( $context['is_premium'] ) ) {
            $schema['isAccessibleForFree'] = false;
            $schema['hasPart']             = array(
                '@type'               => 'WebPageElement',
                'isAccessibleForFree' => false,
                'cssSelector'         => '.nn-premium-content',
            );
        }

        return apply_filters( 'neonews_seo_news_article_schema', $schema, $post_id );
    }

    /**
     * @return array|null
     */
    private function author_schema() {
        $author = get_queried_object();
        if ( ! $author instanceof WP_User ) {
            return null;
        }

        return array(
            '@type'       => 'ProfilePage',
            '@id'         => get_author_posts_url( $author->ID ) . '#profile',
            'url'         => get_author_posts_url( $author->ID ),
            'name'        => $author->display_name,
            'description' => get_the_author_meta( 'description', $author->ID ),
            'mainEntity'  => array(
                '@type' => 'Person',
                'name'  => $author->display_name,
                'url'   => get_author_posts_url( $author->ID ),
            ),
        );
    }

    /**
     * @return array|null
     */
    private function breadcrumb_schema() {
        $context = NeoNews_SEO_Context::get();
        if ( empty( $context['breadcrumbs'] ) || count( $context['breadcrumbs'] ) < 2 ) {
            return null;
        }

        $items = array();
        $pos   = 1;
        foreach ( $context['breadcrumbs'] as $crumb ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => $crumb['name'],
                'item'     => esc_url( $crumb['url'] ),
            );
        }

        return array(
            '@type'           => 'BreadcrumbList',
            '@id'             => esc_url( $context['canonical'] ) . '#breadcrumb',
            'itemListElement' => $items,
        );
    }
}
