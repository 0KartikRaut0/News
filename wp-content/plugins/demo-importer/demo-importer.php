<?php
/**
 * Plugin Name: NeoNews Demo Importer
 * Plugin URI: https://example.com/neonews
 * Description: One-click full site demo: posts, images, menus, widgets, homepage, and theme settings.
 * Version: 1.1.0
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: NeoNews Team
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: neonews-demo
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NEONEWS_DEMO_VERSION', '1.1.0' );
define( 'NEONEWS_DEMO_FILE', __FILE__ );
define( 'NEONEWS_DEMO_DIR', plugin_dir_path( __FILE__ ) );

require_once NEONEWS_DEMO_DIR . 'inc/class-site-setup.php';
require_once NEONEWS_DEMO_DIR . 'inc/class-demo-images.php';

/**
 * Demo Importer Class
 */
class NeoNews_Demo_Importer {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_ajax_neonews_import_demo', array( $this, 'ajax_import' ) );
        add_action( 'wp_ajax_neonews_remove_demo', array( $this, 'ajax_remove' ) );
        add_action( 'wp_ajax_neonews_fix_demo_images', array( $this, 'ajax_fix_images' ) );
    }

    public function add_admin_menu() {
        add_submenu_page(
            'neonews-settings',
            __( 'Demo Content', 'neonews-demo' ),
            __( 'Demo Content', 'neonews-demo' ),
            'manage_options',
            'neonews-demo',
            array( $this, 'render_page' )
        );

        if ( ! menu_page_url( 'neonews-settings', false ) ) {
            add_menu_page(
                __( 'NeoNews Demo', 'neonews-demo' ),
                __( 'NeoNews Demo', 'neonews-demo' ),
                'manage_options',
                'neonews-demo',
                array( $this, 'render_page' ),
                'dashicons-download',
                31
            );
        }
    }

    public function enqueue_scripts( $hook ) {
        if ( false === strpos( $hook, 'neonews-demo' ) ) {
            return;
        }

        wp_enqueue_style(
            'neonews-demo-admin',
            plugin_dir_url( NEONEWS_DEMO_FILE ) . 'admin.css',
            array(),
            NEONEWS_DEMO_VERSION
        );

        wp_enqueue_script(
            'neonews-demo-admin',
            plugin_dir_url( NEONEWS_DEMO_FILE ) . 'admin.js',
            array( 'jquery' ),
            NEONEWS_DEMO_VERSION,
            true
        );

        wp_localize_script( 'neonews-demo-admin', 'neonewsDemoData', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'neonews_demo_nonce' ),
            'i18n'    => array(
                'importing'     => __( 'Building your news site…', 'neonews-demo' ),
                'removing'      => __( 'Removing demo…', 'neonews-demo' ),
                'success'       => __( 'Full demo imported! Visit your homepage.', 'neonews-demo' ),
                'removed'       => __( 'Demo content removed.', 'neonews-demo' ),
                'error'         => __( 'Something failed. Enable PHP GD in Local if images are missing.', 'neonews-demo' ),
                'confirmRemove' => __( 'Remove all demo content, menus, widgets, and pages?', 'neonews-demo' ),
                'fixImages'     => __( 'Generating local images…', 'neonews-demo' ),
                'fixSuccess'    => __( 'Images attached successfully!', 'neonews-demo' ),
            ),
        ) );
    }

    public function render_page() {
        $imported = get_option( 'neonews_demo_imported', false );
        ?>
        <div class="wrap neonews-demo-wrap">
            <h1><?php esc_html_e( 'NeoNews — Full Site Demo', 'neonews-demo' ); ?></h1>
            <p class="description"><?php esc_html_e( 'One click sets up everything: youth-style homepage, menus, widgets, footer, pages, and copyright-free photos.', 'neonews-demo' ); ?></p>

            <div class="neonews-demo-card">
                <h2><?php esc_html_e( 'Import Turnkey Demo', 'neonews-demo' ); ?></h2>

                <div class="neonews-demo-features">
                    <h3><?php esc_html_e( 'Includes:', 'neonews-demo' ); ?></h3>
                    <ul>
                        <li><span class="dashicons dashicons-admin-home"></span> <?php esc_html_e( 'Homepage + Blog page configured', 'neonews-demo' ); ?></li>
                        <li><span class="dashicons dashicons-menu"></span> <?php esc_html_e( 'Header & footer menus (pre-linked)', 'neonews-demo' ); ?></li>
                        <li><span class="dashicons dashicons-welcome-widgets-menus"></span> <?php esc_html_e( 'Sidebar & footer widgets filled', 'neonews-demo' ); ?></li>
                        <li><span class="dashicons dashicons-admin-post"></span> <?php esc_html_e( '15+ articles with featured images', 'neonews-demo' ); ?></li>
                        <li><span class="dashicons dashicons-category"></span> <?php esc_html_e( '5 categories + trending/featured flags', 'neonews-demo' ); ?></li>
                        <li><span class="dashicons dashicons-admin-users"></span> <?php esc_html_e( 'Demo reporter & editor users', 'neonews-demo' ); ?></li>
                        <li><span class="dashicons dashicons-art"></span> <?php esc_html_e( 'Site title, tagline, colors, site icon', 'neonews-demo' ); ?></li>
                        <li><span class="dashicons dashicons-images-alt2"></span> <?php esc_html_e( 'Featured images generated locally (no internet needed)', 'neonews-demo' ); ?></li>
                    </ul>
                </div>

                <div class="neonews-demo-actions">
                    <?php if ( $imported ) : ?>
                        <p class="neonews-demo-status success">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e( 'Demo is active.', 'neonews-demo' ); ?>
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button" target="_blank" rel="noopener"><?php esc_html_e( 'View Site', 'neonews-demo' ); ?></a>
                        </p>
                        <button type="button" id="neonews-remove-demo" class="button button-secondary"><?php esc_html_e( 'Remove Demo', 'neonews-demo' ); ?></button>
                        <button type="button" id="neonews-import-demo" class="button"><?php esc_html_e( 'Re-run Import (safe)', 'neonews-demo' ); ?></button>
                        <button type="button" id="neonews-fix-images" class="button button-primary"><?php esc_html_e( 'Fix Missing Images', 'neonews-demo' ); ?></button>
                    <?php else : ?>
                        <button type="button" id="neonews-import-demo" class="button button-primary button-hero"><?php esc_html_e( 'Import Full Demo Now', 'neonews-demo' ); ?></button>
                        <button type="button" id="neonews-fix-images" class="button"><?php esc_html_e( 'Fix Missing Images Only', 'neonews-demo' ); ?></button>
                    <?php endif; ?>
                </div>

                <div id="neonews-demo-message" class="neonews-demo-message" style="display: none;"></div>
                <div id="neonews-demo-progress" class="neonews-demo-progress" style="display: none;">
                    <div class="progress-bar"><div class="progress-fill"></div></div>
                    <p class="progress-text"><?php esc_html_e( 'Generating images and configuring site…', 'neonews-demo' ); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    public function ajax_import() {
        check_ajax_referer( 'neonews_demo_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'neonews-demo' ) ) );
        }

        @set_time_limit( 300 );
        if ( function_exists( 'wp_raise_memory_limit' ) ) {
            wp_raise_memory_limit( 'admin' );
        } else {
            @ini_set( 'memory_limit', '512M' );
        }

        $result = $this->import_demo_content();

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        update_option( 'neonews_demo_imported', true );
        delete_transient( 'neonews_show_demo_notice' );

        wp_send_json_success( array(
            'message' => __( 'Your news site is ready! Open the homepage to preview.', 'neonews-demo' ),
            'stats'   => $result,
            'homeUrl' => home_url( '/' ),
        ) );
    }

    public function ajax_remove() {
        check_ajax_referer( 'neonews_demo_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'neonews-demo' ) ) );
        }

        $this->remove_demo_content();
        NeoNews_Demo_Site_Setup::teardown();

        delete_option( 'neonews_demo_imported' );

        wp_send_json_success( array( 'message' => __( 'Demo removed.', 'neonews-demo' ) ) );
    }

    /**
     * AJAX: generate missing featured images locally.
     */
    public function ajax_fix_images() {
        check_ajax_referer( 'neonews_demo_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'neonews-demo' ) ) );
        }

        if ( ! function_exists( 'imagecreatetruecolor' ) ) {
            wp_send_json_error( array(
                'message' => __( 'PHP GD extension is not enabled. Enable GD in Local → Site → PHP settings, then try again.', 'neonews-demo' ),
            ) );
        }

        @set_time_limit( 300 );

        $stats = NeoNews_Demo_Images::fix_missing_images();

        wp_send_json_success( array(
            'message' => sprintf(
                /* translators: %d: number of images created */
                __( 'Done! %d featured images were generated on your server.', 'neonews-demo' ),
                absint( $stats['attached'] )
            ),
            'stats'   => $stats,
            'homeUrl' => home_url( '/' ),
        ) );
    }

    private function import_demo_content() {
        $categories = $this->create_categories();
        $users      = $this->create_users();
        $post_map   = $this->create_posts( $categories, $users );

        $setup_stats = NeoNews_Demo_Site_Setup::configure( array(
            'categories' => $categories,
            'post_ids'   => $post_map,
        ) );

        return array_merge(
            array(
                'categories' => count( $categories ),
                'users'      => count( $users ),
                'posts'      => count( $post_map ),
            ),
            $setup_stats
        );
    }

    private function create_categories() {
        $demo_categories = array(
            array( 'name' => 'Politics', 'slug' => 'politics', 'description' => 'Power, policy & global affairs' ),
            array( 'name' => 'Technology', 'slug' => 'technology', 'description' => 'AI, gadgets & the future' ),
            array( 'name' => 'Business', 'slug' => 'business', 'description' => 'Markets, startups & money' ),
            array( 'name' => 'Sports', 'slug' => 'sports', 'description' => 'Games, athletes & hype' ),
            array( 'name' => 'Culture', 'slug' => 'entertainment', 'description' => 'Film, music & internet culture' ),
            array( 'name' => 'Videos', 'slug' => 'videos', 'description' => 'Watch the latest news & explainers' ),
        );

        $created = array();
        foreach ( $demo_categories as $cat_data ) {
            $existing = get_term_by( 'slug', $cat_data['slug'], 'category' );
            if ( $existing ) {
                $created[ $cat_data['slug'] ] = $existing->term_id;
                continue;
            }
            $result = wp_insert_term( $cat_data['name'], 'category', array(
                'slug'        => $cat_data['slug'],
                'description' => $cat_data['description'],
            ) );
            if ( ! is_wp_error( $result ) ) {
                $created[ $cat_data['slug'] ] = $result['term_id'];
                update_term_meta( $result['term_id'], '_neonews_demo_content', true );
            }
        }
        return $created;
    }

    private function create_users() {
        $demo_users = array(
            array(
                'user_login'   => 'demo_reporter',
                'user_email'   => 'reporter@example.com',
                'display_name' => 'Zara Malik',
                'role'         => 'nn_reporter',
                'description'  => 'Gen-Z beat reporter covering tech and culture.',
            ),
            array(
                'user_login'   => 'demo_editor',
                'user_email'   => 'editor@example.com',
                'display_name' => 'Alex Rivera',
                'role'         => 'editor',
                'description'  => 'Senior editor — AI, startups, and investigative features.',
            ),
        );

        $created = array();
        foreach ( $demo_users as $user_data ) {
            $existing = get_user_by( 'login', $user_data['user_login'] );
            if ( $existing ) {
                $created[] = $existing->ID;
                continue;
            }
            $user_id = wp_insert_user( array(
                'user_login'   => $user_data['user_login'],
                'user_email'   => $user_data['user_email'],
                'user_pass'    => wp_generate_password( 16, true, true ),
                'display_name' => $user_data['display_name'],
                'role'         => $user_data['role'],
                'description'  => $user_data['description'],
            ) );
            if ( ! is_wp_error( $user_id ) ) {
                $created[] = $user_id;
                update_user_meta( $user_id, '_neonews_demo_content', true );
            }
        }
        return $created;
    }

    private function create_posts( $categories, $users ) {
        $demo_posts = array(
            array( 'title' => 'BREAKING: Global AI Safety Pact Signed by 40 Nations', 'category' => 'politics', 'seed' => 'ai-pact', 'featured' => true, 'breaking' => true ),
            array( 'title' => 'This Startup Just Built an AI Newsroom in 72 Hours', 'category' => 'technology', 'seed' => 'ai-newsroom', 'featured' => true ),
            array( 'title' => 'Gen Z Is Redefining How We Read News — Here\'s the Data', 'category' => 'technology', 'seed' => 'genz-news', 'featured' => true ),
            array( 'title' => 'Champions League Final Breaks Every Streaming Record', 'category' => 'sports', 'seed' => 'ucl-final', 'featured' => true ),
            array( 'title' => 'The Film Everyone\'s Talking About Drops This Weekend', 'category' => 'entertainment', 'seed' => 'film-hype', 'featured' => true ),
            array( 'title' => 'Markets Rally After Surprise Rate Decision', 'category' => 'business', 'seed' => 'markets-rally', 'featured' => true ),
            array( 'title' => 'Inside the Viral Protest That Shut Down a City Block', 'category' => 'politics', 'seed' => 'viral-protest' ),
            array( 'title' => 'Open-Source Model Beats Big Tech on Benchmarks', 'category' => 'technology', 'seed' => 'open-source-ai' ),
            array( 'title' => 'Why Creators Are Leaving Traditional Media', 'category' => 'entertainment', 'seed' => 'creators-leave' ),
            array( 'title' => 'Electric Racing League Announces $2B Season', 'category' => 'sports', 'seed' => 'electric-racing' ),
            array( 'title' => 'Remote Work 2.0: Companies Mandate "Focus Fridays"', 'category' => 'business', 'seed' => 'remote-work' ),
            array( 'title' => 'Election Night in Photos: Youth Turnout Surges', 'category' => 'politics', 'seed' => 'election-youth' ),
            array( 'title' => 'Your Phone\'s Next Chip Could Be Made in Space', 'category' => 'technology', 'seed' => 'space-chip' ),
            array( 'title' => 'Streetwear Brand Becomes Billion-Dollar Media Company', 'category' => 'business', 'seed' => 'streetwear-media' ),
            array( 'title' => 'Playlist Politics: How TikTok Picks the Charts', 'category' => 'entertainment', 'seed' => 'tiktok-charts' ),
            array( 'title' => 'Watch: AI Safety Summit Highlights in 3 Minutes', 'category' => 'videos', 'seed' => 'video-ai-summit', 'youtube' => 'https://www.youtube.com/watch?v=aircAruvnKk', 'duration' => '12:48' ),
            array( 'title' => 'Watch: Inside the Viral Protest — Full Report', 'category' => 'videos', 'seed' => 'video-protest', 'youtube' => 'https://www.youtube.com/watch?v=HUngLiuC9B0', 'duration' => '8:22' ),
            array( 'title' => 'Watch: Champions League Final Best Moments', 'category' => 'videos', 'seed' => 'video-ucl', 'youtube' => 'https://www.youtube.com/watch?v=Gs069dndIYk', 'duration' => '6:15' ),
            array( 'title' => 'Watch: Gen Z News Habits Explained', 'category' => 'videos', 'seed' => 'video-genz', 'youtube' => 'https://www.youtube.com/watch?v=rfscFS0sjBw', 'duration' => '10:04' ),
            array( 'title' => 'Watch: Markets Explained in 5 Minutes', 'category' => 'videos', 'seed' => 'video-markets', 'youtube' => 'https://www.youtube.com/watch?v=ZCFkWDdmXG8', 'duration' => '5:01' ),
        );

        $post_image_map = array();

        foreach ( $demo_posts as $index => $post_data ) {
            $existing = get_page_by_title( $post_data['title'], OBJECT, 'post' );
            if ( $existing ) {
                $post_image_map[ $existing->ID ] = $post_data['seed'];
                continue;
            }

            $author_id   = ! empty( $users ) ? $users[ $index % count( $users ) ] : get_current_user_id();
            $category_id = isset( $categories[ $post_data['category'] ] ) ? $categories[ $post_data['category'] ] : 0;
            $post_date   = gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( $index * 6 ) . ' hours' ) );

            $post_id = wp_insert_post( array(
                'post_title'   => $post_data['title'],
                'post_content' => $this->get_article_content( $post_data['category'], $post_data['title'] ),
                'post_excerpt' => $this->get_excerpt( $post_data['category'] ),
                'post_status'  => 'publish',
                'post_type'    => 'post',
                'post_author'  => $author_id,
                'post_date'    => $post_date,
            ), true );

            if ( is_wp_error( $post_id ) ) {
                continue;
            }

            if ( $category_id ) {
                wp_set_post_categories( $post_id, array( $category_id ) );
            }

            update_post_meta( $post_id, '_neonews_demo_content', true );
            update_post_meta( $post_id, '_neonews_post_views', wp_rand( 800, 25000 ) );

            if ( ! empty( $post_data['featured'] ) ) {
                update_post_meta( $post_id, '_neonews_featured_post', '1' );
            }
            if ( ! empty( $post_data['breaking'] ) ) {
                update_post_meta( $post_id, '_neonews_breaking_news', '1' );
            }
            if ( ! empty( $post_data['youtube'] ) ) {
                update_post_meta( $post_id, '_neonews_youtube_url', esc_url_raw( $post_data['youtube'] ) );
            }
            if ( ! empty( $post_data['duration'] ) ) {
                update_post_meta( $post_id, '_neonews_video_duration', sanitize_text_field( $post_data['duration'] ) );
            }

            $post_image_map[ $post_id ] = $post_data['seed'];
        }

        return $post_image_map;
    }

    private function get_excerpt( $category ) {
        $map = array(
            'politics'      => 'Power plays, policy shifts, and the stories shaping democracy right now.',
            'technology'    => 'AI breakthroughs, gadgets, and the internet\'s next big wave.',
            'business'      => 'Money moves, market swings, and the founders to watch.',
            'sports'        => 'Highlights, upsets, and the culture around the game.',
            'entertainment' => 'What\'s trending, streaming, and worth your weekend.',
            'videos'        => 'Short-form video news, explainers, and highlights.',
        );
        return isset( $map[ $category ] ) ? $map[ $category ] : $map['technology'];
    }

    private function get_article_content( $category, $title ) {
        $lead = sprintf(
            '<p class="nn-lead"><strong>%s</strong> — %s</p>',
            esc_html( $title ),
            esc_html( $this->get_excerpt( $category ) )
        );

        $body = '<p>Sources close to the story say the development unfolded faster than analysts predicted, with social platforms amplifying reactions within minutes of the announcement.</p>';
        $body .= '<h2>What you need to know</h2><ul><li>Early data points to a major shift in audience behavior</li><li>Experts urge calm while details are verified</li><li>Brands and creators are already adapting their strategies</li></ul>';
        $body .= '<p>We will update this story as more information becomes available. Have a tip? Use our Submit Story page.</p>';
        $body .= '<blockquote><p>“This is the kind of moment that defines a generation’s relationship with news,” said one industry observer.</p></blockquote>';
        $body .= '<p>Stay with NeoPulse for live updates, explainers, and exclusive context you will not get from a basic headline scroll.</p>';

        return $lead . $body;
    }

    private function remove_demo_content() {
        global $wpdb;

        $demo_posts = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
                '_neonews_demo_content',
                '1'
            )
        );
        foreach ( $demo_posts as $post_id ) {
            wp_delete_post( $post_id, true );
        }

        $demo_terms = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key = %s AND meta_value = %s",
                '_neonews_demo_content',
                '1'
            )
        );
        foreach ( $demo_terms as $term_id ) {
            $term = get_term( $term_id );
            if ( $term && ! is_wp_error( $term ) ) {
                wp_delete_term( $term_id, $term->taxonomy );
            }
        }

        $demo_users = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s",
                '_neonews_demo_content',
                '1'
            )
        );
        foreach ( $demo_users as $user_id ) {
            if ( get_current_user_id() !== (int) $user_id ) {
                wp_delete_user( $user_id );
            }
        }
    }
}

NeoNews_Demo_Importer::get_instance();
