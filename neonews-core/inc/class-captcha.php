<?php
/**
 * CAPTCHA verification for login and registration.
 *
 * @package NeoNews_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CAPTCHA handler.
 */
class NeoNews_Captcha {

    /**
     * @var NeoNews_Captcha|null
     */
    private static $instance = null;

    /**
     * Supported providers.
     */
    const PROVIDERS = array( 'turnstile', 'recaptcha_v2', 'recaptcha_v3', 'hcaptcha' );

    /**
     * Get instance.
     *
     * @return NeoNews_Captcha
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'login_enqueue_scripts', array( $this, 'enqueue_wp_login_assets' ) );
        add_action( 'login_form', array( $this, 'render_wp_login_widget' ) );
        add_action( 'register_form', array( $this, 'render_wp_register_widget' ) );
        add_filter( 'authenticate', array( $this, 'verify_wp_login' ), 30, 3 );
        add_filter( 'registration_errors', array( $this, 'verify_wp_register' ), 10, 3 );
    }

    /**
     * Read captcha settings from platform options.
     *
     * @return array
     */
    public static function get_config() {
        $defaults = array(
            'captcha_enabled'       => false,
            'captcha_provider'      => 'turnstile',
            'captcha_site_key'      => '',
            'captcha_secret_key'    => '',
            'captcha_on_login'      => true,
            'captcha_on_register'   => true,
            'captcha_v3_threshold'  => 0.5,
        );

        $settings = get_option( 'neonews_platform_settings', array() );
        $config   = wp_parse_args( $settings, $defaults );

        $config['captcha_provider'] = in_array( $config['captcha_provider'], self::PROVIDERS, true )
            ? $config['captcha_provider']
            : 'turnstile';

        $config['captcha_v3_threshold'] = max( 0.1, min( 1.0, (float) $config['captcha_v3_threshold'] ) );

        return $config;
    }

    /**
     * Is captcha globally configured and enabled?
     *
     * @return bool
     */
    public static function is_configured() {
        $config = self::get_config();
        return ! empty( $config['captcha_enabled'] )
            && ! empty( $config['captcha_site_key'] )
            && ! empty( $config['captcha_secret_key'] );
    }

    /**
     * Is captcha required for a context?
     *
     * @param string $context login|register.
     * @return bool
     */
    public static function is_required_for( $context ) {
        if ( ! self::is_configured() ) {
            return false;
        }

        $config = self::get_config();

        if ( 'login' === $context ) {
            return ! empty( $config['captcha_on_login'] );
        }

        if ( 'register' === $context ) {
            return ! empty( $config['captcha_on_register'] );
        }

        return false;
    }

    /**
     * Client-side config for JS.
     *
     * @return array
     */
    public static function get_js_config() {
        $config = self::get_config();

        return array(
            'enabled'          => self::is_configured(),
            'provider'         => $config['captcha_provider'],
            'siteKey'          => $config['captcha_site_key'],
            'onLogin'          => ! empty( $config['captcha_on_login'] ),
            'onRegister'       => ! empty( $config['captcha_on_register'] ),
            'isInvisible'      => 'recaptcha_v3' === $config['captcha_provider'],
            'usersCanRegister' => (bool) get_option( 'users_can_register' ),
        );
    }

    /**
     * Render widget container for theme modal / forms.
     *
     * @param string $context login|register.
     */
    public static function render_widget( $context ) {
        if ( ! self::is_required_for( $context ) ) {
            return;
        }

        $config = self::get_config();

        if ( 'recaptcha_v3' === $config['captcha_provider'] ) {
            echo '<input type="hidden" name="captcha_token" class="nn-captcha-token" data-context="' . esc_attr( $context ) . '" value="" />';
            return;
        }

        echo '<div class="nn-captcha-wrap" data-context="' . esc_attr( $context ) . '">';
        echo '<div class="nn-captcha-widget" id="nn-captcha-' . esc_attr( $context ) . '"></div>';
        echo '<input type="hidden" name="captcha_token" class="nn-captcha-token" data-context="' . esc_attr( $context ) . '" value="" />';
        echo '</div>';
    }

