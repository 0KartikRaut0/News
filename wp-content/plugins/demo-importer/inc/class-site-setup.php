<?php
/**
 * Full site setup: pages, menus, widgets, images, theme mods.
 *
 * @package NeoNews_Demo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Site Setup Class
 */
class NeoNews_Demo_Site_Setup {

    /**
     * Run complete site configuration after content import.
     *
     * @param array $context Import context (categories, post_ids, etc.).
     * @return array Stats.
     */
    public static function configure( $context = array() ) {
        $stats = array(
            'pages'   => 0,
            'menus'   => 0,
            'widgets' => 0,
            'images'  => 0,
        );

        $pages = self::create_pages();
        $stats['pages'] = count( $pages );

        self::configure_reading_settings( $pages );
        self::create_menus( $pages, $context );
        $stats['menus'] = 3;

        self::configure_widgets( $pages, $context );
        $stats['widgets'] = 1;

        self::configure_theme_mods();
        self::configure_site_identity();

        if ( ! empty( $context['post_ids'] ) ) {
            $stats['images'] = self::attach_featured_images( $context['post_ids'] );
            self::mark_demo_premium_posts( $context['post_ids'] );
        }

        self::attach_site_icon();
        flush_rewrite_rules();

        if ( function_exists( 'neonews_ensure_legal_pages' ) ) {
            neonews_ensure_legal_pages();
        }

        if ( function_exists( 'neonews_ensure_about_page' ) ) {
            neonews_ensure_about_page();
        }

        if ( function_exists( 'neonews_membership_ensure_exclusive_page' ) ) {
            neonews_membership_ensure_exclusive_page();
        }

        if ( function_exists( 'neonews_sync_primary_nav_menus' ) ) {
            neonews_sync_primary_nav_menus();
        }

        return $stats;
    }

