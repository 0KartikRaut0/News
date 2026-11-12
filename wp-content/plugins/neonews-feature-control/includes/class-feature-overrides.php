<?php
/**
 * Persistent feature toggles — small option map applied on every settings read.
 *
 * @package NeoNews_Feature_Control
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NeoNews_FC_Feature_Overrides {

	const OPTION = 'neonews_fc_overrides';

	/**
	 * Option names merged by this class (filter callback name).
	 *
	 * @var array<string, string>
	 */
	private static $merged_options = array(
		'neonews_platform_settings'   => 'merge_platform',
		'neonews_core_settings'       => 'merge_core',
		'neonews_smart_settings'      => 'merge_smart',
		'neonews_membership_settings' => 'merge_membership',
		'neonews_pwa_settings'        => 'merge_pwa',
	);

	/**
	 * @return array<string, array<string, int>>
	 */
	public static function all() {
		$raw = get_option( self::OPTION, array() );
		return is_array( $raw ) ? $raw : array();
	}

	/**
	 * @param string $bucket platform|core|smart|membership|pwa|compound|theme_mod.
	 * @param string $key    Key within bucket.
	 * @return bool|null     Null when not set in overrides.
	 */
	public static function get( $bucket, $key ) {
		$all = self::all();
		if ( ! isset( $all[ $bucket ] ) || ! is_array( $all[ $bucket ] ) ) {
			return null;
		}
		if ( ! array_key_exists( $key, $all[ $bucket ] ) ) {
			return null;
		}
		return NeoNews_FC_Feature_Store::public_to_bool( $all[ $bucket ][ $key ] );
	}

	/**
	 * @param string $bucket Bucket.
	 * @param string $key    Key.
	 * @param bool   $enabled Value.
	 */
	public static function set( $bucket, $key, $enabled ) {
		$all = self::all();
		if ( ! isset( $all[ $bucket ] ) || ! is_array( $all[ $bucket ] ) ) {
			$all[ $bucket ] = array();
		}
		$all[ $bucket ][ $key ] = $enabled ? 1 : 0;
		update_option( self::OPTION, $all, false );
		NeoNews_FC_Feature_Store::bust_option_cache( self::OPTION );
	}

	/**
	 * Apply overrides whenever WordPress loads stored settings.
	 */
	public static function register_filters() {
		add_filter( 'option_neonews_platform_settings', array( __CLASS__, 'merge_platform' ), 50 );
		add_filter( 'option_neonews_core_settings', array( __CLASS__, 'merge_core' ), 50 );
		add_filter( 'option_neonews_smart_settings', array( __CLASS__, 'merge_smart' ), 50 );
		add_filter( 'option_neonews_membership_settings', array( __CLASS__, 'merge_membership' ), 50 );
		add_filter( 'option_neonews_pwa_settings', array( __CLASS__, 'merge_pwa' ), 50 );
		add_filter( 'theme_mod_neonews_show_breaking_news', array( __CLASS__, 'theme_mod_breaking' ), 50 );
		add_filter( 'theme_mod_neonews_show_featured_carousel', array( __CLASS__, 'theme_mod_carousel' ), 50 );
	}

	/**
	 * @param mixed $value Option value.
	 * @return mixed
	 */
	public static function merge_platform( $value ) {
		return self::merge_bucket( $value, 'platform' );
	}

	/**
	 * @param mixed $value Option value.
	 * @return mixed
	 */
	public static function merge_core( $value ) {
		return self::merge_bucket( $value, 'core' );
	}

	/**
	 * @param mixed $value Option value.
	 * @return mixed
	 */
	public static function merge_smart( $value ) {
		return self::merge_bucket( $value, 'smart' );
	}

	/**
	 * @param mixed $value Option value.
	 * @return mixed
	 */
	public static function merge_membership( $value ) {
		return self::merge_bucket( $value, 'membership' );
	}

	/**
	 * @param mixed $value Option value.
	 * @return mixed
	 */
	public static function merge_pwa( $value ) {
		return self::merge_bucket( $value, 'pwa' );
	}

	/**
	 * Read option array from the database without Feature Control overlays (for saving).
	 *
	 * @param string       $option   Option name.
	 * @param array<mixed> $defaults Default values to merge.
	 * @return array<mixed>
	 */
	public static function get_stored_option_array( $option, $defaults = array() ) {
		$method = self::$merged_options[ $option ] ?? '';

		if ( $method && has_filter( "option_{$option}", array( __CLASS__, $method ) ) ) {
			remove_filter( "option_{$option}", array( __CLASS__, $method ), 50 );
		}

		$raw = get_option( $option, array() );
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}

		if ( $method ) {
			add_filter( "option_{$option}", array( __CLASS__, $method ), 50 );
		}

		if ( ! empty( $defaults ) && is_array( $defaults ) ) {
			return wp_parse_args( $raw, $defaults );
		}

		return $raw;
	}

	/**
	 * Membership uses tabbed forms — only sync Feature Control keys from the tab that was saved.
	 *
	 * @param string       $tab       Tab slug.
	 * @param array<mixed> $sanitized Sanitized settings being written.
	 */
	public static function sync_membership_fc_keys_for_tab( $tab, array $sanitized ) {
		if ( ! class_exists( 'NeoNews_FC_Feature_Store' ) ) {
			return;
		}

		$keys_by_tab = array(
			'general' => array( 'enable_paywall', 'show_exclusive_nav_link' ),
			'popup'   => array( 'enable_upgrade_popup', 'show_home_premium_section' ),
		);

		if ( ! isset( $keys_by_tab[ $tab ] ) ) {
			return;
		}

		foreach ( $keys_by_tab[ $tab ] as $key ) {
			if ( ! array_key_exists( $key, $sanitized ) ) {
				continue;
			}
			self::set( 'membership', $key, NeoNews_FC_Feature_Store::public_to_bool( $sanitized[ $key ] ) );
		}

		NeoNews_FC_Feature_Store::bust_option_cache( 'neonews_membership_settings' );
	}

	/**
	 * @param mixed  $value Raw option.
	 * @param string $bucket Bucket id.
	 * @return array
	 */
	private static function merge_bucket( $value, $bucket ) {
		if ( ! is_array( $value ) ) {
			$value = array();
		}
		$all = self::all();
		if ( empty( $all[ $bucket ] ) || ! is_array( $all[ $bucket ] ) ) {
			return $value;
		}
		foreach ( $all[ $bucket ] as $key => $flag ) {
			$value[ $key ] = (int) $flag;
		}
		return $value;
	}

	/**
	 * @param mixed $value Theme mod.
	 * @return mixed
	 */
	public static function theme_mod_breaking( $value ) {
		$ov = self::get( 'theme_mod', 'neonews_show_breaking_news' );
		return null !== $ov ? ( $ov ? 1 : 0 ) : $value;
	}

	/**
	 * @param mixed $value Theme mod.
	 * @return mixed
	 */
	public static function theme_mod_carousel( $value ) {
		$ov = self::get( 'theme_mod', 'neonews_show_featured_carousel' );
		return null !== $ov ? ( $ov ? 1 : 0 ) : $value;
	}

	/**
	 * Record override from a registry feature row.
	 *
	 * @param array $feature Feature.
	 * @param bool  $enabled State.
	 */
	/**
	 * When settings are saved from a plugin/theme screen (not Feature Control), keep overrides in sync.
	 */
	public static function register_native_save_hooks() {
		$map = array(
			'neonews_platform_settings'   => 'platform',
			'neonews_core_settings'       => 'core',
			'neonews_smart_settings'      => 'smart',
			'neonews_membership_settings' => 'membership',
			'neonews_pwa_settings'        => 'pwa',
		);

		foreach ( $map as $option => $bucket ) {
			add_action(
				"update_option_{$option}",
				static function ( $old_value, $new_value ) use ( $bucket ) {
					unset( $old_value );
					// Membership FC sync runs from tab sanitize (partial forms); skip full-bucket sync here.
					if ( 'membership' === $bucket ) {
						return;
					}
					self::sync_bucket_from_saved_settings( $bucket, $new_value );
				},
				10,
				2
			);
		}
	}

	/**
	 * Mirror saved option values into the override map for registry keys in this bucket.
	 *
	 * @param string $bucket   platform|core|smart|membership|pwa.
	 * @param mixed  $settings New option value.
	 */
	public static function sync_bucket_from_saved_settings( $bucket, $settings ) {
		if ( ! is_array( $settings ) || ! class_exists( 'NeoNews_FC_Feature_Registry' ) || ! class_exists( 'NeoNews_FC_Feature_Store' ) ) {
			return;
		}

		foreach ( NeoNews_FC_Feature_Registry::get_features() as $feature ) {
			if ( ( $feature['source'] ?? '' ) !== $bucket ) {
				continue;
			}
			$key = $feature['key'] ?? '';
			if ( ! $key || ! array_key_exists( $key, $settings ) ) {
				continue;
			}
			self::set( $bucket, $key, NeoNews_FC_Feature_Store::public_to_bool( $settings[ $key ] ) );
		}

		if ( 'platform' === $bucket ) {
			if ( array_key_exists( 'show_breaking_news', $settings ) ) {
				self::set(
					'theme_mod',
					'neonews_show_breaking_news',
					NeoNews_FC_Feature_Store::public_to_bool( $settings['show_breaking_news'] )
				);
			}
			if ( array_key_exists( 'show_home_carousel', $settings ) ) {
				self::set(
					'theme_mod',
					'neonews_show_featured_carousel',
					NeoNews_FC_Feature_Store::public_to_bool( $settings['show_home_carousel'] )
				);
			}
		}

		$option_by_bucket = array(
			'platform'   => 'neonews_platform_settings',
			'core'       => 'neonews_core_settings',
			'smart'      => 'neonews_smart_settings',
			'membership' => 'neonews_membership_settings',
			'pwa'        => 'neonews_pwa_settings',
		);
		if ( isset( $option_by_bucket[ $bucket ] ) ) {
			NeoNews_FC_Feature_Store::bust_option_cache( $option_by_bucket[ $bucket ] );
		}
		if ( 'smart' === $bucket && class_exists( 'NeoNews_Smart' ) && method_exists( 'NeoNews_Smart', 'flush_settings_cache' ) ) {
			NeoNews_Smart::flush_settings_cache();
		}
	}

	public static function sync_feature( array $feature, $enabled ) {
		$source = $feature['source'] ?? 'platform';
		$id     = $feature['id'] ?? '';

		if ( 'compound' === $source ) {
			self::set( 'compound', $id, $enabled );
			$type = $feature['compound'] ?? '';
			if ( 'breaking_ticker' === $type ) {
				self::set( 'platform', 'show_breaking_news', $enabled );
				self::set( 'theme_mod', 'neonews_show_breaking_news', $enabled );
			}
			if ( 'featured_carousel' === $type ) {
				self::set( 'platform', 'show_home_carousel', $enabled );
				self::set( 'theme_mod', 'neonews_show_featured_carousel', $enabled );
			}
			return;
		}

		if ( isset( $feature['key'] ) && $source ) {
			self::set( $source, $feature['key'], $enabled );
		}
	}
}

add_action( 'plugins_loaded', array( 'NeoNews_FC_Feature_Overrides', 'register_filters' ), 5 );
add_action( 'plugins_loaded', array( 'NeoNews_FC_Feature_Overrides', 'register_native_save_hooks' ), 6 );
