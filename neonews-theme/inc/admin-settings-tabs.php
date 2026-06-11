<?php
/**
 * Admin settings tab panels (Homepage, Header, Footer, Sidebar, Features).
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Render homepage settings tab.
 *
 * @param array $settings Current settings.
 */
function neonews_admin_tab_homepage( $settings ) {
    ?>
    <h2><?php esc_html_e( 'Homepage sections', 'neonews' ); ?></h2>
    <p class="description"><?php esc_html_e( 'Toggle each block on the front page. Categories and pages are managed in Posts → Categories and Pages.', 'neonews' ); ?></p>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Hero headline', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_home_hero]" value="1" <?php checked( ! empty( $settings['show_home_hero'] ) ); ?> /> <?php esc_html_e( 'Show hero text block', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Hero title (line 1)', 'neonews' ); ?></th>
            <td><input type="text" name="neonews_platform_settings[home_hero_title]" value="<?php echo esc_attr( $settings['home_hero_title'] ); ?>" class="large-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Hero title accent', 'neonews' ); ?></th>
            <td><input type="text" name="neonews_platform_settings[home_hero_title_accent]" value="<?php echo esc_attr( $settings['home_hero_title_accent'] ); ?>" class="large-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Hero subtitle', 'neonews' ); ?></th>
            <td><textarea name="neonews_platform_settings[home_hero_subtitle]" rows="2" class="large-text"><?php echo esc_textarea( $settings['home_hero_subtitle'] ); ?></textarea></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Featured carousel', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_home_carousel]" value="1" <?php checked( ! empty( $settings['show_home_carousel'] ) ); ?> /> <?php esc_html_e( 'Show featured carousel (mark posts in editor)', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Carousel post count', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[home_carousel_count]" value="<?php echo esc_attr( $settings['home_carousel_count'] ); ?>" min="1" max="12" class="small-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Category blocks', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_home_categories]" value="1" <?php checked( ! empty( $settings['show_home_categories'] ) ); ?> /> <?php esc_html_e( 'Show category grid sections', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Category slugs', 'neonews' ); ?></th>
            <td>
                <input type="text" name="neonews_platform_settings[home_category_slugs]" value="<?php echo esc_attr( $settings['home_category_slugs'] ); ?>" class="large-text" placeholder="business,technology,sports,entertainment" />
                <p class="description"><?php esc_html_e( 'Comma-separated category slugs. Create categories in Posts → Categories.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Posts per category', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[home_category_posts]" value="<?php echo esc_attr( $settings['home_category_posts'] ); ?>" min="1" max="10" class="small-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Mid-page ad after category #', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[home_mid_ad_after_cat]" value="<?php echo esc_attr( $settings['home_mid_ad_after_cat'] ); ?>" min="0" max="10" class="small-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Watch / Videos', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_home_videos]" value="1" <?php checked( ! empty( $settings['show_home_videos'] ) ); ?> /> <?php esc_html_e( 'Show video section', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Video category slug', 'neonews' ); ?></th>
            <td><input type="text" name="neonews_platform_settings[home_video_category]" value="<?php echo esc_attr( $settings['home_video_category'] ); ?>" class="regular-text" placeholder="videos" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Video count', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[home_video_count]" value="<?php echo esc_attr( $settings['home_video_count'] ); ?>" min="1" max="12" class="small-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Watch section title', 'neonews' ); ?></th>
            <td><input type="text" name="neonews_platform_settings[home_video_title]" value="<?php echo esc_attr( $settings['home_video_title'] ); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Watch section subtitle', 'neonews' ); ?></th>
            <td><input type="text" name="neonews_platform_settings[home_video_subtitle]" value="<?php echo esc_attr( $settings['home_video_subtitle'] ); ?>" class="large-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Reader tipline', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_home_tipline]" value="1" <?php checked( ! empty( $settings['show_home_tipline'] ) ); ?> /> <?php esc_html_e( 'Show “Have a tip?” callout', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Latest posts', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_home_latest]" value="1" <?php checked( ! empty( $settings['show_home_latest'] ) ); ?> /> <?php esc_html_e( 'Show latest news grid', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Latest post count', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[home_latest_count]" value="<?php echo esc_attr( $settings['home_latest_count'] ); ?>" min="1" max="24" class="small-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Sidebar newsletter', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_home_newsletter]" value="1" <?php checked( ! empty( $settings['show_home_newsletter'] ) ); ?> /> <?php esc_html_e( 'Show newsletter box in homepage sidebar', 'neonews' ); ?></label></td>
        </tr>
    </table>
    <?php
}

