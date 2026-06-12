<?php
/**
 * SEO Lite settings page.
 *
 * @package NeoNews_SEO_Lite
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin settings.
 */
final class NeoNews_SEO_Lite_Admin {

    /** @var self|null */
    private static $instance = null;

    /**
     * @return self
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'menu' ), 23 );
        add_action( 'admin_init', array( $this, 'register' ) );
        add_action( 'update_option_' . NEONEWS_SEO_LITE_OPTION, array( $this, 'keep_off_autoload' ) );
    }

    /**
     * @return void
     */
    public function keep_off_autoload() {
        global $wpdb;

        $wpdb->update(
            $wpdb->options,
            array( 'autoload' => 'no' ),
            array( 'option_name' => NEONEWS_SEO_LITE_OPTION ),
            array( '%s' ),
            array( '%s' )
        );
        wp_cache_delete( NEONEWS_SEO_LITE_OPTION, 'options' );
        NeoNews_SEO_Lite::flush_settings_cache();
    }

    /**
     * @return void
     */
    public function menu() {
        $parent = 'neonews-settings';

        if ( ! $this->news_pulse_menu_exists() ) {
            add_menu_page(
                __( 'SEO Lite', 'neonews-seo-lite' ),
                __( 'SEO Lite', 'neonews-seo-lite' ),
                'manage_options',
                'neonews-seo-lite',
                array( $this, 'render' ),
                'dashicons-search',
                81
            );
            return;
        }

        add_submenu_page(
            $parent,
            __( 'SEO Lite', 'neonews-seo-lite' ),
            __( 'SEO Lite', 'neonews-seo-lite' ),
            'manage_options',
            'neonews-seo-lite',
            array( $this, 'render' )
        );
    }

