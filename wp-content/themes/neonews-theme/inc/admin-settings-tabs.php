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
            <td>
                <label><input type="checkbox" name="neonews_platform_settings[show_home_tipline]" value="1" <?php checked( ! empty( $settings['show_home_tipline'] ) ); ?> /> <?php esc_html_e( 'Show “Pitch a story” section', 'neonews' ); ?></label>
                <p class="description">
                    <?php esc_html_e( 'Uses the same access rules as Submit Story — who can pitch (Premium only, or basic members too) is set in', 'neonews' ); ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-core-settings#nn-submission-settings' ) ); ?>"><?php esc_html_e( 'Core Features → Story Submissions', 'neonews' ); ?></a>.
                    <?php esc_html_e( 'Pitches are reviewed under', 'neonews' ); ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-submissions' ) ); ?>"><?php esc_html_e( 'Story Submissions', 'neonews' ); ?></a>.
                </p>
            </td>
        </tr>
        <?php
        $tip_fields = array(
            'tipline_badge'       => __( 'Pitch — badge', 'neonews' ),
            'tipline_title'       => __( 'Pitch — heading', 'neonews' ),
            'tipline_text'        => __( 'Pitch — intro text', 'neonews' ),
            'tipline_form_title'  => __( 'Pitch — box title', 'neonews' ),
            'tipline_form_desc'   => __( 'Pitch — box description', 'neonews' ),
            'tipline_cta_open'    => __( 'Pitch — button (can submit)', 'neonews' ),
            'tipline_cta_locked'  => __( 'Pitch — button (locked)', 'neonews' ),
            'tipline_locked_text' => __( 'Pitch — locked message', 'neonews' ),
        );
        foreach ( $tip_fields as $tip_key => $tip_label ) :
            ?>
            <tr>
                <th><?php echo esc_html( $tip_label ); ?></th>
                <td><input type="text" name="neonews_platform_settings[<?php echo esc_attr( $tip_key ); ?>]" value="<?php echo esc_attr( $settings[ $tip_key ] ); ?>" class="large-text" /></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <th><?php esc_html_e( 'Pitch — bullet points', 'neonews' ); ?></th>
            <td>
                <textarea name="neonews_platform_settings[tipline_points]" rows="3" class="large-text"><?php echo esc_textarea( $settings['tipline_points'] ); ?></textarea>
                <p class="description"><?php esc_html_e( 'One per line.', 'neonews' ); ?></p>
            </td>
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
                <p class="description">
                    <?php esc_html_e( 'WhatsApp number, page chat buttons and the floating icon are configured in the', 'neonews' ); ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-settings&tab=whatsapp' ) ); ?>"><?php esc_html_e( 'WhatsApp tab', 'neonews' ); ?></a>.
                </p>
                <p style="margin-top:1rem;"><strong><?php esc_html_e( 'Where to show', 'neonews' ); ?></strong></p>
                <label><input type="checkbox" name="neonews_platform_settings[show_header_social]" value="1" <?php checked( ! empty( $settings['show_header_social'] ) ); ?> /> <?php esc_html_e( 'Social icons in header date/time bar', 'neonews' ); ?></label><br />
                <label><input type="checkbox" name="neonews_platform_settings[show_footer_social]" value="1" <?php checked( ! empty( $settings['show_footer_social'] ) ); ?> /> <?php esc_html_e( 'Social icons in footer', 'neonews' ); ?></label><br />
                <label><input type="checkbox" name="neonews_platform_settings[show_contact_social]" value="1" <?php checked( ! empty( $settings['show_contact_social'] ) ); ?> /> <?php esc_html_e( 'Social icons on Contact page', 'neonews' ); ?></label>
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
 * Render WhatsApp settings tab.
 *
 * @param array $settings Current settings.
 */