/**
 * Render header settings tab.
 *
 * @param array $settings Current settings.
 */
function neonews_admin_tab_header( $settings ) {
    ?>
    <h2><?php esc_html_e( 'Header & navigation', 'neonews' ); ?></h2>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Date & time bar', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_header_datetime]" value="1" <?php checked( $settings['show_header_datetime'] ); ?> /> <?php esc_html_e( 'Show live date and time', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Breaking news ticker', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_breaking_news]" value="1" <?php checked( ! empty( $settings['show_breaking_news'] ) ); ?> /> <?php esc_html_e( 'Show breaking news bar (mark posts as breaking in editor)', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Breaking labels', 'neonews' ); ?></th>
            <td>
                <input type="text" name="neonews_platform_settings[breaking_label_live]" value="<?php echo esc_attr( $settings['breaking_label_live'] ); ?>" class="small-text" placeholder="LIVE" />
                <input type="text" name="neonews_platform_settings[breaking_label_tag]" value="<?php echo esc_attr( $settings['breaking_label_tag'] ); ?>" class="small-text" placeholder="Breaking" />
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Breaking item count', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[breaking_count]" value="<?php echo esc_attr( $settings['breaking_count'] ); ?>" min="1" max="10" class="small-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Logo mark (no custom logo)', 'neonews' ); ?></th>
            <td><input type="text" name="neonews_platform_settings[logo_mark]" value="<?php echo esc_attr( $settings['logo_mark'] ); ?>" class="small-text" maxlength="4" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Search bar', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_header_search]" value="1" <?php checked( ! empty( $settings['show_header_search'] ) ); ?> /> <?php esc_html_e( 'Show search in header', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Search placeholders', 'neonews' ); ?></th>
            <td>
                <input type="text" name="neonews_platform_settings[search_placeholder_desktop]" value="<?php echo esc_attr( $settings['search_placeholder_desktop'] ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Desktop placeholder', 'neonews' ); ?>" /><br />
                <input type="text" name="neonews_platform_settings[search_placeholder_mobile]" value="<?php echo esc_attr( $settings['search_placeholder_mobile'] ); ?>" class="large-text" style="margin-top:6px;" placeholder="<?php esc_attr_e( 'Mobile placeholder', 'neonews' ); ?>" />
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Sign-in / account menu', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_header_auth]" value="1" <?php checked( ! empty( $settings['show_header_auth'] ) ); ?> /> <?php esc_html_e( 'Show user menu and login', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Fallback menu categories', 'neonews' ); ?></th>
            <td>
                <input type="text" name="neonews_platform_settings[fallback_nav_categories]" value="<?php echo esc_attr( $settings['fallback_nav_categories'] ); ?>" class="large-text" />
                <p class="description"><?php esc_html_e( 'Used when no Primary Menu is assigned (Appearance → Menus).', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Fallback menu pages', 'neonews' ); ?></th>
            <td><input type="text" name="neonews_platform_settings[fallback_nav_pages]" value="<?php echo esc_attr( $settings['fallback_nav_pages'] ); ?>" class="large-text" placeholder="about,contact" /></td>
        </tr>
    </table>
    <?php
}

/**
 * Render footer settings tab.
 *
 * @param array $settings Current settings.
 */
