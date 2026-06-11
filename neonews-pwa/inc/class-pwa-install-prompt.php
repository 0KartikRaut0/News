<?php
/**
 * Install app banner — native prompt + iOS / desktop guidance.
 *
 * @package NeoNews_PWA
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_PWA_Install_Prompt {

    /**
     * @return self
     */
    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 45 );
        add_action( 'wp_footer', array( $this, 'render_banner' ), 12 );
    }

    /**
     * @return bool
     */
    public static function should_show() {
        $pwa = neonews_pwa();
        if ( ! $pwa->is_enabled() || is_admin() ) {
            return false;
        }

        $settings = $pwa->get_settings();
        return ! empty( $settings['enable_install_banner'] );
    }

    public function enqueue() {
        if ( ! self::should_show() ) {
            return;
        }

        $settings = neonews_pwa()->get_settings();

        wp_enqueue_style(
            'neonews-pwa-install',
            NEONEWS_PWA_URL . 'public/css/install-prompt.css',
            array(),
            NEONEWS_PWA_VERSION
        );

        wp_enqueue_script(
            'neonews-pwa-install',
            NEONEWS_PWA_URL . 'public/js/install-prompt.js',
            array(),
            NEONEWS_PWA_VERSION,
            true
        );

        $icon = get_site_icon_url( 192 );
        if ( ! $icon ) {
            $icon = get_site_icon_url( 512 );
        }

        wp_localize_script(
            'neonews-pwa-install',
            'neonewsPwaInstall',
            array(
                'appName'           => $settings['app_name'],
                'iconUrl'           => $icon ? $icon : '',
                'dismissDays'       => max( 1, min( 90, absint( $settings['install_dismiss_days'] ?? 7 ) ) ),
                'showIosHint'       => ! empty( $settings['install_show_ios'] ),
                'showDesktopHint'   => ! empty( $settings['install_show_desktop'] ),
                'position'          => in_array( $settings['install_banner_position'] ?? '', array( 'top', 'bottom' ), true )
                    ? $settings['install_banner_position']
                    : 'bottom',
                'iosSteps'          => array(
                    __( 'Tap the Share button', 'neonews-pwa' ),
                    __( 'Scroll and tap “Add to Home Screen”', 'neonews-pwa' ),
                    __( 'Tap Add — the app opens like a native app', 'neonews-pwa' ),
                ),
                'desktopHint'       => __( 'Click the install icon in your browser’s address bar, or open the browser menu and choose “Install app”.', 'neonews-pwa' ),
            )
        );
    }

    public function render_banner() {
        if ( ! self::should_show() ) {
            return;
        }

        $settings = neonews_pwa()->get_settings();
        $title    = $settings['install_banner_title'] ?? '';
        $message  = $settings['install_banner_message'] ?? '';

        if ( ! $title ) {
            $title = sprintf(
                /* translators: %s: app name */
                __( 'Install %s', 'neonews-pwa' ),
                $settings['app_name']
            );
        }

        if ( ! $message ) {
            $message = __( 'Get quick access on your phone, tablet, or computer — works like a native app.', 'neonews-pwa' );
        }

        $position = in_array( $settings['install_banner_position'] ?? '', array( 'top', 'bottom' ), true )
            ? $settings['install_banner_position']
            : 'bottom';

        $icon = get_site_icon_url( 96 );
        ?>
        <div id="nn-pwa-install-banner" class="nn-pwa-install nn-pwa-install--<?php echo esc_attr( $position ); ?> nn-pwa-install-hidden" role="dialog" aria-live="polite" aria-label="<?php esc_attr_e( 'Install app', 'neonews-pwa' ); ?>" aria-hidden="true">
            <div class="nn-pwa-install-inner nn-container">
                <div class="nn-pwa-install-brand">
                    <?php if ( $icon ) : ?>
                        <img class="nn-pwa-install-icon" src="<?php echo esc_url( $icon ); ?>" alt="" width="48" height="48" />
                    <?php else : ?>
                        <span class="nn-pwa-install-icon-fallback" aria-hidden="true">📱</span>
                    <?php endif; ?>
                    <div class="nn-pwa-install-copy">
                        <strong class="nn-pwa-install-title"><?php echo esc_html( $title ); ?></strong>
                        <p class="nn-pwa-install-message"><?php echo esc_html( $message ); ?></p>
                        <div class="nn-pwa-install-ios-steps nn-pwa-install-extra" hidden>
                            <ol class="nn-pwa-install-steps"></ol>
                        </div>
                        <p class="nn-pwa-install-desktop-hint nn-pwa-install-extra" hidden></p>
                    </div>
                </div>
                <div class="nn-pwa-install-actions">
                    <button type="button" class="nn-btn nn-pwa-install-btn" id="nn-pwa-install-action">
                        <?php esc_html_e( 'Install', 'neonews-pwa' ); ?>
                    </button>
                    <button type="button" class="nn-btn nn-btn-outline nn-pwa-install-dismiss" id="nn-pwa-install-dismiss">
                        <?php esc_html_e( 'Not now', 'neonews-pwa' ); ?>
                    </button>
                    <button type="button" class="nn-pwa-install-close" id="nn-pwa-install-close" aria-label="<?php esc_attr_e( 'Close', 'neonews-pwa' ); ?>">&times;</button>
                </div>
            </div>
        </div>
        <?php
    }
}
