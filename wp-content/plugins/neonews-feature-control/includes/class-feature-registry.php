<?php
/**
 * Master list of toggles for the NeoNews stack.
 *
 * @package NeoNews_Feature_Control
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NeoNews_FC_Feature_Registry {

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_groups() {
		return array(
			'header'      => array(
				'label' => __( 'Header & navigation', 'neonews-feature-control' ),
				'icon'  => '📌',
			),
			'footer'      => array(
				'label' => __( 'Footer & pages', 'neonews-feature-control' ),
				'icon'  => '⬇️',
			),
			'homepage'    => array(
				'label' => __( 'Homepage', 'neonews-feature-control' ),
				'icon'  => '🏠',
			),
			'sidebar'     => array(
				'label' => __( 'Sidebar widgets', 'neonews-feature-control' ),
				'icon'  => '📋',
			),
			'engagement'  => array(
				'label' => __( 'Reader engagement', 'neonews-feature-control' ),
				'icon'  => '❤️',
			),
			'accounts'    => array(
				'label' => __( 'Accounts & security', 'neonews-feature-control' ),
				'icon'  => '👤',
			),
			'whatsapp'    => array(
				'label' => __( 'WhatsApp', 'neonews-feature-control' ),
				'icon'  => '💬',
			),
			'privacy_ads' => array(
				'label' => __( 'Privacy & ads', 'neonews-feature-control' ),
				'icon'  => '🛡️',
			),
			'core'        => array(
				'label' => __( 'NeoNews Core', 'neonews-feature-control' ),
				'icon'  => '⚡',
			),
			'smart'       => array(
				'label' => __( 'Smart Tools', 'neonews-feature-control' ),
				'icon'  => '🧠',
			),
			'membership'  => array(
				'label' => __( 'Membership', 'neonews-feature-control' ),
				'icon'  => '⭐',
			),
			'pwa'         => array(
				'label' => __( 'PWA & install', 'neonews-feature-control' ),
				'icon'  => '📲',
			),
		);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_features() {
		$list = array(
			// Header.
			array(
				'id'          => 'header_datetime',
				'group'       => 'header',
				'icon'        => '🕐',
				'title'       => __( 'Date & time bar', 'neonews-feature-control' ),
				'description' => __( 'Live clock in the top header strip.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_header_datetime',
			),
			array(
				'id'          => 'header_search',
				'group'       => 'header',
				'icon'        => '🔍',
				'title'       => __( 'Search bar', 'neonews-feature-control' ),
				'description' => __( 'Desktop and mobile story search.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_header_search',
			),
			array(
				'id'          => 'header_auth',
				'group'       => 'header',
				'icon'        => '👤',
				'title'       => __( 'Accounts & login', 'neonews-feature-control' ),
				'description' => __( 'Sign in, sign up, and user menu in the header.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_header_auth',
			),
			array(
				'id'          => 'theme_toggle',
				'group'       => 'header',
				'icon'        => '🌓',
				'title'       => __( 'Dark / light mode toggle', 'neonews-feature-control' ),
				'description' => __( 'Sun/moon button so visitors can switch theme.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_theme_toggle',
			),
			array(
				'id'          => 'breaking_ticker',
				'group'       => 'header',
				'icon'        => '📢',
				'title'       => __( 'Breaking news ticker', 'neonews-feature-control' ),
				'description' => __( 'Scrolling “breaking” headlines under the header.', 'neonews-feature-control' ),
				'source'      => 'compound',
				'compound'    => 'breaking_ticker',
			),
			array(
				'id'          => 'weather_global',
				'group'       => 'header',
				'icon'        => '🌤️',
				'title'       => __( 'Weather', 'neonews-feature-control' ),
				'description' => __( 'Weather chip in the header (sidebar widget needs this too).', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'weather_enabled',
			),
			array(
				'id'          => 'header_social',
				'group'       => 'header',
				'icon'        => '🔗',
				'title'       => __( 'Header social icons', 'neonews-feature-control' ),
				'description' => __( 'Social profile links in the header area.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_header_social',
			),

			// Footer.
			array(
				'id'          => 'footer_sections',
				'group'       => 'footer',
				'icon'        => '📂',
				'title'       => __( 'Footer category links', 'neonews-feature-control' ),
				'description' => __( '“Sections” column with category links.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_footer_sections',
			),
			array(
				'id'          => 'footer_company',
				'group'       => 'footer',
				'icon'        => '📄',
				'title'       => __( 'Company pages', 'neonews-feature-control' ),
				'description' => __( 'About, Contact, Careers, Advertise links in the footer.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_footer_company_pages',
			),
			array(
				'id'          => 'footer_legal',
				'group'       => 'footer',
				'icon'        => '⚖️',
				'title'       => __( 'Legal page links', 'neonews-feature-control' ),
				'description' => __( 'Privacy, Terms, Cookies, and similar footer links.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_footer_legal',
			),
			array(
				'id'          => 'footer_social',
				'group'       => 'footer',
				'icon'        => '🔗',
				'title'       => __( 'Footer social icons', 'neonews-feature-control' ),
				'description' => __( 'Social icons in the footer brand column.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_footer_social',
			),
			array(
				'id'          => 'footer_newsletter',
				'group'       => 'footer',
				'icon'        => '✉️',
				'title'       => __( 'Footer newsletter', 'neonews-feature-control' ),
				'description' => __( 'Email signup form in the footer.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_footer_newsletter',
			),

			// Homepage.
			array(
				'id'          => 'home_hero',
				'group'       => 'homepage',
				'icon'        => '✨',
				'title'       => __( 'Homepage hero text', 'neonews-feature-control' ),
				'description' => __( 'Title and intro block on the front page.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_home_hero',
			),
			array(
				'id'          => 'home_carousel',
				'group'       => 'homepage',
				'icon'        => '🎠',
				'title'       => __( 'Featured carousel', 'neonews-feature-control' ),
				'description' => __( 'Large rotating featured stories.', 'neonews-feature-control' ),
				'source'      => 'compound',
				'compound'    => 'featured_carousel',
			),
			array(
				'id'          => 'home_categories',
				'group'       => 'homepage',
				'icon'        => '🗂️',
				'title'       => __( 'Category blocks', 'neonews-feature-control' ),
				'description' => __( 'Per-category story sections on the home page.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_home_categories',
			),
			array(
				'id'          => 'home_videos',
				'group'       => 'homepage',
				'icon'        => '▶️',
				'title'       => __( 'Video section', 'neonews-feature-control' ),
				'description' => __( 'Homepage video grid or list.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_home_videos',
			),
			array(
				'id'          => 'home_tipline',
				'group'       => 'homepage',
				'icon'        => '📝',
				'title'       => __( 'Reader tipline', 'neonews-feature-control' ),
				'description' => __( '“Pitch a story” call-to-action on the homepage.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_home_tipline',
			),
			array(
				'id'          => 'home_latest',
				'group'       => 'homepage',
				'icon'        => '📰',
				'title'       => __( 'Latest stories grid', 'neonews-feature-control' ),
				'description' => __( 'Recent posts block on the homepage.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_home_latest',
			),
			array(
				'id'          => 'home_newsletter',
				'group'       => 'homepage',
				'icon'        => '✉️',
				'title'       => __( 'Homepage newsletter', 'neonews-feature-control' ),
				'description' => __( 'Subscribe form in the homepage layout.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_home_newsletter',
			),
			array(
				'id'          => 'home_banner',
				'group'       => 'homepage',
				'icon'        => '🖼️',
				'title'       => __( 'Promo banner', 'neonews-feature-control' ),
				'description' => __( 'Full-width image or video banner under the hero.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'home_banner_enabled',
			),

			// Sidebar.
			array(
				'id'          => 'sidebar_weather',
				'group'       => 'sidebar',
				'icon'        => '🌡️',
				'title'       => __( 'Sidebar weather widget', 'neonews-feature-control' ),
				'description' => __( 'Weather card in the sidebar (requires Weather on).', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'sidebar_weather',
			),
			array(
				'id'          => 'sidebar_fresh_reads',
				'group'       => 'sidebar',
				'icon'        => '🆕',
				'title'       => __( 'Fresh Reads', 'neonews-feature-control' ),
				'description' => __( 'Recently published list in the sidebar.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'sidebar_fresh_reads',
			),
			array(
				'id'          => 'sidebar_topics',
				'group'       => 'sidebar',
				'icon'        => '🏷️',
				'title'       => __( 'Topic pills', 'neonews-feature-control' ),
				'description' => __( 'Popular category chips in the sidebar.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'sidebar_topics',
			),

			// Engagement.
			array(
				'id'          => 'engagement_bar',
				'group'       => 'engagement',
				'icon'        => '📊',
				'title'       => __( 'Engagement bar', 'neonews-feature-control' ),
				'description' => __( 'Like, share, and actions bar on articles.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_engagement_bar',
			),
			array(
				'id'          => 'post_likes',
				'group'       => 'engagement',
				'icon'        => '❤️',
				'title'       => __( 'Likes', 'neonews-feature-control' ),
				'description' => __( 'Like button on posts.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_post_likes',
			),
			array(
				'id'          => 'post_favorites',
				'group'       => 'engagement',
				'icon'        => '🔖',
				'title'       => __( 'Favorites', 'neonews-feature-control' ),
				'description' => __( 'Save-to-favorites control.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_post_favorites',
			),
			array(
				'id'          => 'post_shares',
				'group'       => 'engagement',
				'icon'        => '↗️',
				'title'       => __( 'Share buttons', 'neonews-feature-control' ),
				'description' => __( 'Social sharing controls on articles.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_post_shares',
			),
			array(
				'id'          => 'post_views',
				'group'       => 'engagement',
				'icon'        => '👁',
				'title'       => __( 'View counts', 'neonews-feature-control' ),
				'description' => __( 'Display view numbers on posts.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_post_views',
			),
			array(
				'id'          => 'article_nav',
				'group'       => 'engagement',
				'icon'        => '⬅️',
				'title'       => __( 'Article back button', 'neonews-feature-control' ),
				'description' => __( 'Floating back control at the bottom of articles.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_article_nav',
			),

			// Accounts.
			array(
				'id'          => 'auth_registration',
				'group'       => 'accounts',
				'icon'        => '📝',
				'title'       => __( 'New account registration', 'neonews-feature-control' ),
				'description' => __( 'Allow visitors to create accounts (Sign up).', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'auth_allow_registration',
			),
			array(
				'id'          => 'captcha',
				'group'       => 'accounts',
				'icon'        => '🤖',
				'title'       => __( 'Login CAPTCHA', 'neonews-feature-control' ),
				'description' => __( 'Bot protection on login and registration.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'captcha_enabled',
			),

			// WhatsApp.
			array(
				'id'          => 'whatsapp_float',
				'group'       => 'whatsapp',
				'icon'        => '💬',
				'title'       => __( 'Floating WhatsApp button', 'neonews-feature-control' ),
				'description' => __( 'Sticky chat button on screen edge.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'whatsapp_float_enabled',
			),
			array(
				'id'          => 'post_whatsapp',
				'group'       => 'whatsapp',
				'icon'        => '📱',
				'title'       => __( 'WhatsApp on posts', 'neonews-feature-control' ),
				'description' => __( 'Share-via-WhatsApp on articles (needs Share buttons).', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'show_post_whatsapp',
			),

			// Privacy & ads.
			array(
				'id'          => 'cookie_banner',
				'group'       => 'privacy_ads',
				'icon'        => '🍪',
				'title'       => __( 'Cookie consent banner', 'neonews-feature-control' ),
				'description' => __( 'Bottom bar for cookie preferences.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'cookie_banner_enabled',
			),
			array(
				'id'          => 'ads_master',
				'group'       => 'privacy_ads',
				'icon'        => '📢',
				'title'       => __( 'Advertisements', 'neonews-feature-control' ),
				'description' => __( 'Master switch for all ad zones.', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'ads_master',
			),
			array(
				'id'          => 'analytics',
				'group'       => 'privacy_ads',
				'icon'        => '📈',
				'title'       => __( 'Google Analytics', 'neonews-feature-control' ),
				'description' => __( 'Load GA after cookie consent (when configured).', 'neonews-feature-control' ),
				'source'      => 'platform',
				'key'         => 'analytics_enabled',
			),

			// Core.
			array(
				'id'          => 'view_counter',
				'group'       => 'core',
				'icon'        => '👁',
				'title'       => __( 'View counter (tracking)', 'neonews-feature-control' ),
				'description' => __( 'Count post views in the database.', 'neonews-feature-control' ),
				'source'      => 'core',
				'key'         => 'enable_view_counter',
				'requires'    => 'NeoNews_Core',
			),
			array(
				'id'          => 'activity_tracking',
				'group'       => 'core',
				'icon'        => '📋',
				'title'       => __( 'Activity tracking', 'neonews-feature-control' ),
				'description' => __( 'Log reads, likes, logins for analytics.', 'neonews-feature-control' ),
				'source'      => 'core',
				'key'         => 'enable_activity_tracking',
				'requires'    => 'NeoNews_Core',
			),
			array(
				'id'          => 'news_submission',
				'group'       => 'core',
				'icon'        => '📨',
				'title'       => __( 'News submission', 'neonews-feature-control' ),
				'description' => __( 'Allow users to submit stories from the front end.', 'neonews-feature-control' ),
				'source'      => 'core',
				'key'         => 'enable_submission',
				'requires'    => 'NeoNews_Core',
			),

			// Smart.
			array(
				'id'          => 'smart_related',
				'group'       => 'smart',
				'icon'        => '🔗',
				'title'       => __( 'Smart related posts', 'neonews-feature-control' ),
				'description' => __( '“Trending” and related story blocks.', 'neonews-feature-control' ),
				'source'      => 'smart',
				'key'         => 'enable_smart_related',
				'requires'    => 'NeoNews_Smart',
			),
			array(
				'id'          => 'personalized_home',
				'group'       => 'smart',
				'icon'        => '🎯',
				'title'       => __( 'Recommended for you', 'neonews-feature-control' ),
				'description' => __( 'Personalized picks on the homepage.', 'neonews-feature-control' ),
				'source'      => 'smart',
				'key'         => 'enable_personalized_home',
				'requires'    => 'NeoNews_Smart',
			),
			array(
				'id'          => 'story_glance',
				'group'       => 'smart',
				'icon'        => '⚡',
				'title'       => __( 'Story at a glance', 'neonews-feature-control' ),
				'description' => __( 'Quick summary box on long articles.', 'neonews-feature-control' ),
				'source'      => 'smart',
				'key'         => 'enable_story_glance',
				'requires'    => 'NeoNews_Smart',
			),

			// Membership.
			array(
				'id'          => 'paywall',
				'group'       => 'membership',
				'icon'        => '🔒',
				'title'       => __( 'Premium paywall', 'neonews-feature-control' ),
				'description' => __( 'Limit free article reads for non-members.', 'neonews-feature-control' ),
				'source'      => 'membership',
				'key'         => 'enable_paywall',
				'requires'    => 'NeoNews_Membership',
			),
			array(
				'id'          => 'premium_home',
				'group'       => 'membership',
				'icon'        => '⭐',
				'title'       => __( 'Premium picks on home', 'neonews-feature-control' ),
				'description' => __( 'Exclusive stories section on the homepage.', 'neonews-feature-control' ),
				'source'      => 'membership',
				'key'         => 'show_home_premium_section',
				'requires'    => 'NeoNews_Membership',
			),
			array(
				'id'          => 'exclusive_nav',
				'group'       => 'membership',
				'icon'        => '🧭',
				'title'       => __( 'Exclusive nav link', 'neonews-feature-control' ),
				'description' => __( '“Exclusive” item in the main menu.', 'neonews-feature-control' ),
				'source'      => 'membership',
				'key'         => 'show_exclusive_nav_link',
				'requires'    => 'NeoNews_Membership',
			),
			array(
				'id'          => 'upgrade_popup',
				'group'       => 'membership',
				'icon'        => '💎',
				'title'       => __( 'Upgrade popup', 'neonews-feature-control' ),
				'description' => __( 'Timed premium upsell modal for visitors.', 'neonews-feature-control' ),
				'source'      => 'membership',
				'key'         => 'enable_upgrade_popup',
				'requires'    => 'NeoNews_Membership',
			),

			// PWA.
			array(
				'id'          => 'pwa',
				'group'       => 'pwa',
				'icon'        => '📲',
				'title'       => __( 'PWA support', 'neonews-feature-control' ),
				'description' => __( 'Manifest, service worker, and installability.', 'neonews-feature-control' ),
				'source'      => 'pwa',
				'key'         => 'enable_pwa',
				'requires'    => 'neonews_pwa',
			),
			array(
				'id'          => 'install_banner',
				'group'       => 'pwa',
				'icon'        => '⬇️',
				'title'       => __( 'Install app banner', 'neonews-feature-control' ),
				'description' => __( '“Add to home screen” prompt for visitors.', 'neonews-feature-control' ),
				'source'      => 'pwa',
				'key'         => 'enable_install_banner',
				'requires'    => 'neonews_pwa',
			),
		);

		/**
		 * Add or modify Feature Control toggles.
		 *
		 * @param array $list Feature definitions.
		 */
		return apply_filters( 'neonews_feature_control_registry', $list );
	}

	/**
	 * @param array $feature Feature row.
	 * @return bool
	 */
	public static function is_available( array $feature ) {
		if ( empty( $feature['requires'] ) ) {
			return true;
		}
		$req = $feature['requires'];
		if ( 'neonews_pwa' === $req ) {
			return function_exists( 'neonews_pwa' );
		}
		return class_exists( $req );
	}

	/**
	 * @param string $id Feature id.
	 * @return array|null
	 */
	public static function get_by_id( $id ) {
		foreach ( self::get_features() as $feature ) {
			if ( $feature['id'] === $id ) {
				return $feature;
			}
		}
		return null;
	}
}