function neonews_admin_tab_footer( $settings ) {
    ?>
    <h2><?php esc_html_e( 'Footer content', 'neonews' ); ?></h2>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Brand tagline', 'neonews' ); ?></th>
            <td><textarea name="neonews_platform_settings[footer_tagline]" rows="2" class="large-text"><?php echo esc_textarea( $settings['footer_tagline'] ); ?></textarea></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Copyright text', 'neonews' ); ?></th>
            <td>
                <textarea name="neonews_platform_settings[footer_copyright]" rows="2" class="large-text"><?php echo esc_textarea( $settings['footer_copyright'] ); ?></textarea>
                <p class="description"><?php esc_html_e( 'Leave empty for default “© YEAR Site Name”. Also editable in Appearance → Customize → Footer.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Social media & WhatsApp', 'neonews' ); ?></th>
            <td>
                <p><strong><?php esc_html_e( 'Profile URLs', 'neonews' ); ?></strong></p>
                <p>
                    <label>X / Twitter<br /><input type="url" name="neonews_platform_settings[social_x]" value="<?php echo esc_url( $settings['social_x'] ); ?>" class="large-text" placeholder="https://x.com/yournews" /></label>
                </p>
                <p>
                    <label>Facebook<br /><input type="url" name="neonews_platform_settings[social_facebook]" value="<?php echo esc_url( $settings['social_facebook'] ); ?>" class="large-text" placeholder="https://facebook.com/yournews" /></label>
                </p>
                <p>
                    <label>LinkedIn<br /><input type="url" name="neonews_platform_settings[social_linkedin]" value="<?php echo esc_url( $settings['social_linkedin'] ); ?>" class="large-text" /></label>
                </p>
                <p>
                    <label>Instagram<br /><input type="url" name="neonews_platform_settings[social_instagram]" value="<?php echo esc_url( $settings['social_instagram'] ); ?>" class="large-text" /></label>
                </p>
                <p style="margin-top:1rem;"><strong><?php esc_html_e( 'WhatsApp', 'neonews' ); ?></strong></p>
                <p>
                    <label><?php esc_html_e( 'WhatsApp number (country code, no +)', 'neonews' ); ?><br />
                    <input type="text" name="neonews_platform_settings[whatsapp_number]" value="<?php echo esc_attr( $settings['whatsapp_number'] ); ?>" class="regular-text" placeholder="15551234567" /></label>
                    <span class="description"><?php esc_html_e( 'Required for page chat buttons. Example: 919876543210 for India, 15551234567 for US.', 'neonews' ); ?></span>
                </p>
                <p>
                    <label><?php esc_html_e( 'Default WhatsApp message (Contact page)', 'neonews' ); ?><br />
                    <input type="text" name="neonews_platform_settings[whatsapp_contact_message]" value="<?php echo esc_attr( $settings['whatsapp_contact_message'] ); ?>" class="large-text" /></label>
                </p>
                <p>
                    <label><?php esc_html_e( 'Default WhatsApp message (Advertise page)', 'neonews' ); ?><br />
                    <input type="text" name="neonews_platform_settings[whatsapp_advertise_message]" value="<?php echo esc_attr( $settings['whatsapp_advertise_message'] ); ?>" class="large-text" /></label>
                </p>
                <p>
                    <label><?php esc_html_e( 'Default WhatsApp message (Careers page)', 'neonews' ); ?><br />
                    <input type="text" name="neonews_platform_settings[whatsapp_careers_message]" value="<?php echo esc_attr( $settings['whatsapp_careers_message'] ); ?>" class="large-text" /></label>
                </p>
                <p style="margin-top:1rem;"><strong><?php esc_html_e( 'Where to show', 'neonews' ); ?></strong></p>
                <label><input type="checkbox" name="neonews_platform_settings[show_header_social]" value="1" <?php checked( ! empty( $settings['show_header_social'] ) ); ?> /> <?php esc_html_e( 'Social icons in header date/time bar', 'neonews' ); ?></label><br />
                <label><input type="checkbox" name="neonews_platform_settings[show_footer_social]" value="1" <?php checked( ! empty( $settings['show_footer_social'] ) ); ?> /> <?php esc_html_e( 'Social icons in footer', 'neonews' ); ?></label><br />
                <label><input type="checkbox" name="neonews_platform_settings[show_contact_social]" value="1" <?php checked( ! empty( $settings['show_contact_social'] ) ); ?> /> <?php esc_html_e( 'Social icons on Contact page', 'neonews' ); ?></label><br />
                <label><input type="checkbox" name="neonews_platform_settings[show_post_whatsapp]" value="1" <?php checked( ! empty( $settings['show_post_whatsapp'] ) ); ?> /> <?php esc_html_e( 'WhatsApp share button on posts', 'neonews' ); ?></label><br />
                <label><input type="checkbox" name="neonews_platform_settings[show_contact_whatsapp]" value="1" <?php checked( ! empty( $settings['show_contact_whatsapp'] ) ); ?> /> <?php esc_html_e( 'WhatsApp chat button on Contact page', 'neonews' ); ?></label><br />
                <label><input type="checkbox" name="neonews_platform_settings[show_advertise_whatsapp]" value="1" <?php checked( ! empty( $settings['show_advertise_whatsapp'] ) ); ?> /> <?php esc_html_e( 'WhatsApp chat button on Advertise page', 'neonews' ); ?></label><br />
                <label><input type="checkbox" name="neonews_platform_settings[show_careers_whatsapp]" value="1" <?php checked( ! empty( $settings['show_careers_whatsapp'] ) ); ?> /> <?php esc_html_e( 'WhatsApp chat button on Careers page', 'neonews' ); ?></label>
                <p class="description"><?php esc_html_e( 'Post WhatsApp requires “Post shares” enabled under Features & Colors. Chat buttons require a WhatsApp number above.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Sections column', 'neonews' ); ?></th>
            <td>
                <input type="text" name="neonews_platform_settings[footer_section_categories]" value="<?php echo esc_attr( $settings['footer_section_categories'] ); ?>" class="large-text" />
                <p class="description"><?php esc_html_e( 'Category slugs for footer “Sections” links.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Company column', 'neonews' ); ?></th>
            <td>
                <input type="text" name="neonews_platform_settings[footer_company_pages]" value="<?php echo esc_attr( $settings['footer_company_pages'] ); ?>" class="large-text" placeholder="about,careers,advertise,contact" />
                <p class="description"><?php esc_html_e( 'Page slugs for footer “Company” links. Create pages in Pages → Add New.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Footer newsletter', 'neonews' ); ?></th>
            <td>
                <label><input type="checkbox" name="neonews_platform_settings[show_footer_newsletter]" value="1" <?php checked( ! empty( $settings['show_footer_newsletter'] ) ); ?> /> <?php esc_html_e( 'Show newsletter form in footer', 'neonews' ); ?></label>
                <p><input type="text" name="neonews_platform_settings[footer_newsletter_title]" value="<?php echo esc_attr( $settings['footer_newsletter_title'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Title', 'neonews' ); ?>" /></p>
                <p><textarea name="neonews_platform_settings[footer_newsletter_text]" rows="2" class="large-text"><?php echo esc_textarea( $settings['footer_newsletter_text'] ); ?></textarea></p>
                <p><input type="url" name="neonews_platform_settings[newsletter_form_action]" value="<?php echo esc_url( $settings['newsletter_form_action'] ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Form action URL (Mailchimp, etc.) — leave empty for placeholder', 'neonews' ); ?>" /></p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Render sidebar settings tab.
 *
 * @param array $settings Current settings.
 */
function neonews_admin_tab_sidebar( $settings ) {
    ?>
    <h2><?php esc_html_e( 'Sidebar widgets', 'neonews' ); ?></h2>
    <p class="description"><?php esc_html_e( 'Built-in sidebar blocks. Additional widgets: Appearance → Widgets → Sidebar.', 'neonews' ); ?></p>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Weather widget', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[sidebar_weather]" value="1" <?php checked( ! empty( $settings['sidebar_weather'] ) ); ?> /> <?php esc_html_e( 'Show weather in sidebar', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Fresh Reads', 'neonews' ); ?></th>
            <td>
                <label><input type="checkbox" name="neonews_platform_settings[sidebar_fresh_reads]" value="1" <?php checked( ! empty( $settings['sidebar_fresh_reads'] ) ); ?> /> <?php esc_html_e( 'Show trending posts widget', 'neonews' ); ?></label>
                <p><input type="text" name="neonews_platform_settings[sidebar_fresh_reads_title]" value="<?php echo esc_attr( $settings['sidebar_fresh_reads_title'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Widget title', 'neonews' ); ?>" /></p>
                <p><input type="number" name="neonews_platform_settings[sidebar_fresh_reads_count]" value="<?php echo esc_attr( $settings['sidebar_fresh_reads_count'] ); ?>" min="1" max="10" class="small-text" /> <?php esc_html_e( 'posts', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Topics', 'neonews' ); ?></th>
            <td>
                <label><input type="checkbox" name="neonews_platform_settings[sidebar_topics]" value="1" <?php checked( ! empty( $settings['sidebar_topics'] ) ); ?> /> <?php esc_html_e( 'Show category topic pills', 'neonews' ); ?></label>
                <p><input type="number" name="neonews_platform_settings[sidebar_topics_count]" value="<?php echo esc_attr( $settings['sidebar_topics_count'] ); ?>" min="1" max="20" class="small-text" /> <?php esc_html_e( 'categories', 'neonews' ); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Render features & content tab.
 *
 * @param array $settings Current settings.
 */
function neonews_admin_tab_features( $settings ) {
    ?>
    <h2><?php esc_html_e( 'Post & engagement features', 'neonews' ); ?></h2>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Engagement bar', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_engagement_bar]" value="1" <?php checked( ! empty( $settings['show_engagement_bar'] ) ); ?> /> <?php esc_html_e( 'Show like/share bar on single posts', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Post likes', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_post_likes]" value="1" <?php checked( ! empty( $settings['show_post_likes'] ) ); ?> /> <?php esc_html_e( 'Enable like button', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Post shares', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_post_shares]" value="1" <?php checked( ! empty( $settings['show_post_shares'] ) ); ?> /> <?php esc_html_e( 'Enable share buttons', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'View counts in meta', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_post_views]" value="1" <?php checked( ! empty( $settings['show_post_views'] ) ); ?> /> <?php esc_html_e( 'Show view counts on cards and meta lines', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Excerpt length', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[excerpt_length]" value="<?php echo esc_attr( $settings['excerpt_length'] ); ?>" min="5" max="80" class="small-text" /> <?php esc_html_e( 'words', 'neonews' ); ?></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Reading speed', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[reading_wpm]" value="<?php echo esc_attr( $settings['reading_wpm'] ); ?>" min="100" max="400" class="small-text" /> <?php esc_html_e( 'words per minute', 'neonews' ); ?></td>
        </tr>
    </table>

    <h2><?php esc_html_e( 'Category accent colors', 'neonews' ); ?></h2>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Custom colors', 'neonews' ); ?></th>
            <td>
                <textarea name="neonews_platform_settings[category_colors]" rows="6" class="large-text code" placeholder="business:#16a34a&#10;technology:#ca8a04"><?php echo esc_textarea( $settings['category_colors'] ); ?></textarea>
                <p class="description"><?php esc_html_e( 'One per line: slug:#hexcolor. Overrides default category bar colors on homepage.', 'neonews' ); ?></p>
            </td>
        </tr>
    </table>

    <h2><?php esc_html_e( 'Design & typography', 'neonews' ); ?></h2>
    <p><?php
    printf(
        /* translators: %s: Customizer URL */
        esc_html__( 'Fonts, colors, and layout density are controlled in %s for live preview.', 'neonews' ),
        '<a href="' . esc_url( admin_url( 'customize.php' ) ) . '">' . esc_html__( 'Appearance → Customize → NewsPulse Design', 'neonews' ) . '</a>'
    );
    ?></p>
    <?php
}
