<?php
/**
 * AES-256-GCM encryption for sensitive data at rest.
 *
 * @package NeoNews_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Encryption helper.
 */
class NeoNews_Encryption {

    /**
     * Ensure encryption key exists in options.
     */
    public static function ensure_key() {
        if ( ! get_option( 'neonews_security_enc_key' ) ) {
            update_option( 'neonews_security_enc_key', wp_generate_password( 64, true, true ), false );
        }
    }

    /**
     * Derive binary key from WordPress salts + stored secret.
     *
     * @return string
     */
    private static function get_key() {
        self::ensure_key();
        $secret = (string) get_option( 'neonews_security_enc_key', '' );
        $material = $secret . wp_salt( 'auth' ) . wp_salt( 'secure_auth' );
        return hash( 'sha256', $material, true );
    }

    /**
     * Encrypt plaintext.
     *
     * @param string $plaintext Plain text.
     * @return string Base64 payload or empty on failure.
     */
    public static function encrypt( $plaintext ) {
        if ( '' === $plaintext || null === $plaintext ) {
            return '';
        }

        if ( ! function_exists( 'openssl_encrypt' ) ) {
            return base64_encode( 'plain:' . $plaintext ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
        }

        $iv = random_bytes( 12 );
        $tag = '';
        $cipher = openssl_encrypt( $plaintext, 'aes-256-gcm', self::get_key(), OPENSSL_RAW_DATA, $iv, $tag, '', 16 );

        if ( false === $cipher ) {
            return '';
        }

        return base64_encode( 'v1:' . $iv . $tag . $cipher ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
    }

    /**
     * Decrypt payload.
     *
     * @param string $payload Encrypted payload.
     * @return string
     */
    public static function decrypt( $payload ) {
        if ( '' === $payload || null === $payload ) {
            return '';
        }

        $raw = base64_decode( $payload, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
        if ( false === $raw ) {
            return $payload;
        }

        if ( 0 === strpos( $raw, 'plain:' ) ) {
            return substr( $raw, 6 );
        }

        if ( 0 !== strpos( $raw, 'v1:' ) || strlen( $raw ) < 30 ) {
            return $payload;
        }

        $raw    = substr( $raw, 3 );
        $iv     = substr( $raw, 0, 12 );
        $tag    = substr( $raw, 12, 16 );
        $cipher = substr( $raw, 28 );

        if ( ! function_exists( 'openssl_decrypt' ) ) {
            return '';
        }

        $plain = openssl_decrypt( $cipher, 'aes-256-gcm', self::get_key(), OPENSSL_RAW_DATA, $iv, $tag );
        return false === $plain ? '' : $plain;
    }

    /**
     * Is string already encrypted?
     *
     * @param string $value Value.
     * @return bool
     */
    public static function is_encrypted( $value ) {
        if ( ! is_string( $value ) || '' === $value ) {
            return false;
        }
        $raw = base64_decode( $value, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
        return false !== $raw && ( 0 === strpos( $raw, 'v1:' ) || 0 === strpos( $raw, 'plain:' ) );
    }
}