    /**
     * Create essential pages.
     */
    private static function create_pages() {
        $pages_def = array(
            'home' => array(
                'title'    => 'Home',
                'content'  => '',
                'template' => '',
            ),
            'blog' => array(
                'title'   => 'Latest News',
                'content' => '',
            ),
            'about' => array(
                'title'    => 'About Us',
                'content'  => self::get_about_content(),
                'template' => 'templates/template-about.php',
            ),
            'contact' => array(
                'title'    => 'Contact',
                'content'  => self::get_contact_content(),
                'template' => 'templates/template-contact.php',
            ),
            'my-account' => array(
                'title'    => 'My Account',
                'content'  => '',
                'template' => 'templates/template-account.php',
            ),
            'my-profile' => array(
                'title'    => 'My Profile',
                'content'  => '',
                'template' => 'templates/template-profile.php',
            ),
            'careers' => array(
                'title'    => 'Careers',
                'content'  => self::get_careers_content(),
                'template' => 'templates/template-careers.php',
            ),
            'advertise' => array(
                'title'    => 'Advertise',
                'content'  => self::get_advertise_content(),
                'template' => 'templates/template-advertise.php',
            ),
            'privacy-policy' => array(
                'title'    => 'Privacy Policy',
                'content'  => self::get_privacy_content(),
                'template' => 'templates/template-legal.php',
            ),
            'terms' => array(
                'title'    => 'Terms of Service',
                'content'  => self::get_terms_content(),
                'template' => 'templates/template-legal.php',
            ),
            'cookies' => array(
                'title'    => 'Cookie Policy',
                'content'  => self::get_cookies_content(),
                'template' => 'templates/template-legal.php',
            ),
            'cache-policy' => array(
                'title'    => 'Cache & Storage Policy',
                'content'  => self::get_cache_content(),
                'template' => 'templates/template-legal.php',
            ),
            'submit' => array(
                'title'   => 'Submit Story',
                'content' => '[neonews_submit_form]',
            ),
            'subscribe' => array(
                'title'   => 'Go Premium',
                'content' => self::get_subscribe_content(),
            ),
            'exclusive' => array(
                'title'    => 'Exclusive News',
                'slug'     => 'exclusive',
                'content'  => self::get_exclusive_content(),
                'template' => 'templates/template-exclusive.php',
            ),
        );

        $pages = array();

        foreach ( $pages_def as $key => $data ) {
            $slug = ! empty( $data['slug'] ) ? $data['slug'] : sanitize_title( $key );
            $existing = get_page_by_path( $slug );
            if ( ! $existing && ! empty( $data['title'] ) ) {
                $existing = get_page_by_path( sanitize_title( $data['title'] ) );
            }
            if ( $existing ) {
                $pages[ $key ] = $existing->ID;
                if ( ! empty( $data['template'] ) ) {
                    update_post_meta( $existing->ID, '_wp_page_template', $data['template'] );
                }
                continue;
            }

            $page_id = wp_insert_post( array(
                'post_title'   => $data['title'],
                'post_name'    => $slug,
                'post_content' => $data['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => get_current_user_id() ?: 1,
            ), true );

            if ( is_wp_error( $page_id ) ) {
                continue;
            }

            if ( ! empty( $data['template'] ) ) {
                update_post_meta( $page_id, '_wp_page_template', $data['template'] );
            }

            update_post_meta( $page_id, '_neonews_demo_content', true );
            $pages[ $key ] = $page_id;
        }

        return $pages;
    }

    /**
     * Reading settings.
     */
    private static function configure_reading_settings( $pages ) {
        if ( ! empty( $pages['home'] ) ) {
            update_option( 'show_on_front', 'page' );
            update_option( 'page_on_front', $pages['home'] );
        }
        if ( ! empty( $pages['blog'] ) ) {
            update_option( 'page_for_posts', $pages['blog'] );
        }
        update_option( 'posts_per_page', 9 );
    }

    /**
     * Create and assign navigation menus.
     */
    private static function create_menus( $pages, $context ) {
        $categories = isset( $context['categories'] ) ? $context['categories'] : array();

        $primary_id = self::get_or_create_menu( 'NeoNews Main Menu' );
        $footer_id  = self::get_or_create_menu( 'NeoNews Footer Menu' );

        self::clear_menu_items( $primary_id );
        self::clear_menu_items( $footer_id );

        $order = 1;
        if ( ! empty( $pages['home'] ) ) {
            self::add_page_to_menu( $primary_id, $pages['home'], $order++, 'Home' );
        }

        if ( ! empty( $pages['exclusive'] ) ) {
            self::add_page_to_menu( $primary_id, $pages['exclusive'], $order++, '★ Exclusive', 'nn-nav-exclusive nn-nav-premium-item' );
        }

        foreach ( array( 'politics', 'technology', 'business', 'sports', 'entertainment', 'videos' ) as $slug ) {
            if ( isset( $categories[ $slug ] ) ) {
                self::add_term_to_menu( $primary_id, $categories[ $slug ], 'category', $order++ );
            }
        }

        if ( ! empty( $pages['blog'] ) ) {
            self::add_page_to_menu( $primary_id, $pages['blog'], $order++, 'All News' );
        }
        if ( ! empty( $pages['submit'] ) ) {
            self::add_page_to_menu( $primary_id, $pages['submit'], $order++, 'Submit Story' );
        }

        $company_parent = wp_update_nav_menu_item( $primary_id, 0, array(
            'menu-item-title'     => 'Company',
            'menu-item-url'       => '#',
            'menu-item-type'      => 'custom',
            'menu-item-status'    => 'publish',
            'menu-item-position'  => $order++,
            'menu-item-classes'   => 'nn-nav-company-parent menu-item-has-children',
        ) );

        $child_order = 1;
        foreach ( array( 'about', 'careers', 'advertise', 'contact' ) as $key ) {
            if ( ! empty( $pages[ $key ] ) ) {
                wp_update_nav_menu_item( $primary_id, 0, array(
                    'menu-item-title'     => get_the_title( $pages[ $key ] ),
                    'menu-item-object'    => 'page',
                    'menu-item-object-id' => $pages[ $key ],
                    'menu-item-type'      => 'post_type',
                    'menu-item-status'    => 'publish',
                    'menu-item-parent-id' => $company_parent,
                    'menu-item-position'  => $child_order++,
                ) );
            }
        }

        $forder = 1;
        foreach ( array( 'about', 'contact', 'careers', 'advertise', 'exclusive', 'my-account', 'subscribe', 'blog' ) as $key ) {
            if ( ! empty( $pages[ $key ] ) ) {
                self::add_page_to_menu( $footer_id, $pages[ $key ], $forder++ );
            }
        }

        $locations = get_theme_mod( 'nav_menu_locations', array() );
        $locations['primary'] = $primary_id;
        $locations['footer']  = $footer_id;
        $locations['mobile']  = $primary_id;
        set_theme_mod( 'nav_menu_locations', $locations );

        update_term_meta( $primary_id, '_neonews_demo_content', true );
        update_term_meta( $footer_id, '_neonews_demo_content', true );
    }

    /**
     * Configure sidebar and footer widgets.
     */
    private static function configure_widgets( $pages, $context ) {
        $site_name = get_bloginfo( 'name' );

        self::setup_text_widget( 1, 'About ' . $site_name, sprintf(
            '<p><strong>%s</strong> delivers fast, visual news for the digital generation — politics, tech, culture, and everything in between.</p><p><a href="%s">Learn more →</a></p>',
            esc_html( $site_name ),
            ! empty( $pages['about'] ) ? esc_url( get_permalink( $pages['about'] ) ) : '#'
        ) );

        self::setup_text_widget( 2, 'Stay in the Loop', '<p>Get top stories in your inbox. Replace this widget with your newsletter form.</p><p><a class="nn-btn" href="' . ( ! empty( $pages['subscribe'] ) ? esc_url( get_permalink( $pages['subscribe'] ) ) : '#' ) . '">Subscribe Free</a></p>' );

        self::setup_recent_posts_widget( 3, 'Fresh Reads', 5 );
        self::setup_categories_widget( 4 );

        $sidebars = get_option( 'sidebars_widgets', array() );
        if ( ! is_array( $sidebars ) ) {
            $sidebars = array();
        }

        $sidebars['sidebar-1']  = array();
        $sidebars['footer-1']   = array( 'text-1' );
        $sidebars['footer-2']   = array( 'nav_menu-5' );
        self::setup_categories_widget( 6, 'Topics' );
        $sidebars['footer-3']   = array( 'categories-6' );
        $sidebars['footer-4']   = array( 'text-2' );
        $sidebars['wp_inactive_widgets'] = array();

        update_option( 'sidebars_widgets', $sidebars );

        self::setup_nav_menu_widget( 5, 'Quick Links', self::get_or_create_menu( 'NeoNews Footer Menu' ) );
    }

    /**
     * Theme customizer defaults.
     */
    private static function configure_theme_mods() {
        set_theme_mod( 'neonews_default_theme_mode', 'light' );
        set_theme_mod( 'neonews_show_breaking_news', true );
        set_theme_mod( 'neonews_show_featured_carousel', true );
        set_theme_mod( 'neonews_posts_per_row', 3 );
        set_theme_mod( 'neonews_primary_color', '#ff7a18' );
        set_theme_mod( 'neonews_secondary_color', '#1c1917' );
        set_theme_mod( 'neonews_footer_text', '© ' . gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '. Demo content — replace with your brand.' );
    }

    /**
     * Site title and tagline.
     */
    private static function configure_site_identity() {
        if ( 'WordPress' === get_option( 'blogname' ) || empty( get_option( 'blogname' ) ) ) {
            update_option( 'blogname', 'NewsPulse' );
        }
        if ( empty( get_option( 'blogdescription' ) ) || false !== strpos( get_option( 'blogdescription' ), 'Just another' ) ) {
            update_option( 'blogdescription', 'Smart reporting from every beat — politics, business, tech, sports & culture.' );
        }
    }

    /**
     * Sideload featured images (Picsum — free for commercial use).
     *
     * @param array $post_image_map Post ID => seed slug.
     */
    public static function attach_featured_images( $post_image_map ) {
        return NeoNews_Demo_Images::attach_featured_images( $post_image_map, false );
    }

    /**
     * Site icon — generated locally.
     */
    private static function attach_site_icon() {
        NeoNews_Demo_Images::create_site_icon();
    }

    /**
     * Upload local file to media library.
     */
    private static function upload_file_as_attachment( $file_path, $post_id, $desc ) {
        $filetype = wp_check_filetype( basename( $file_path ), null );
        $upload_dir = wp_upload_dir();

        $filename = wp_unique_filename( $upload_dir['path'], basename( $file_path ) );
        $new_file = $upload_dir['path'] . '/' . $filename;

        if ( ! copy( $file_path, $new_file ) ) {
            return 0;
        }

        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title'     => sanitize_file_name( $desc ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        $attach_id = wp_insert_attachment( $attachment, $new_file, $post_id );
        if ( ! is_wp_error( $attach_id ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $attach_data = wp_generate_attachment_metadata( $attach_id, $new_file );
            wp_update_attachment_metadata( $attach_id, $attach_data );
            update_option( 'site_icon', $attach_id );
            return $attach_id;
        }

        return 0;
    }

    /**
     * Remove demo site configuration.
     */
    public static function teardown() {
        $pages = get_posts( array(
            'post_type'      => 'page',
            'posts_per_page' => -1,
            'meta_key'       => '_neonews_demo_content',
            'meta_value'     => '1',
            'fields'         => 'ids',
        ) );

        foreach ( $pages as $page_id ) {
            wp_delete_post( $page_id, true );
        }

        $menus = wp_get_nav_menus();
        foreach ( $menus as $menu ) {
            if ( get_term_meta( $menu->term_id, '_neonews_demo_content', true ) ) {
                wp_delete_nav_menu( $menu->term_id );
            }
        }

        set_theme_mod( 'nav_menu_locations', array() );

        $attachments = get_posts( array(
            'post_type'      => 'attachment',
            'posts_per_page' => -1,
            'meta_key'       => '_neonews_demo_content',
            'meta_value'     => '1',
            'fields'         => 'ids',
        ) );

        foreach ( $attachments as $aid ) {
            wp_delete_attachment( $aid, true );
        }

        if ( get_option( 'site_icon' ) && in_array( get_option( 'site_icon' ), $attachments, true ) ) {
            delete_option( 'site_icon' );
        }

        update_option( 'show_on_front', 'posts' );
        delete_option( 'page_on_front' );
        delete_option( 'page_for_posts' );
    }

    private static function get_or_create_menu( $name ) {
        $menu = wp_get_nav_menu_object( $name );
        if ( $menu ) {
            return (int) $menu->term_id;
        }
        return (int) wp_create_nav_menu( $name );
    }

    private static function clear_menu_items( $menu_id ) {
        $items = wp_get_nav_menu_items( $menu_id );
        if ( $items ) {
            foreach ( $items as $item ) {
                wp_delete_post( $item->ID, true );
            }
        }
    }

    private static function add_page_to_menu( $menu_id, $page_id, $order, $title = '', $classes = '' ) {
        wp_update_nav_menu_item( $menu_id, 0, array(
            'menu-item-title'     => $title ? $title : get_the_title( $page_id ),
            'menu-item-object'    => 'page',
            'menu-item-object-id' => $page_id,
            'menu-item-type'      => 'post_type',
            'menu-item-status'    => 'publish',
            'menu-item-position'  => $order,
            'menu-item-classes'   => $classes,
        ) );
    }

    /**
     * Mark sample posts as premium for exclusive/home sections.
     *
     * @param array $post_map Post map from import.
     */
    private static function mark_demo_premium_posts( $post_map ) {
        if ( function_exists( 'neonews_membership_seed_premium_posts' ) ) {
            neonews_membership_seed_premium_posts( 4 );
            update_option( 'neonews_premium_demo_seeded', 1 );
            return;
        }

        if ( ! is_array( $post_map ) ) {
            return;
        }

        $ids = array_values( $post_map );
        $pick = array_slice( $ids, 0, 4 );
        foreach ( $pick as $post_id ) {
            update_post_meta( (int) $post_id, '_neonews_premium_post', '1' );
            update_post_meta( (int) $post_id, '_neonews_premium_on_home', '1' );
        }
    }

    private static function get_exclusive_content() {
        return '<div class="nn-exclusive-intro"><p>Welcome to our premium newsroom — member-only investigations, early access, and ad-free deep dives.</p><ul><li>Exclusive long-form reporting</li><li>Early access stories</li><li>Premium member benefits</li></ul></div>';
    }

    private static function add_term_to_menu( $menu_id, $term_id, $taxonomy, $order ) {
        $term = get_term( $term_id, $taxonomy );
        if ( ! $term || is_wp_error( $term ) ) {
            return;
        }
        wp_update_nav_menu_item( $menu_id, 0, array(
            'menu-item-title'     => $term->name,
            'menu-item-object'    => $taxonomy,
            'menu-item-object-id' => $term_id,
            'menu-item-type'      => 'taxonomy',
            'menu-item-status'    => 'publish',
            'menu-item-position'  => $order,
        ) );
    }

    private static function setup_text_widget( $id, $title, $text ) {
        $widgets = get_option( 'widget_text', array() );
        $widgets[ $id ] = array(
            'title'  => $title,
            'text'   => $text,
            'filter' => true,
        );
        update_option( 'widget_text', $widgets );
    }

    private static function setup_recent_posts_widget( $id, $title, $number ) {
        $widgets = get_option( 'widget_recent-posts', array() );
        $widgets[ $id ] = array(
            'title'  => $title,
            'number' => $number,
        );
        update_option( 'widget_recent-posts', $widgets );
    }

    private static function setup_categories_widget( $id, $title = 'Explore Topics' ) {
        $widgets = get_option( 'widget_categories', array() );
        $widgets[ $id ] = array(
            'title'        => $title,
            'count'        => 1,
            'hierarchical' => 0,
            'dropdown'     => 0,
        );
        update_option( 'widget_categories', $widgets );
    }

    private static function setup_nav_menu_widget( $id, $title, $menu_id ) {
        $widgets = get_option( 'widget_nav_menu', array() );
        $widgets[ $id ] = array(
            'title'    => $title,
            'nav_menu' => $menu_id,
        );
        update_option( 'widget_nav_menu', $widgets );
    }

    private static function get_about_content() {
        return '<h3>Our Story</h3><p>We are a next-gen newsroom built for speed, clarity, and visual storytelling. Our mission is to make complex stories simple for readers worldwide.</p><h3>Our Values</h3><ul><li><strong>Truth first</strong> — verified reporting you can trust</li><li><strong>Design matters</strong> — stories you actually want to read</li><li><strong>Community</strong> — readers, members, and contributors together</li></ul>';
    }

    private static function get_contact_content() {
        return '<p>Have a tip, partnership idea, or press inquiry? We read every message.</p>';
    }

    private static function get_careers_content() {
        return '<h3>Open Roles</h3><ul><li><strong>Senior Reporter</strong> — Technology & AI beat</li><li><strong>Video Producer</strong> — Short-form news content</li><li><strong>Product Designer</strong> — Reader experience & mobile</li></ul><p>Send your portfolio to careers@' . esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ) . '</p>';
    }

    private static function get_advertise_content() {
        return '<p>Partner with us to reach a highly engaged audience. Contact <a href="mailto:ads@example.com">ads@example.com</a> for rate cards and custom packages.</p>';
    }

    private static function get_privacy_content() {
        return '<p>We respect your privacy. This policy explains what data we collect, how we use it, and your rights.</p><h2>Data we collect</h2><p>When you browse our site, we may collect analytics data, cookies, and account information if you register.</p><h2>How we use data</h2><p>We use data to improve our service, personalize content, and communicate with subscribers.</p>';
    }

    private static function get_terms_content() {
        return '<p>By using this website, you agree to these terms. Content is for informational purposes. Do not reproduce without permission.</p><h2>User accounts</h2><p>Members are responsible for keeping credentials secure. Abuse may result in account suspension.</p>';
    }

    private static function get_cookies_content() {
        return '<p>We use cookies to remember preferences, analyze traffic, and improve your experience.</p><h2>Types of cookies</h2><ul><li><strong>Essential</strong> — required for login and security</li><li><strong>Analytics</strong> — help us understand usage</li><li><strong>Preferences</strong> — theme mode and settings</li></ul>';
    }

    private static function get_cache_content() {
        return '<p>We use browser and server caching to deliver faster page loads.</p><h2>What we store</h2><ul><li><strong>Browser cache</strong> — images, styles, and scripts</li><li><strong>Session data</strong> — login state and preferences</li><li><strong>CDN cache</strong> — static assets for speed</li></ul><p>You can clear cache via your browser settings at any time.</p>';
    }

    private static function get_subscribe_content() {
        return '<div class="nn-premium-notice"><h3>Unlock Premium</h3><p>Unlimited articles, exclusive deep dives, and ad-free reading. Payment integration coming soon — admins can grant premium status from user profiles.</p></div>[membership_status]';
    }
}