    /**
     * Enqueue provider script on front end.
     */
    public static function enqueue_scripts() {
        if ( ! self::is_configured() ) {
            return;
        }

        $config = self::get_config();

        switch ( $config['captcha_provider'] ) {
            case 'turnstile':
                wp_enqueue_script( 'cloudflare-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
                break;
            case 'recaptcha_v2':
                wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
                break;
            case 'recaptcha_v3':
                wp_enqueue_script(
                    'google-recaptcha-v3',
                    'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $config['captcha_site_key'] ),
                    array(),
                    null,
                    true
                ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
                break;
            case 'hcaptcha':
                wp_enqueue_script( 'hcaptcha', 'https://js.hcaptcha.com/1/api.js', array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
                break;
        }
    }

    /**
     * Enqueue wp-login assets.
     */
    public function enqueue_wp_login_assets() {
        if ( ! self::is_configured() ) {
            return;
        }

        self::enqueue_scripts();
        wp_enqueue_script(
            'neonews-captcha',
            NEONEWS_CORE_URL . 'public/js/captcha.js',
            array(),
            NEONEWS_CORE_VERSION,
            true
        );
        wp_add_inline_style( 'login', '.nn-wp-captcha{margin:16px 0;}.nn-wp-captcha .nn-captcha-wrap{margin:0;}' );
    }

    /**
     * Render widget on wp-login.php login form.
     */
    public function render_wp_login_widget() {
        if ( ! self::is_required_for( 'login' ) ) {
            return;
        }

        echo '<div class="nn-wp-captcha">';
        self::render_widget( 'login' );
        echo '</div>';
        self::print_wp_login_init_script();
    }

    /**
     * Render widget on wp-login.php register form.
     */
    public function render_wp_register_widget() {
        if ( ! self::is_required_for( 'register' ) ) {
            return;
        }

        echo '<div class="nn-wp-captcha">';
        self::render_widget( 'register' );
        echo '</div>';
        self::print_wp_login_init_script();
    }

    /**
     * Inline script to mount widgets on wp-login.php.
     */
    private static function print_wp_login_init_script() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;

        $config = wp_json_encode( self::get_js_config() );
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.nnInitCaptcha && <?php echo $config; // phpcs:ignore WordPress.Security.EscapeOutput ?>.enabled) {
                window.nnInitCaptcha(<?php echo $config; // phpcs:ignore WordPress.Security.EscapeOutput ?>);
            }
        });
        </script>
        <?php
    }

    /**
     * Verify captcha from current request.
     *
     * @param string $context login|register.
     * @return true|WP_Error
     */
    public static function verify_request( $context ) {
        if ( ! self::is_required_for( $context ) ) {
            return true;
        }

        $token = self::get_token_from_request();
        if ( ! $token ) {
            return new WP_Error(
                'neonews_captcha_missing',
                __( 'Please complete the CAPTCHA verification.', 'neonews-core' )
            );
        }

        return self::verify_token( $token, $context );
    }

