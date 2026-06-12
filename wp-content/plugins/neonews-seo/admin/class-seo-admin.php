<?php
/**
 * SEO admin settings page.
 *
 * @package NeoNews_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_SEO_Admin {

    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'menu' ), 22 );
        add_action( 'admin_init', array( $this, 'register' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
    }

    public function menu() {
        add_submenu_page(
            'neonews-settings',
            __( 'SEO', 'neonews-seo' ),
            __( 'SEO', 'neonews-seo' ),
            'manage_options',
            'neonews-seo',
            array( $this, 'render_page' )
        );
    }

    public function register() {
        register_setting(
            'neonews_seo_group',
            'neonews_seo_settings',
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize' ),
            )
        );
    }

    /**
     * @param string $hook Hook.
     */
    public function assets( $hook ) {
        if ( false === strpos( $hook, 'neonews-seo' ) && false === strpos( $hook, 'post.php' ) && false === strpos( $hook, 'post-new.php' ) && false === strpos( $hook, 'edit-tags.php' ) ) {
            return;
        }
        wp_enqueue_style( 'neonews-seo-admin', NEONEWS_SEO_URL . 'admin/css/admin.css', array(), NEONEWS_SEO_VERSION );
        wp_enqueue_media();
        wp_enqueue_script(
            'neonews-seo-admin',
            NEONEWS_SEO_URL . 'admin/js/admin.js',
            array( 'jquery' ),
            NEONEWS_SEO_VERSION,
            true
        );
    }

    /**
     * @param array $input Input.
     * @return array
     */
    public function sanitize( $input ) {
        $prev = NeoNews_SEO::get_settings();
        $out  = $prev;

        $bools = array(
            'enabled', 'replace_theme_seo', 'defer_third_party',
            'enable_meta_description', 'enable_canonical', 'enable_open_graph', 'enable_twitter_cards',
            'enable_schema', 'enable_website_schema', 'enable_organization_schema',
            'enable_breadcrumbs', 'enable_breadcrumb_schema',
            'noindex_search', 'noindex_author', 'noindex_date', 'noindex_paged', 'noindex_tags', 'noindex_categories', 'noindex_account_pages',
            'sitemap_posts', 'sitemap_pages', 'sitemap_categories', 'sitemap_tags', 'sitemap_authors', 'sitemap_exclude_noindex',
            'article_section_category', 'premium_paywall_schema',
        );

        foreach ( $bools as $key ) {
            $out[ $key ] = ! empty( $input[ $key ] );
        }

        $out['home_title']              = sanitize_text_field( $input['home_title'] ?? '' );
        $out['home_description']        = sanitize_textarea_field( $input['home_description'] ?? '' );
        $out['title_separator']         = sanitize_text_field( $input['title_separator'] ?? '|' );
        $out['org_name']                = sanitize_text_field( $input['org_name'] ?? '' );
        $out['org_logo']                = absint( $input['org_logo'] ?? 0 );
        $out['default_og_image']        = absint( $input['default_og_image'] ?? 0 );
        $out['twitter_site']            = sanitize_text_field( $input['twitter_site'] ?? '' );
        $out['twitter_card']            = in_array( $input['twitter_card'] ?? '', array( 'summary', 'summary_large_image' ), true ) ? $input['twitter_card'] : 'summary_large_image';
        $out['breadcrumb_home_label']   = sanitize_text_field( $input['breadcrumb_home_label'] ?? '' );
        $out['robots_txt_extra']        = sanitize_textarea_field( $input['robots_txt_extra'] ?? '' );

        return $out;
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $s   = NeoNews_SEO::get_settings();
        $tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
        $tabs = array(
            'general'  => __( 'General', 'neonews-seo' ),
            'social'   => __( 'Social', 'neonews-seo' ),
            'schema'   => __( 'Schema', 'neonews-seo' ),
            'indexing' => __( 'Indexing', 'neonews-seo' ),
            'sitemap'  => __( 'Sitemap', 'neonews-seo' ),
        );
        if ( ! isset( $tabs[ $tab ] ) ) {
            $tab = 'general';
        }
        ?>
        <div class="wrap neonews-seo-admin">
            <h1><?php esc_html_e( 'NeoNews SEO', 'neonews-seo' ); ?></h1>
            <p><?php esc_html_e( 'Complete SEO for your news site — meta tags, social previews, structured data, sitemaps, and robots. Everything runs on your server.', 'neonews-seo' ); ?></p>

            <?php if ( NeoNews_SEO::third_party_active() ) : ?>
                <div class="notice notice-warning"><p><?php esc_html_e( 'Another SEO plugin is active. Enable output below only if you want NeoNews SEO to take over (or disable the other plugin).', 'neonews-seo' ); ?></p></div>
            <?php endif; ?>

            <nav class="nav-tab-wrapper">
                <?php foreach ( $tabs as $slug => $label ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-seo&tab=' . $slug ) ); ?>" class="nav-tab<?php echo $tab === $slug ? ' nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
                <?php endforeach; ?>
            </nav>

            <form method="post" action="options.php" class="neonews-seo-form">
                <?php settings_fields( 'neonews_seo_group' ); ?>

                <?php if ( 'general' === $tab ) : ?>
                    <table class="form-table">
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[enabled]" value="1" <?php checked( ! empty( $s['enabled'] ) ); ?> /> <?php esc_html_e( 'Enable NeoNews SEO', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[replace_theme_seo]" value="1" <?php checked( ! empty( $s['replace_theme_seo'] ) ); ?> /> <?php esc_html_e( 'Replace theme SEO output (recommended)', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[defer_third_party]" value="1" <?php checked( ! empty( $s['defer_third_party'] ) ); ?> /> <?php esc_html_e( 'Stay quiet when Yoast, Rank Math, or AIOSEO is active', 'neonews-seo' ); ?></label></td></tr>
                        <tr>
                            <th><?php esc_html_e( 'Title separator', 'neonews-seo' ); ?></th>
                            <td><input type="text" name="neonews_seo_settings[title_separator]" value="<?php echo esc_attr( $s['title_separator'] ); ?>" class="small-text" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Homepage title', 'neonews-seo' ); ?></th>
                            <td><input type="text" name="neonews_seo_settings[home_title]" value="<?php echo esc_attr( $s['home_title'] ); ?>" class="large-text" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Homepage meta description', 'neonews-seo' ); ?></th>
                            <td><textarea name="neonews_seo_settings[home_description]" rows="3" class="large-text"><?php echo esc_textarea( $s['home_description'] ); ?></textarea></td>
                        </tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[enable_meta_description]" value="1" <?php checked( ! empty( $s['enable_meta_description'] ) ); ?> /> <?php esc_html_e( 'Meta descriptions', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[enable_canonical]" value="1" <?php checked( ! empty( $s['enable_canonical'] ) ); ?> /> <?php esc_html_e( 'Canonical URLs', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[enable_breadcrumbs]" value="1" <?php checked( ! empty( $s['enable_breadcrumbs'] ) ); ?> /> <?php esc_html_e( 'Breadcrumb navigation on posts and archives', 'neonews-seo' ); ?></label></td></tr>
                        <tr>
                            <th><?php esc_html_e( 'Breadcrumb home label', 'neonews-seo' ); ?></th>
                            <td><input type="text" name="neonews_seo_settings[breadcrumb_home_label]" value="<?php echo esc_attr( $s['breadcrumb_home_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Home', 'neonews-seo' ); ?>" /></td>
                        </tr>
                    </table>
                <?php elseif ( 'social' === $tab ) : ?>
                    <table class="form-table">
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[enable_open_graph]" value="1" <?php checked( ! empty( $s['enable_open_graph'] ) ); ?> /> <?php esc_html_e( 'Open Graph tags (Facebook, LinkedIn, etc.)', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[enable_twitter_cards]" value="1" <?php checked( ! empty( $s['enable_twitter_cards'] ) ); ?> /> <?php esc_html_e( 'Twitter/X Cards', 'neonews-seo' ); ?></label></td></tr>
                        <tr>
                            <th><?php esc_html_e( 'Twitter @site', 'neonews-seo' ); ?></th>
                            <td><input type="text" name="neonews_seo_settings[twitter_site]" value="<?php echo esc_attr( $s['twitter_site'] ); ?>" class="regular-text" placeholder="@yournews" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Twitter card type', 'neonews-seo' ); ?></th>
                            <td>
                                <select name="neonews_seo_settings[twitter_card]">
                                    <option value="summary_large_image" <?php selected( $s['twitter_card'], 'summary_large_image' ); ?>><?php esc_html_e( 'Summary with large image', 'neonews-seo' ); ?></option>
                                    <option value="summary" <?php selected( $s['twitter_card'], 'summary' ); ?>><?php esc_html_e( 'Summary', 'neonews-seo' ); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Default social image', 'neonews-seo' ); ?></th>
                            <td>
                                <?php $this->render_media_field( 'default_og_image', $s['default_og_image'] ); ?>
                                <p class="description"><?php esc_html_e( 'Used when a post has no featured image. Recommended 1200×630 px.', 'neonews-seo' ); ?></p>
                            </td>
                        </tr>
                    </table>
                <?php elseif ( 'schema' === $tab ) : ?>
                    <table class="form-table">
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[enable_schema]" value="1" <?php checked( ! empty( $s['enable_schema'] ) ); ?> /> <?php esc_html_e( 'JSON-LD structured data', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[enable_website_schema]" value="1" <?php checked( ! empty( $s['enable_website_schema'] ) ); ?> /> <?php esc_html_e( 'WebSite + SearchAction on homepage', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[enable_organization_schema]" value="1" <?php checked( ! empty( $s['enable_organization_schema'] ) ); ?> /> <?php esc_html_e( 'Organization / publisher schema', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[enable_breadcrumb_schema]" value="1" <?php checked( ! empty( $s['enable_breadcrumb_schema'] ) ); ?> /> <?php esc_html_e( 'BreadcrumbList schema', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[article_section_category]" value="1" <?php checked( ! empty( $s['article_section_category'] ) ); ?> /> <?php esc_html_e( 'Use primary category as article section', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[premium_paywall_schema]" value="1" <?php checked( ! empty( $s['premium_paywall_schema'] ) ); ?> /> <?php esc_html_e( 'Paywall hints on premium posts (isAccessibleForFree)', 'neonews-seo' ); ?></label></td></tr>
                        <tr>
                            <th><?php esc_html_e( 'Organization name', 'neonews-seo' ); ?></th>
                            <td><input type="text" name="neonews_seo_settings[org_name]" value="<?php echo esc_attr( $s['org_name'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Organization logo', 'neonews-seo' ); ?></th>
                            <td><?php $this->render_media_field( 'org_logo', $s['org_logo'] ); ?></td>
                        </tr>
                    </table>
                <?php elseif ( 'indexing' === $tab ) : ?>
                    <table class="form-table">
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[noindex_search]" value="1" <?php checked( ! empty( $s['noindex_search'] ) ); ?> /> <?php esc_html_e( 'Noindex search results', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[noindex_author]" value="1" <?php checked( ! empty( $s['noindex_author'] ) ); ?> /> <?php esc_html_e( 'Noindex author archives', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[noindex_date]" value="1" <?php checked( ! empty( $s['noindex_date'] ) ); ?> /> <?php esc_html_e( 'Noindex date archives', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[noindex_paged]" value="1" <?php checked( ! empty( $s['noindex_paged'] ) ); ?> /> <?php esc_html_e( 'Noindex paginated pages (page/2, etc.)', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[noindex_tags]" value="1" <?php checked( ! empty( $s['noindex_tags'] ) ); ?> /> <?php esc_html_e( 'Noindex tag archives', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[noindex_categories]" value="1" <?php checked( ! empty( $s['noindex_categories'] ) ); ?> /> <?php esc_html_e( 'Noindex category archives', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[noindex_account_pages]" value="1" <?php checked( ! empty( $s['noindex_account_pages'] ) ); ?> /> <?php esc_html_e( 'Noindex login/account/profile pages', 'neonews-seo' ); ?></label></td></tr>
                        <tr>
                            <th><?php esc_html_e( 'Extra robots.txt rules', 'neonews-seo' ); ?></th>
                            <td><textarea name="neonews_seo_settings[robots_txt_extra]" rows="6" class="large-text code"><?php echo esc_textarea( $s['robots_txt_extra'] ); ?></textarea>
                            <p class="description"><?php esc_html_e( 'Appended to robots.txt. Sitemap line is added automatically.', 'neonews-seo' ); ?></p></td>
                        </tr>
                    </table>
                <?php elseif ( 'sitemap' === $tab ) : ?>
                    <table class="form-table">
                        <tr><td colspan="2"><p><?php printf( esc_html__( 'WordPress sitemap: %s', 'neonews-seo' ), '<a href="' . esc_url( home_url( '/wp-sitemap.xml' ) ) . '" target="_blank" rel="noopener">' . esc_html( home_url( '/wp-sitemap.xml' ) ) . '</a>' ); ?></p></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[sitemap_posts]" value="1" <?php checked( ! empty( $s['sitemap_posts'] ) ); ?> /> <?php esc_html_e( 'Include posts', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[sitemap_pages]" value="1" <?php checked( ! empty( $s['sitemap_pages'] ) ); ?> /> <?php esc_html_e( 'Include pages', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[sitemap_categories]" value="1" <?php checked( ! empty( $s['sitemap_categories'] ) ); ?> /> <?php esc_html_e( 'Include categories', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[sitemap_tags]" value="1" <?php checked( ! empty( $s['sitemap_tags'] ) ); ?> /> <?php esc_html_e( 'Include tags', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[sitemap_authors]" value="1" <?php checked( ! empty( $s['sitemap_authors'] ) ); ?> /> <?php esc_html_e( 'Include author archives', 'neonews-seo' ); ?></label></td></tr>
                        <tr><td colspan="2"><label><input type="checkbox" name="neonews_seo_settings[sitemap_exclude_noindex]" value="1" <?php checked( ! empty( $s['sitemap_exclude_noindex'] ) ); ?> /> <?php esc_html_e( 'Exclude posts marked noindex', 'neonews-seo' ); ?></label></td></tr>
                    </table>
                <?php endif; ?>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * @param string $field Field name.
     * @param int    $attachment_id Attachment ID.
     */
    private function render_media_field( $field, $attachment_id ) {
        $url = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'thumbnail' ) : '';
        ?>
        <div class="nn-seo-media-field" data-field="<?php echo esc_attr( $field ); ?>">
            <input type="hidden" name="neonews_seo_settings[<?php echo esc_attr( $field ); ?>]" id="nn-seo-<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( $attachment_id ); ?>" />
            <div class="nn-seo-media-preview"><?php if ( $url ) : ?><img src="<?php echo esc_url( $url ); ?>" alt="" /><?php endif; ?></div>
            <button type="button" class="button nn-seo-media-select" data-target="nn-seo-<?php echo esc_attr( $field ); ?>"><?php esc_html_e( 'Select image', 'neonews-seo' ); ?></button>
            <button type="button" class="button nn-seo-media-clear" data-target="nn-seo-<?php echo esc_attr( $field ); ?>"><?php esc_html_e( 'Remove', 'neonews-seo' ); ?></button>
        </div>
        <?php
    }
}
