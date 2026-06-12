<?php
/**
 * One-click automated security fixes.
 *
 * @package NeoNews_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Auto-fix engine.
 */
class NeoNews_Auto_Fix {

    /**
     * Available fix definitions.
     *
     * @return array
     */
    public static function get_fix_registry() {
        return array(
            'block_xmlrpc'            => array( 'setting' => 'block_xmlrpc', 'label' => __( 'Block XML-RPC', 'neonews-security' ) ),
            'hide_wp_version'         => array( 'setting' => 'hide_wp_version', 'label' => __( 'Hide WordPress version', 'neonews-security' ) ),
            'disable_file_edit'       => array( 'setting' => 'disable_file_edit', 'label' => __( 'Disable file editor', 'neonews-security' ) ),
            'enable_security_headers' => array( 'setting' => 'security_headers', 'label' => __( 'Enable security headers', 'neonews-security' ) ),
            'block_user_enumeration'  => array( 'setting' => 'block_user_enum', 'label' => __( 'Block user enumeration', 'neonews-security' ) ),
            'block_uploads_php'       => array( 'setting' => 'uploads_php_block', 'label' => __( 'Block PHP in uploads', 'neonews-security' ) ),
            'enable_login_limit'      => array( 'setting' => 'login_limit_enabled', 'label' => __( 'Enable login rate limiting', 'neonews-security' ) ),
            'encrypt_api_keys'        => array( 'setting' => 'encrypt_api_keys', 'label' => __( 'Encrypt API keys at rest', 'neonews-security' ) ),
            'force_secure_cookies'    => array( 'setting' => 'force_secure_auth_cookie', 'label' => __( 'Force secure auth cookies', 'neonews-security' ) ),
            'enable_all_hardening'    => array( 'multi' => true, 'label' => __( 'Enable all hardening toggles', 'neonews-security' ) ),
        );
    }

    /**
     * Apply a fix by ID.
     *
     * @param string $fix_id Fix ID.
     * @return array Result.
     */
    public static function apply( $fix_id ) {
        $registry = self::get_fix_registry();

        if ( ! isset( $registry[ $fix_id ] ) ) {
            return array(
                'success' => false,
                'message' => __( 'Unknown fix ID.', 'neonews-security' ),
            );
        }

        $settings = NeoNews_Security::get_settings();
        $def      = $registry[ $fix_id ];
        $messages = array();

        if ( ! empty( $def['multi'] ) ) {
            $settings['block_xmlrpc']        = true;
            $settings['hide_wp_version']     = true;
            $settings['disable_file_edit']   = true;
            $settings['security_headers']    = true;
            $settings['block_user_enum']     = true;
            $settings['uploads_php_block']   = true;
            $settings['login_limit_enabled'] = true;
            $settings['encrypt_api_keys']    = true;
            $messages[] = __( 'All hardening toggles enabled.', 'neonews-security' );
        } else {
            $key = $def['setting'];
            $settings[ $key ] = true;
            $messages[] = sprintf(
                /* translators: %s: setting label */
                __( 'Enabled: %s', 'neonews-security' ),
                $def['label']
            );
        }

        NeoNews_Security::update_settings( $settings );

        $extra = self::run_side_effects( $fix_id );
        if ( $extra ) {
            $messages = array_merge( $messages, $extra );
        }

        NeoNews_Security_Log::log(
            'fix',
            'info',
            sprintf(
                /* translators: %s: fix id */
                __( 'Auto-fix applied: %s', 'neonews-security' ),
                $fix_id
            ),
            array( 'fix_id' => $fix_id )
        );

        NeoNews_Security_Scanner::run_scan();

        return array(
            'success' => true,
            'message' => implode( ' ', $messages ),
            'fix_id'  => $fix_id,
        );
    }

    /**
     * Apply multiple fixes.
     *
     * @param array $fix_ids Fix IDs.
     * @return array
     */
    public static function apply_multiple( $fix_ids ) {
        $results = array();
        foreach ( $fix_ids as $id ) {
            $results[] = self::apply( sanitize_key( $id ) );
        }
        return $results;
    }

    /**
     * File/system side effects.
     *
     * @param string $fix_id Fix ID.
     * @return array Messages.
     */
    private static function run_side_effects( $fix_id ) {
        $messages = array();

        if ( in_array( $fix_id, array( 'block_uploads_php', 'enable_all_hardening' ), true ) ) {
            if ( self::write_uploads_htaccess() ) {
                $messages[] = __( 'Uploads .htaccess protection written.', 'neonews-security' );
            }
        }

        if ( in_array( $fix_id, array( 'disable_file_edit', 'enable_all_hardening' ), true ) ) {
            if ( self::write_mu_plugin_hardening() ) {
                $messages[] = __( 'Must-use plugin hardening loader installed.', 'neonews-security' );
            }
        }

        if ( in_array( $fix_id, array( 'encrypt_api_keys', 'enable_all_hardening' ), true ) ) {
            NeoNews_Encrypted_Options::migrate_existing_keys();
            $messages[] = __( 'Sensitive API keys encrypted in database.', 'neonews-security' );
        }

        return $messages;
    }

    /**
     * Write uploads .htaccess.
     *
     * @return bool
     */
    public static function write_uploads_htaccess() {
        $upload_dir = wp_upload_dir();
        if ( empty( $upload_dir['basedir'] ) || ! wp_is_writable( $upload_dir['basedir'] ) ) {
            return false;
        }

        $file = trailingslashit( $upload_dir['basedir'] ) . '.htaccess';
        $rules = "# NeoNews Security — block PHP execution\n<Files *.php>\n    deny from all\n</Files>\n<Files *.phtml>\n    deny from all\n</Files>\n";

        if ( file_exists( $file ) && false !== strpos( file_get_contents( $file ), 'NeoNews Security' ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
            return true;
        }

        return (bool) file_put_contents( $file, $rules, FILE_APPEND | LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
    }

    /**
     * Install MU-plugin for wp-config-level constants.
     *
     * @return bool
     */
    public static function write_mu_plugin_hardening() {
        $dir = WPMU_PLUGIN_DIR;
        if ( ! file_exists( $dir ) ) {
            wp_mkdir_p( $dir );
        }

        if ( ! is_writable( $dir ) ) {
            return false;
        }

        $file = trailingslashit( $dir ) . 'neonews-security-hardening.php';
        $code = <<<'PHP'
<?php
/**
 * NeoNews Security hardening loader (auto-generated).
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
    define( 'DISALLOW_FILE_EDIT', true );
}
if ( ! defined( 'DISALLOW_FILE_MODS' ) ) {
    // Uncomment to block plugin/theme installs from admin:
    // define( 'DISALLOW_FILE_MODS', true );
}

PHP;

        if ( file_exists( $file ) ) {
            return true;
        }

        return (bool) file_put_contents( $file, $code, LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
    }
}