function neonews_admin_tab_whatsapp( $settings ) {
    $name  = 'neonews_platform_settings';
    $pages = neonews_get_whatsapp_pages();
    ?>
    <h2><?php esc_html_e( 'WhatsApp contact', 'neonews' ); ?></h2>
    <?php if ( ! neonews_has_whatsapp_number() ) : ?>
        <div class="notice notice-warning inline"><p><?php esc_html_e( 'No WhatsApp number saved yet — WhatsApp buttons and the floating icon stay hidden until you add one below.', 'neonews' ); ?></p></div>
    <?php endif; ?>
    <table class="form-table">
        <tr>
            <th><label for="nn-wa-number"><?php esc_html_e( 'WhatsApp number', 'neonews' ); ?></label></th>
            <td>
                <input type="text" id="nn-wa-number" name="<?php echo esc_attr( $name ); ?>[whatsapp_number]" value="<?php echo esc_attr( $settings['whatsapp_number'] ); ?>" class="regular-text" placeholder="919876543210" />
                <p class="description"><?php esc_html_e( 'Country code + number, digits only (no +, spaces or dashes). Example: 919876543210 for India. Used on every page unless a page has its own number below.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="nn-wa-hours"><?php esc_html_e( 'Availability note', 'neonews' ); ?></label></th>
            <td>
                <input type="text" id="nn-wa-hours" name="<?php echo esc_attr( $name ); ?>[whatsapp_hours]" value="<?php echo esc_attr( $settings['whatsapp_hours'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Mon–Sat, 10am–7pm IST', 'neonews' ); ?>" />
                <p class="description"><?php esc_html_e( 'Optional. Shown under each WhatsApp button.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Floating WhatsApp icon', 'neonews' ); ?></th>
            <td>
                <label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[whatsapp_float_enabled]" value="1" <?php checked( ! empty( $settings['whatsapp_float_enabled'] ) ); ?> /> <?php esc_html_e( 'Show a floating WhatsApp icon on pages where WhatsApp is enabled below', 'neonews' ); ?></label><br />
                <label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[whatsapp_float_mobile]" value="1" <?php checked( ! empty( $settings['whatsapp_float_mobile'] ) ); ?> /> <?php esc_html_e( 'Also show on mobile', 'neonews' ); ?></label>
                <p>
                    <label><?php esc_html_e( 'Position', 'neonews' ); ?>
                        <select name="<?php echo esc_attr( $name ); ?>[whatsapp_float_position]">
                            <option value="right" <?php selected( $settings['whatsapp_float_position'], 'right' ); ?>><?php esc_html_e( 'Bottom right', 'neonews' ); ?></option>
                            <option value="left" <?php selected( $settings['whatsapp_float_position'], 'left' ); ?>><?php esc_html_e( 'Bottom left', 'neonews' ); ?></option>
                        </select>
                    </label>
                </p>
                <p>
                    <label><?php esc_html_e( 'Label next to icon (desktop)', 'neonews' ); ?><br />
                    <input type="text" name="<?php echo esc_attr( $name ); ?>[whatsapp_float_label]" value="<?php echo esc_attr( $settings['whatsapp_float_label'] ); ?>" class="regular-text" /></label>
                </p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Post sharing', 'neonews' ); ?></th>
            <td>
                <label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[show_post_whatsapp]" value="1" <?php checked( ! empty( $settings['show_post_whatsapp'] ) ); ?> /> <?php esc_html_e( 'WhatsApp share button on posts', 'neonews' ); ?></label>
                <p class="description"><?php esc_html_e( 'Requires “Post shares” enabled under Features & Colors. Does not need a number.', 'neonews' ); ?></p>
            </td>
        </tr>
    </table>

    <?php foreach ( $pages as $key => $label ) : ?>
        <h2><?php echo esc_html( sprintf( __( '%s page', 'neonews' ), $label ) ); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Enable', 'neonews' ); ?></th>
                <td><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[show_<?php echo esc_attr( $key ); ?>_whatsapp]" value="1" <?php checked( ! empty( $settings[ 'show_' . $key . '_whatsapp' ] ) ); ?> /> <?php echo esc_html( sprintf( __( 'Show WhatsApp contact on the %s page', 'neonews' ), $label ) ); ?></label></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Number for this page', 'neonews' ); ?></th>
                <td>
                    <input type="text" name="<?php echo esc_attr( $name ); ?>[whatsapp_<?php echo esc_attr( $key ); ?>_number]" value="<?php echo esc_attr( $settings[ 'whatsapp_' . $key . '_number' ] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Leave blank to use the main number', 'neonews' ); ?>" />
                    <p class="description"><?php esc_html_e( 'Optional — e.g. route Advertise to your sales team and Careers to HR.', 'neonews' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Box title', 'neonews' ); ?></th>
                <td><input type="text" name="<?php echo esc_attr( $name ); ?>[whatsapp_<?php echo esc_attr( $key ); ?>_title]" value="<?php echo esc_attr( $settings[ 'whatsapp_' . $key . '_title' ] ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Box description', 'neonews' ); ?></th>
                <td><input type="text" name="<?php echo esc_attr( $name ); ?>[whatsapp_<?php echo esc_attr( $key ); ?>_desc]" value="<?php echo esc_attr( $settings[ 'whatsapp_' . $key . '_desc' ] ); ?>" class="large-text" /></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Button text', 'neonews' ); ?></th>
                <td><input type="text" name="<?php echo esc_attr( $name ); ?>[whatsapp_<?php echo esc_attr( $key ); ?>_button]" value="<?php echo esc_attr( $settings[ 'whatsapp_' . $key . '_button' ] ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Prefilled message', 'neonews' ); ?></th>
                <td>
                    <input type="text" name="<?php echo esc_attr( $name ); ?>[whatsapp_<?php echo esc_attr( $key ); ?>_message]" value="<?php echo esc_attr( $settings[ 'whatsapp_' . $key . '_message' ] ); ?>" class="large-text" />
                    <p class="description"><?php esc_html_e( 'Text already typed in the visitor’s WhatsApp when they open the chat.', 'neonews' ); ?></p>
                </td>
            </tr>
        </table>
    <?php endforeach; ?>
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
