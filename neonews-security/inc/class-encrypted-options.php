<?php
/**
 * Encrypt sensitive NeoNews Core / platform API keys at rest.
 *
 * @package NeoNews_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Encrypted options bridge.
 */
class NeoNews_Encrypted_Options {

    /**
     * Option keys to encrypt (option_name => key path).
     *
     * @return array
     */
    public static function get_sensitive_keys() {
        return array(
            'neonews_core_settings' => array( 'onesignal_rest_key' ),
            'neonews_platform_settings' => array( 'google_analytics_id', 'cookie_custom_script', 'captcha_secret_key' ),
            'neonews_membership_settings' => array( 'stripe_test_secret_key', 'stripe_live_secret_key' ),
        );
    }

    /**
     * Init filters.
     */
    public static function init() {
        add_filter( 'pre_update_option_neonews_core_settings', array( __CLASS__, 'encrypt_on_save_core' ), 10, 2 );
        add_filter( 'option_neonews_core_settings', array( __CLASS__, 'decrypt_on_read_core' ) );
        add_filter( 'pre_update_option_neonews_platform_settings', array( __CLASS__, 'encrypt_on_save_platform' ), 10, 2 );
        add_filter( 'option_neonews_platform_settings', array( __CLASS__, 'decrypt_on_read_platform' ) );
        add_filter( 'pre_update_option_neonews_membership_settings', array( __CLASS__, 'encrypt_on_save_membership' ), 10, 2 );
        add_filter( 'option_neonews_membership_settings', array( __CLASS__, 'decrypt_on_read_membership' ) );
    }

    /**
     * Should encrypt?
     *
     * @return bool
     */
    private static function encryption_enabled() {
        $settings = NeoNews_Security::get_settings();
        return ! empty( $settings['encrypt_api_keys'] );
    }

    /**
     * Encrypt core settings on save.
     *
     * @param mixed $value New value.
     * @param mixed $old   Old value.
     * @return mixed
     */
    public static function encrypt_on_save_core( $value, $old ) {
        if ( ! self::encryption_enabled() || ! is_array( $value ) ) {
            return $value;
        }
        if ( ! empty( $value['onesignal_rest_key'] ) && ! NeoNews_Encryption::is_encrypted( $value['onesignal_rest_key'] ) ) {
            $value['onesignal_rest_key'] = NeoNews_Encryption::encrypt( $value['onesignal_rest_key'] );
        }
        return $value;
    }

    /**
     * Decrypt core settings on read.
     *
     * @param mixed $value Value.
     * @return mixed
     */
    public static function decrypt_on_read_core( $value ) {
        if ( ! is_array( $value ) || empty( $value['onesignal_rest_key'] ) ) {
            return $value;
        }
        if ( NeoNews_Encryption::is_encrypted( $value['onesignal_rest_key'] ) ) {
            $value['onesignal_rest_key'] = NeoNews_Encryption::decrypt( $value['onesignal_rest_key'] );
        }
        return $value;
    }

    /**
     * Encrypt platform settings on save.
     *
     * @param mixed $value New value.
     * @param mixed $old   Old value.
     * @return mixed
     */
    public static function encrypt_on_save_platform( $value, $old ) {
        if ( ! self::encryption_enabled() || ! is_array( $value ) ) {
            return $value;
        }
        if ( ! empty( $value['cookie_custom_script'] ) && ! NeoNews_Encryption::is_encrypted( $value['cookie_custom_script'] ) ) {
            $value['cookie_custom_script'] = NeoNews_Encryption::encrypt( $value['cookie_custom_script'] );
        }
        return $value;
    }

    /**
     * Decrypt platform settings on read.
     *
     * @param mixed $value Value.
     * @return mixed
     */
    public static function decrypt_on_read_platform( $value ) {
        if ( ! is_array( $value ) ) {
            return $value;
        }
        if ( ! empty( $value['cookie_custom_script'] ) && NeoNews_Encryption::is_encrypted( $value['cookie_custom_script'] ) ) {
            $value['cookie_custom_script'] = NeoNews_Encryption::decrypt( $value['cookie_custom_script'] );
        }
        return $value;
    }

    /**
     * Encrypt membership settings on save.
     *
     * @param mixed $value New value.
     * @param mixed $old   Old value.
     * @return mixed
     */
    public static function encrypt_on_save_membership( $value, $old ) {
        if ( ! self::encryption_enabled() || ! is_array( $value ) ) {
            return $value;
        }

        foreach ( array( 'stripe_test_secret_key', 'stripe_live_secret_key' ) as $secret_key ) {
            if ( ! empty( $value[ $secret_key ] ) && ! NeoNews_Encryption::is_encrypted( $value[ $secret_key ] ) ) {
                $value[ $secret_key ] = NeoNews_Encryption::encrypt( $value[ $secret_key ] );
            }
        }

        return $value;
    }

    /**
     * Decrypt membership settings on read.
     *
     * @param mixed $value Value.
     * @return mixed
     */
    public static function decrypt_on_read_membership( $value ) {
        if ( ! is_array( $value ) ) {
            return $value;
        }

        foreach ( array( 'stripe_test_secret_key', 'stripe_live_secret_key' ) as $secret_key ) {
            if ( ! empty( $value[ $secret_key ] ) && NeoNews_Encryption::is_encrypted( $value[ $secret_key ] ) ) {
                $value[ $secret_key ] = NeoNews_Encryption::decrypt( $value[ $secret_key ] );
            }
        }

        return $value;
    }

    /**
     * Migrate existing plaintext keys to encrypted storage.
     */
    public static function migrate_existing_keys() {
        if ( ! self::encryption_enabled() ) {
            return;
        }

        $core = get_option( 'neonews_core_settings', array() );
        if ( is_array( $core ) && ! empty( $core['onesignal_rest_key'] ) && ! NeoNews_Encryption::is_encrypted( $core['onesignal_rest_key'] ) ) {
            $core['onesignal_rest_key'] = NeoNews_Encryption::encrypt( $core['onesignal_rest_key'] );
            update_option( 'neonews_core_settings', $core, false );
        }

        $platform = get_option( 'neonews_platform_settings', array() );
        if ( is_array( $platform ) && ! empty( $platform['cookie_custom_script'] ) && ! NeoNews_Encryption::is_encrypted( $platform['cookie_custom_script'] ) ) {
            $platform['cookie_custom_script'] = NeoNews_Encryption::encrypt( $platform['cookie_custom_script'] );
            update_option( 'neonews_platform_settings', $platform, false );
        }

        $membership = get_option( 'neonews_membership_settings', array() );
        if ( is_array( $membership ) ) {
            $changed = false;
            foreach ( array( 'stripe_test_secret_key', 'stripe_live_secret_key' ) as $secret_key ) {
                if ( ! empty( $membership[ $secret_key ] ) && ! NeoNews_Encryption::is_encrypted( $membership[ $secret_key ] ) ) {
                    $membership[ $secret_key ] = NeoNews_Encryption::encrypt( $membership[ $secret_key ] );
                    $changed = true;
                }
            }
            if ( $changed ) {
                update_option( 'neonews_membership_settings', $membership, false );
            }
        }
    }
}
