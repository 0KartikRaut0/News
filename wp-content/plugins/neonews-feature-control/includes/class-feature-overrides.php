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
