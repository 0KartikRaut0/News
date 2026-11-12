<?php
/**
 * Read/write feature toggles across theme and NeoNews plugins.
 *
 * @package NeoNews_Feature_Control
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NeoNews_FC_Feature_Store {

	/**
	 * @param array $feature Registry row.
	 * @return bool
	 */
	public static function is_enabled( array $feature ) {
		$source = $feature['source'] ?? 'platform';

		switch ( $source ) {
			case 'platform':
				return self::platform_get( $feature['key'] );

			case 'core':
				return class_exists( 'NeoNews_Core' )
					? (bool) NeoNews_Core::get_settings( $feature['key'] )
					: false;

			case 'smart':
				return class_exists( 'NeoNews_Smart' )
					? (bool) NeoNews_Smart::get_settings( $feature['key'] )
					: false;

			case 'membership':
				return class_exists( 'NeoNews_Membership' )
					? (bool) NeoNews_Membership::get_settings( $feature['key'] )
					: false;

			case 'pwa':
				return function_exists( 'neonews_pwa' )
					? (bool) neonews_pwa()->get_settings( $feature['key'] )
					: false;

			case 'compound':
				return self::compound_get( $feature );

			default:
				return false;
		}
	}

	/**
	 * @param array $feature Registry row.
	 * @param bool  $enabled New state.
	 * @return true|WP_Error
	 */
	public static function set_enabled( array $feature, $enabled ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'forbidden', __( 'You cannot change site features.', 'neonews-feature-control' ) );
		}

		$enabled = (bool) $enabled;
		$source  = $feature['source'] ?? 'platform';

		switch ( $source ) {
			case 'platform':
				self::platform_set( $feature['key'], $enabled );
				self::after_platform_set( $feature['key'], $enabled );
				break;

			case 'core':
				if ( ! class_exists( 'NeoNews_Core' ) ) {
					return new WP_Error( 'missing_plugin', __( 'NeoNews Core is not active.', 'neonews-feature-control' ) );
				}
				self::option_merge_set( 'neonews_core_settings', $feature['key'], $enabled, array( 'NeoNews_Core', 'get_settings' ) );
				break;

			case 'smart':
				if ( ! class_exists( 'NeoNews_Smart' ) ) {
					return new WP_Error( 'missing_plugin', __( 'Smart Tools is not active.', 'neonews-feature-control' ) );
				}
				self::smart_set( $feature['key'], $enabled );
				break;

			case 'membership':
				if ( ! class_exists( 'NeoNews_Membership' ) ) {
					return new WP_Error( 'missing_plugin', __( 'Membership plugin is not active.', 'neonews-feature-control' ) );
				}
				self::option_merge_set(
					'neonews_membership_settings',
					$feature['key'],
					$enabled,
					array( 'NeoNews_Membership', 'get_settings' )
				);
				break;

			case 'pwa':
				if ( ! function_exists( 'neonews_pwa' ) ) {
					return new WP_Error( 'missing_plugin', __( 'PWA plugin is not active.', 'neonews-feature-control' ) );
				}
				$settings = neonews_pwa()->get_settings();
				$settings[ $feature['key'] ] = $enabled;
				update_option( 'neonews_pwa_settings', $settings );
				break;

			case 'compound':
				return self::compound_set( $feature, $enabled );

			default:
				return new WP_Error( 'invalid_source', __( 'Unknown feature source.', 'neonews-feature-control' ) );
		}

		/**
		 * Fires after a feature toggle is saved.
		 *
		 * @param string $id      Feature id.
		 * @param bool   $enabled New state.
		 * @param array  $feature Full registry row.
		 */
		do_action( 'neonews_feature_control_updated', $feature['id'], $enabled, $feature );

		return true;
	}

	/**
	 * @param string $key Setting key.
	 * @return bool
	 */
	private static function platform_get( $key ) {
		if ( function_exists( 'neonews_is_feature_enabled' ) ) {
			return neonews_is_feature_enabled( $key );
		}
		if ( function_exists( 'neonews_get_setting' ) ) {
			return (bool) neonews_get_setting( $key, false );
		}
		$settings = get_option( 'neonews_platform_settings', array() );
		return ! empty( $settings[ $key ] );
	}

	/**
	 * @param string $key     Setting key.
	 * @param bool   $enabled Value.
	 */
	private static function platform_set( $key, $enabled ) {
		$defaults = function_exists( 'neonews_get_default_settings' )
			? neonews_get_default_settings()
			: array();
		$settings = wp_parse_args( get_option( 'neonews_platform_settings', array() ), $defaults );
		$settings[ $key ] = $enabled;
		update_option( 'neonews_platform_settings', $settings );
	}

	/**
	 * Side effects for platform keys.
	 *
	 * @param string $key     Key.
	 * @param bool   $enabled State.
	 */
	private static function after_platform_set( $key, $enabled ) {
		if ( 'auth_allow_registration' === $key ) {
			update_option( 'users_can_register', $enabled ? 1 : 0 );
		}
	}

	/**
	 * @param array $feature Compound feature.
	 * @return bool
	 */
	private static function compound_get( array $feature ) {
		$type = $feature['compound'] ?? '';
		if ( 'breaking_ticker' === $type ) {
			return function_exists( 'neonews_show_breaking_ticker' ) && neonews_show_breaking_ticker();
		}
		if ( 'featured_carousel' === $type ) {
			return function_exists( 'neonews_show_featured_carousel' ) && neonews_show_featured_carousel();
		}
		return false;
	}

	/**
	 * @param array $feature Registry row.
	 * @param bool  $enabled State.
	 * @return true|WP_Error
	 */
	private static function compound_set( array $feature, $enabled ) {
		$type = $feature['compound'] ?? '';
		if ( 'breaking_ticker' === $type ) {
			self::platform_set( 'show_breaking_news', $enabled );
			set_theme_mod( 'neonews_show_breaking_news', $enabled );
			return true;
		}
		if ( 'featured_carousel' === $type ) {
			self::platform_set( 'show_home_carousel', $enabled );
			set_theme_mod( 'neonews_show_featured_carousel', $enabled );
			return true;
		}
		return new WP_Error( 'invalid_compound', __( 'Unknown compound feature.', 'neonews-feature-control' ) );
	}

	/**
	 * @param string   $option Option name.
	 * @param string   $key    Key.
	 * @param bool     $value  Value.
	 * @param callable $getter Defaults callback.
	 */
	private static function option_merge_set( $option, $key, $value, $getter ) {
		$defaults = is_callable( $getter ) ? call_user_func( $getter ) : array();
		$defaults = is_array( $defaults ) ? $defaults : array();
		$current  = get_option( $option, array() );
		$current  = is_array( $current ) ? $current : array();
		$settings = wp_parse_args( $current, $defaults );
		$settings[ $key ] = $value;
		update_option( $option, $settings );
	}

	/**
	 * @param string $key     Key.
	 * @param bool   $enabled State.
	 */
	private static function smart_set( $key, $enabled ) {
		$defaults = function_exists( 'neonews_smart_default_settings' )
			? neonews_smart_default_settings()
			: array();
		$settings = wp_parse_args( get_option( 'neonews_smart_settings', array() ), $defaults );
		$settings[ $key ] = $enabled;
		if ( function_exists( 'neonews_smart_save_settings' ) ) {
			neonews_smart_save_settings( $settings );
		} else {
			update_option( 'neonews_smart_settings', $settings, false );
		}
	}
}