    /**
     * @return bool
     */
    private function news_pulse_menu_exists() {
        global $menu;

        if ( ! is_array( $menu ) ) {
            return false;
        }

        foreach ( $menu as $item ) {
            if ( isset( $item[2] ) && 'neonews-settings' === $item[2] ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return void
     */
    public function register() {
        register_setting(
            'neonews_seo_lite_group',
            NEONEWS_SEO_LITE_OPTION,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize' ),
            )
        );
    }

    /**
     * @param array<string,mixed> $input Input.
     * @return array<string,mixed>
     */
    public function sanitize( $input ) {
        $out = NeoNews_SEO_Lite::settings();

        $bools = array(
            'enabled',
            'replace_theme_seo',
            'enable_schema',
            'enable_breadcrumb_schema',
            'enable_breadcrumbs',
            'noindex_search',
            'noindex_author',
            'sitemap_posts',
            'sitemap_pages',
            'sitemap_categories',
            'sitemap_tags',
            'sitemap_authors',
            'sitemap_exclude_noindex',
        );

        foreach ( $bools as $key ) {
            $out[ $key ] = ! empty( $input[ $key ] );
        }

        $out['home_description']       = sanitize_textarea_field( $input['home_description'] ?? '' );
        $out['title_separator']        = sanitize_text_field( $input['title_separator'] ?? '|' );
        $out['default_og_image']       = esc_url_raw( $input['default_og_image'] ?? '' );
        $out['twitter_site']           = sanitize_text_field( $input['twitter_site'] ?? '' );
        $out['org_name']               = sanitize_text_field( $input['org_name'] ?? '' );
        $out['breadcrumb_home_label']  = sanitize_text_field( $input['breadcrumb_home_label'] ?? '' );

        if ( ! empty( $out['enabled'] ) ) {
            neonews_seo_lite_pause_full_seo();
        }

        return $out;
    }

    /**
     * @return void
     */
    public function render() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $s = NeoNews_SEO_Lite::settings();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'NeoNews SEO Lite', 'neonews-seo-lite' ); ?></h1>
            <p><?php esc_html_e( 'Lightweight SEO designed for low-memory hosts. Deactivate the full NeoNews SEO plugin when using this.', 'neonews-seo-lite' ); ?></p>

            <form method="post" action="options.php">
                <?php settings_fields( 'neonews_seo_lite_group' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enable', 'neonews-seo-lite' ); ?></th>
                        <td><label><input type="checkbox" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[enabled]" value="1" <?php checked( ! empty( $s['enabled'] ) ); ?> /> <?php esc_html_e( 'Enable SEO Lite', 'neonews-seo-lite' ); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Theme SEO', 'neonews-seo-lite' ); ?></th>
                        <td><label><input type="checkbox" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[replace_theme_seo]" value="1" <?php checked( ! empty( $s['replace_theme_seo'] ) ); ?> /> <?php esc_html_e( 'Replace theme SEO output', 'neonews-seo-lite' ); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Homepage description', 'neonews-seo-lite' ); ?></th>
                        <td><textarea name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[home_description]" rows="3" class="large-text"><?php echo esc_textarea( $s['home_description'] ); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Title separator', 'neonews-seo-lite' ); ?></th>
                        <td><input type="text" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[title_separator]" value="<?php echo esc_attr( $s['title_separator'] ); ?>" class="small-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Default social image URL', 'neonews-seo-lite' ); ?></th>
                        <td>
                            <input type="url" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[default_og_image]" value="<?php echo esc_attr( $s['default_og_image'] ); ?>" class="large-text" placeholder="https://..." />
                            <p class="description"><?php esc_html_e( 'Used when a post has no featured image. Paste a direct image URL (no media library).', 'neonews-seo-lite' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Twitter @site', 'neonews-seo-lite' ); ?></th>
                        <td><input type="text" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[twitter_site]" value="<?php echo esc_attr( $s['twitter_site'] ); ?>" class="regular-text" placeholder="@yournews" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Organization name', 'neonews-seo-lite' ); ?></th>
                        <td><input type="text" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[org_name]" value="<?php echo esc_attr( $s['org_name'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Schema', 'neonews-seo-lite' ); ?></th>
                        <td>
                            <label><input type="checkbox" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[enable_schema]" value="1" <?php checked( ! empty( $s['enable_schema'] ) ); ?> /> <?php esc_html_e( 'Output JSON-LD (Organization, WebSite, NewsArticle)', 'neonews-seo-lite' ); ?></label><br />
                            <label><input type="checkbox" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[enable_breadcrumb_schema]" value="1" <?php checked( ! empty( $s['enable_breadcrumb_schema'] ) ); ?> /> <?php esc_html_e( 'BreadcrumbList schema', 'neonews-seo-lite' ); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Indexing', 'neonews-seo-lite' ); ?></th>
                        <td>
                            <label><input type="checkbox" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[noindex_search]" value="1" <?php checked( ! empty( $s['noindex_search'] ) ); ?> /> <?php esc_html_e( 'Noindex search results', 'neonews-seo-lite' ); ?></label><br />
                            <label><input type="checkbox" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[noindex_author]" value="1" <?php checked( ! empty( $s['noindex_author'] ) ); ?> /> <?php esc_html_e( 'Noindex author archives', 'neonews-seo-lite' ); ?></label>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Breadcrumbs', 'neonews-seo-lite' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Navigation', 'neonews-seo-lite' ); ?></th>
                        <td><label><input type="checkbox" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[enable_breadcrumbs]" value="1" <?php checked( ! empty( $s['enable_breadcrumbs'] ) ); ?> /> <?php esc_html_e( 'Show breadcrumbs on posts and archives', 'neonews-seo-lite' ); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Home label', 'neonews-seo-lite' ); ?></th>
                        <td><input type="text" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[breadcrumb_home_label]" value="<?php echo esc_attr( $s['breadcrumb_home_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Home', 'neonews-seo-lite' ); ?>" /></td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Sitemap', 'neonews-seo-lite' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'WordPress sitemap', 'neonews-seo-lite' ); ?></th>
                        <td>
                            <p>
                                <a href="<?php echo esc_url( home_url( '/wp-sitemap.xml' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( home_url( '/wp-sitemap.xml' ) ); ?></a>
                            </p>
                            <label><input type="checkbox" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[sitemap_posts]" value="1" <?php checked( ! empty( $s['sitemap_posts'] ) ); ?> /> <?php esc_html_e( 'Include posts', 'neonews-seo-lite' ); ?></label><br />
                            <label><input type="checkbox" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[sitemap_pages]" value="1" <?php checked( ! empty( $s['sitemap_pages'] ) ); ?> /> <?php esc_html_e( 'Include pages', 'neonews-seo-lite' ); ?></label><br />
                            <label><input type="checkbox" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[sitemap_categories]" value="1" <?php checked( ! empty( $s['sitemap_categories'] ) ); ?> /> <?php esc_html_e( 'Include categories', 'neonews-seo-lite' ); ?></label><br />
                            <label><input type="checkbox" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[sitemap_tags]" value="1" <?php checked( ! empty( $s['sitemap_tags'] ) ); ?> /> <?php esc_html_e( 'Include tags', 'neonews-seo-lite' ); ?></label><br />
                            <label><input type="checkbox" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[sitemap_authors]" value="1" <?php checked( ! empty( $s['sitemap_authors'] ) ); ?> /> <?php esc_html_e( 'Include author archives', 'neonews-seo-lite' ); ?></label><br />
                            <label><input type="checkbox" name="<?php echo esc_attr( NEONEWS_SEO_LITE_OPTION ); ?>[sitemap_exclude_noindex]" value="1" <?php checked( ! empty( $s['sitemap_exclude_noindex'] ) ); ?> /> <?php esc_html_e( 'Exclude posts marked noindex', 'neonews-seo-lite' ); ?></label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