    /**
     * Extract token from POST.
     *
     * @return string
     */
    private static function get_token_from_request() {
        if ( ! empty( $_POST['captcha_token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            return sanitize_text_field( wp_unslash( $_POST['captcha_token'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
        }

        $map = array(
            'turnstile'    => 'cf-turnstile-response',
            'recaptcha_v2' => 'g-recaptcha-response',
            'recaptcha_v3' => 'g-recaptcha-response',
            'hcaptcha'     => 'h-captcha-response',
        );

        $provider = self::get_config()['captcha_provider'];
        $field    = $map[ $provider ] ?? '';

        if ( $field && ! empty( $_POST[ $field ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            return sanitize_text_field( wp_unslash( $_POST[ $field ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
        }

        return '';
    }

    /**
     * Verify token with provider API.
     *
     * @param string $token   Token.
     * @param string $context Action context.
     * @return true|WP_Error
     */
    public static function verify_token( $token, $context = 'login' ) {
        $config = self::get_config();
        $secret = $config['captcha_secret_key'];

        if ( empty( $secret ) ) {
            return new WP_Error( 'neonews_captcha_config', __( 'CAPTCHA is not configured.', 'neonews-core' ) );
        }

        switch ( $config['captcha_provider'] ) {
            case 'turnstile':
                $response = wp_remote_post(
                    'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                    array(
                        'timeout' => 15,
                        'body'    => array(
                            'secret'   => $secret,
                            'response' => $token,
                            'remoteip' => self::get_client_ip(),
                        ),
                    )
                );
                break;

            case 'hcaptcha':
                $response = wp_remote_post(
                    'https://api.hcaptcha.com/siteverify',
                    array(
                        'timeout' => 15,
                        'body'    => array(
                            'secret'   => $secret,
                            'response' => $token,
                            'remoteip' => self::get_client_ip(),
                        ),
                    )
                );
                break;

            case 'recaptcha_v2':
            case 'recaptcha_v3':
            default:
                $response = wp_remote_post(
                    'https://www.google.com/recaptcha/api/siteverify',
                    array(
                        'timeout' => 15,
                        'body'    => array(
                            'secret'   => $secret,
                            'response' => $token,
                            'remoteip' => self::get_client_ip(),
                        ),
                    )
                );
                break;
        }

        if ( is_wp_error( $response ) ) {
            return new WP_Error(
                'neonews_captcha_http',
                __( 'Could not verify CAPTCHA. Please try again.', 'neonews-core' )
            );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body['success'] ) ) {
            return new WP_Error(
                'neonews_captcha_failed',
                __( 'CAPTCHA verification failed. Please try again.', 'neonews-core' )
            );
        }

        if ( 'recaptcha_v3' === $config['captcha_provider'] ) {
            $score = isset( $body['score'] ) ? (float) $body['score'] : 0;
            if ( $score < (float) $config['captcha_v3_threshold'] ) {
                return new WP_Error(
                    'neonews_captcha_score',
                    __( 'CAPTCHA score too low. Please try again.', 'neonews-core' )
                );
            }
        }

        /**
         * Fires after successful captcha verification.
         *
         * @param array  $body    Provider response body.
         * @param string $context login|register.
         */
        do_action( 'neonews_captcha_verified', $body, $context );

        return true;
    }

    /**
     * Verify wp-login.php login attempt.
     *
     * @param WP_User|WP_Error|null $user     User or error.
     * @param string                $username Username.
     * @param string                $password Password.
     * @return WP_User|WP_Error|null
     */
    public function verify_wp_login( $user, $username, $password ) {
        if ( empty( $_POST['log'] ) || ! self::is_required_for( 'login' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            return $user;
        }

        if ( is_wp_error( $user ) ) {
            return $user;
        }

        $result = self::verify_request( 'login' );
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return $user;
    }

    /**
     * Verify wp-login.php registration.
     *
     * @param WP_Error $errors               Errors.
     * @param string   $sanitized_user_login Login.
     * @param string   $user_email           Email.
     * @return WP_Error
     */
    public function verify_wp_register( $errors, $sanitized_user_login, $user_email ) {
        if ( ! self::is_required_for( 'register' ) ) {
            return $errors;
        }

        $result = self::verify_request( 'register' );
        if ( is_wp_error( $result ) ) {
            $errors->add( 'neonews_captcha', $result->get_error_message() );
        }

        return $errors;
    }

    /**
     * Client IP for provider verification.
     *
     * @return string
     */
    private static function get_client_ip() {
        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
            return trim( explode( ',', $ip )[0] );
        }
        if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }
        return '';
    }
}

/**
 * Verify captcha for a context.
 *
 * @param string $context login|register.
 * @return true|WP_Error
 */
function neonews_verify_captcha( $context ) {
    return NeoNews_Captcha::verify_request( $context );
}
