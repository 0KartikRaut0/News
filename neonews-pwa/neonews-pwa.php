<?php
/**
 * Plugin Name: NeoNews PWA
 * Plugin URI: https://example.com/neonews
 * Description: Progressive Web App support for NeoNews. Add to home screen functionality with offline caching.
 * Version: 1.1.0
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: NeoNews Team
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: neonews-pwa
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NEONEWS_PWA_VERSION', '1.1.0' );
define( 'NEONEWS_PWA_FILE', __FILE__ );
define( 'NEONEWS_PWA_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEONEWS_PWA_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Plugin Class
 */
final class NeoNews_PWA {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        require_once NEONEWS_PWA_DIR . 'inc/class-pwa-install-prompt.php';
        NeoNews_PWA_Install_Prompt::get_instance();
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook( NEONEWS_PWA_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( NEONEWS_PWA_FILE, array( $this, 'deactivate' ) );

        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'wp_head', array( $this, 'add_manifest_link' ), 1 );
        add_action( 'wp_head', array( $this, 'add_meta_tags' ), 1 );
        add_action( 'wp_footer', array( $this, 'register_service_worker' ) );
        add_action( 'init', array( $this, 'register_routes' ) );
        
        if ( is_admin() ) {
            add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
            add_action( 'admin_init', array( $this, 'register_settings' ) );
        }
    }

    /**
     * Plugin activation
     */
    public function activate() {
        if ( ! get_option( 'neonews_pwa_version' ) ) {
            add_option( 'neonews_pwa_version', NEONEWS_PWA_VERSION );
            add_option( 'neonews_pwa_settings', $this->get_default_settings() );
        }

        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Load textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'neonews-pwa',
            false,
            dirname( plugin_basename( NEONEWS_PWA_FILE ) ) . '/languages'
        );
    }

    /**
     * Register custom routes
     */
    public function register_routes() {
        add_rewrite_rule( '^manifest\.json$', 'index.php?neonews_pwa_manifest=1', 'top' );
        add_rewrite_rule( '^sw\.js$', 'index.php?neonews_pwa_sw=1', 'top' );
        add_rewrite_rule( '^offline/?$', 'index.php?neonews_pwa_offline=1', 'top' );

        add_filter( 'query_vars', function( $vars ) {
            $vars[] = 'neonews_pwa_manifest';
            $vars[] = 'neonews_pwa_sw';
            $vars[] = 'neonews_pwa_offline';
            return $vars;
        } );

        add_action( 'template_redirect', array( $this, 'handle_pwa_requests' ) );
    }

    /**
     * Handle PWA requests
     */
    public function handle_pwa_requests() {
        if ( get_query_var( 'neonews_pwa_manifest' ) ) {
            $this->output_manifest();
            exit;
        }

        if ( get_query_var( 'neonews_pwa_sw' ) ) {
            $this->output_service_worker();
            exit;
        }

        if ( get_query_var( 'neonews_pwa_offline' ) ) {
            $this->output_offline_page();
            exit;
        }
    }

    /**
     * Add manifest link to head
     */
    public function add_manifest_link() {
        if ( ! $this->is_enabled() ) {
            return;
        }
        ?>
        <link rel="manifest" href="<?php echo esc_url( home_url( '/manifest.json' ) ); ?>">
        <?php
    }

    /**
     * Add PWA meta tags
     */
    public function add_meta_tags() {
        if ( ! $this->is_enabled() ) {
            return;
        }

        $settings = $this->get_settings();
        ?>
        <meta name="theme-color" content="<?php echo esc_attr( $settings['theme_color'] ); ?>">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="<?php echo esc_attr( $settings['app_name'] ); ?>">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="application-name" content="<?php echo esc_attr( $settings['app_name'] ); ?>">
        <meta name="msapplication-TileColor" content="<?php echo esc_attr( $settings['theme_color'] ); ?>">
        <?php
        
        $icon_url = get_site_icon_url( 192 );
        if ( $icon_url ) {
            ?>
            <link rel="apple-touch-icon" href="<?php echo esc_url( $icon_url ); ?>">
            <?php
        }
    }

    /**
     * Register service worker
     */
    public function register_service_worker() {
        if ( ! $this->is_enabled() ) {
            return;
        }
        ?>
        <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo esc_url( home_url( '/sw.js' ) ); ?>', {
                    scope: '/'
                }).then(function(registration) {
                    console.log('NeoNews PWA: Service Worker registered with scope:', registration.scope);
                }).catch(function(error) {
                    console.log('NeoNews PWA: Service Worker registration failed:', error);
                });
            });
        }
        </script>
        <?php
    }

    /**
     * Output manifest.json
     */
    private function output_manifest() {
        $settings = $this->get_settings();

        $manifest = array(
            'name'             => $settings['app_name'],
            'short_name'       => $settings['app_short_name'],
            'description'      => $settings['app_description'],
            'start_url'        => home_url( '/?utm_source=pwa' ),
            'scope'            => '/',
            'display'          => $settings['display'],
            'orientation'      => $settings['orientation'],
            'theme_color'      => $settings['theme_color'],
            'background_color' => $settings['background_color'],
            'icons'            => $this->get_icons(),
            'categories'       => array( 'news', 'magazine' ),
            'lang'             => get_locale(),
            'dir'              => is_rtl() ? 'rtl' : 'ltr',
        );

        header( 'Content-Type: application/manifest+json' );
        header( 'Cache-Control: public, max-age=86400' );
        echo wp_json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    }

    /**
     * Get PWA icons
     */
    private function get_icons() {
        $icons = array();
        $sizes = array( 72, 96, 128, 144, 152, 192, 384, 512 );

        foreach ( $sizes as $size ) {
            $icon_url = get_site_icon_url( $size );
            
            if ( $icon_url ) {
                $icons[] = array(
                    'src'     => $icon_url,
                    'sizes'   => "{$size}x{$size}",
                    'type'    => 'image/png',
                    'purpose' => 'any maskable',
                );
            }
        }

        if ( empty( $icons ) ) {
            $icons[] = array(
                'src'     => NEONEWS_PWA_URL . 'assets/icon-192.png',
                'sizes'   => '192x192',
                'type'    => 'image/png',
                'purpose' => 'any maskable',
            );
            $icons[] = array(
                'src'     => NEONEWS_PWA_URL . 'assets/icon-512.png',
                'sizes'   => '512x512',
                'type'    => 'image/png',
                'purpose' => 'any maskable',
            );
        }

        return $icons;
    }

    /**
     * Output service worker
     */
    private function output_service_worker() {
        $settings = $this->get_settings();
        $cache_version = 'neonews-v' . NEONEWS_PWA_VERSION;
        
        header( 'Content-Type: application/javascript' );
        header( 'Cache-Control: no-cache' );
        header( 'Service-Worker-Allowed: /' );

        $home_url = home_url( '/' );
        $offline_url = home_url( '/offline/' );
        $theme_url = get_template_directory_uri();
        ?>
const CACHE_NAME = '<?php echo esc_js( $cache_version ); ?>';
const OFFLINE_URL = '<?php echo esc_js( $offline_url ); ?>';

const PRECACHE_URLS = [
    '<?php echo esc_js( $home_url ); ?>',
    '<?php echo esc_js( $offline_url ); ?>',
    '<?php echo esc_js( $theme_url . '/style.css' ); ?>',
    '<?php echo esc_js( $theme_url . '/css/main.css' ); ?>'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('NeoNews PWA: Pre-caching resources');
                return cache.addAll(PRECACHE_URLS);
            })
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('NeoNews PWA: Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);
    
    if (url.pathname.includes('/wp-admin') || 
        url.pathname.includes('/wp-login') ||
        url.pathname.includes('/wp-json')) {
        return;
    }

    if (event.request.destination === 'image' ||
        event.request.destination === 'style' ||
        event.request.destination === 'font') {
        event.respondWith(
            caches.match(event.request)
                .then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    return fetch(event.request).then((response) => {
                        if (!response || response.status !== 200) {
                            return response;
                        }
                        const responseToCache = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseToCache);
                        });
                        return response;
                    });
                })
        );
        return;
    }

    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                    return response;
                })
                .catch(() => {
                    return caches.match(event.request)
                        .then((cachedResponse) => {
                            if (cachedResponse) {
                                return cachedResponse;
                            }
                            return caches.match(OFFLINE_URL);
                        });
                })
        );
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then((cachedResponse) => {
                return cachedResponse || fetch(event.request);
            })
    );
});
        <?php
    }

    /**
     * Output offline page
     */
    private function output_offline_page() {
        $settings = $this->get_settings();
        ?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_locale() ); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="<?php echo esc_attr( $settings['theme_color'] ); ?>">
    <title><?php esc_html_e( 'Offline', 'neonews-pwa' ); ?> - <?php echo esc_html( $settings['app_name'] ); ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1d3557 0%, #457b9d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #fff;
        }
        .offline-container {
            text-align: center;
            max-width: 400px;
        }
        .offline-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.9;
        }
        h1 {
            font-size: 1.75rem;
            margin-bottom: 15px;
        }
        p {
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .retry-btn {
            display: inline-block;
            padding: 12px 30px;
            background: #fff;
            color: #1d3557;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .retry-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">📡</div>
        <h1><?php esc_html_e( "You're Offline", 'neonews-pwa' ); ?></h1>
        <p><?php esc_html_e( "It looks like you've lost your internet connection. Please check your network and try again.", 'neonews-pwa' ); ?></p>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="retry-btn" onclick="window.location.reload(); return false;">
            <?php esc_html_e( 'Try Again', 'neonews-pwa' ); ?>
        </a>
    </div>
    <script>
        window.addEventListener('online', () => window.location.reload());
    </script>
</body>
</html>
        <?php
        exit;
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'neonews-settings',
            __( 'PWA Settings', 'neonews-pwa' ),
            __( 'PWA', 'neonews-pwa' ),
            'manage_options',
            'neonews-pwa',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'neonews_pwa_group',
            'neonews_pwa_settings',
            array( 'sanitize_callback' => array( $this, 'sanitize_settings' ) )
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings( $input ) {
        $position = sanitize_key( $input['install_banner_position'] ?? 'bottom' );
        if ( ! in_array( $position, array( 'top', 'bottom' ), true ) ) {
            $position = 'bottom';
        }

        return array(
            'enable_pwa'               => ! empty( $input['enable_pwa'] ),
            'enable_install_banner'    => ! empty( $input['enable_install_banner'] ),
            'install_banner_title'     => sanitize_text_field( $input['install_banner_title'] ?? '' ),
            'install_banner_message'   => sanitize_textarea_field( $input['install_banner_message'] ?? '' ),
            'install_dismiss_days'     => max( 1, min( 90, absint( $input['install_dismiss_days'] ?? 7 ) ) ),
            'install_banner_position'  => $position,
            'install_show_ios'         => ! empty( $input['install_show_ios'] ),
            'install_show_desktop'     => ! empty( $input['install_show_desktop'] ),
            'app_name'                 => sanitize_text_field( $input['app_name'] ?? get_bloginfo( 'name' ) ),
            'app_short_name'           => sanitize_text_field( substr( $input['app_short_name'] ?? '', 0, 12 ) ),
            'app_description'          => sanitize_text_field( $input['app_description'] ?? '' ),
            'theme_color'              => sanitize_hex_color( $input['theme_color'] ?? '#e63946' ),
            'background_color'         => sanitize_hex_color( $input['background_color'] ?? '#ffffff' ),
            'display'                  => sanitize_key( $input['display'] ?? 'standalone' ),
            'orientation'              => sanitize_key( $input['orientation'] ?? 'any' ),
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( isset( $_POST['neonews_pwa_settings'] ) && check_admin_referer( 'neonews_pwa_save', 'neonews_pwa_nonce' ) ) {
            $settings = $this->sanitize_settings( $_POST['neonews_pwa_settings'] );
            update_option( 'neonews_pwa_settings', $settings );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'neonews-pwa' ) . '</p></div>';
        }

        $settings = $this->get_settings();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'PWA Settings', 'neonews-pwa' ); ?></h1>
            
            <form method="post">
                <?php wp_nonce_field( 'neonews_pwa_save', 'neonews_pwa_nonce' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enable PWA', 'neonews-pwa' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="neonews_pwa_settings[enable_pwa]" value="1" <?php checked( $settings['enable_pwa'] ); ?>>
                                <?php esc_html_e( 'Enable Progressive Web App features', 'neonews-pwa' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="app_name"><?php esc_html_e( 'App Name', 'neonews-pwa' ); ?></label></th>
                        <td>
                            <input type="text" id="app_name" name="neonews_pwa_settings[app_name]" value="<?php echo esc_attr( $settings['app_name'] ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="app_short_name"><?php esc_html_e( 'Short Name', 'neonews-pwa' ); ?></label></th>
                        <td>
                            <input type="text" id="app_short_name" name="neonews_pwa_settings[app_short_name]" value="<?php echo esc_attr( $settings['app_short_name'] ); ?>" class="regular-text" maxlength="12">
                            <p class="description"><?php esc_html_e( 'Maximum 12 characters. Used on home screen.', 'neonews-pwa' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="app_description"><?php esc_html_e( 'Description', 'neonews-pwa' ); ?></label></th>
                        <td>
                            <textarea id="app_description" name="neonews_pwa_settings[app_description]" class="large-text" rows="3"><?php echo esc_textarea( $settings['app_description'] ); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="theme_color"><?php esc_html_e( 'Theme Color', 'neonews-pwa' ); ?></label></th>
                        <td>
                            <input type="color" id="theme_color" name="neonews_pwa_settings[theme_color]" value="<?php echo esc_attr( $settings['theme_color'] ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="background_color"><?php esc_html_e( 'Background Color', 'neonews-pwa' ); ?></label></th>
                        <td>
                            <input type="color" id="background_color" name="neonews_pwa_settings[background_color]" value="<?php echo esc_attr( $settings['background_color'] ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="display"><?php esc_html_e( 'Display Mode', 'neonews-pwa' ); ?></label></th>
                        <td>
                            <select id="display" name="neonews_pwa_settings[display]">
                                <option value="standalone" <?php selected( $settings['display'], 'standalone' ); ?>><?php esc_html_e( 'Standalone', 'neonews-pwa' ); ?></option>
                                <option value="fullscreen" <?php selected( $settings['display'], 'fullscreen' ); ?>><?php esc_html_e( 'Fullscreen', 'neonews-pwa' ); ?></option>
                                <option value="minimal-ui" <?php selected( $settings['display'], 'minimal-ui' ); ?>><?php esc_html_e( 'Minimal UI', 'neonews-pwa' ); ?></option>
                                <option value="browser" <?php selected( $settings['display'], 'browser' ); ?>><?php esc_html_e( 'Browser', 'neonews-pwa' ); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Install app banner', 'neonews-pwa' ); ?></h2>
                <p class="description"><?php esc_html_e( 'Shows a site-wide prompt so visitors can install your news app on desktop, Android, or iPhone.', 'neonews-pwa' ); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Show install banner', 'neonews-pwa' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="neonews_pwa_settings[enable_install_banner]" value="1" <?php checked( ! empty( $settings['enable_install_banner'] ) ); ?>>
                                <?php esc_html_e( 'Enable “Install app” prompt for visitors', 'neonews-pwa' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="install_banner_title"><?php esc_html_e( 'Banner title', 'neonews-pwa' ); ?></label></th>
                        <td>
                            <input type="text" id="install_banner_title" name="neonews_pwa_settings[install_banner_title]" value="<?php echo esc_attr( $settings['install_banner_title'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( sprintf( __( 'Install %s', 'neonews-pwa' ), $settings['app_name'] ) ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="install_banner_message"><?php esc_html_e( 'Banner message', 'neonews-pwa' ); ?></label></th>
                        <td>
                            <textarea id="install_banner_message" name="neonews_pwa_settings[install_banner_message]" class="large-text" rows="2" placeholder="<?php esc_attr_e( 'Get quick access on your phone, tablet, or computer — works like a native app.', 'neonews-pwa' ); ?>"><?php echo esc_textarea( $settings['install_banner_message'] ); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="install_banner_position"><?php esc_html_e( 'Banner position', 'neonews-pwa' ); ?></label></th>
                        <td>
                            <select id="install_banner_position" name="neonews_pwa_settings[install_banner_position]">
                                <option value="bottom" <?php selected( $settings['install_banner_position'], 'bottom' ); ?>><?php esc_html_e( 'Bottom of screen', 'neonews-pwa' ); ?></option>
                                <option value="top" <?php selected( $settings['install_banner_position'], 'top' ); ?>><?php esc_html_e( 'Top of screen', 'neonews-pwa' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="install_dismiss_days"><?php esc_html_e( 'Hide after dismiss', 'neonews-pwa' ); ?></label></th>
                        <td>
                            <input type="number" id="install_dismiss_days" name="neonews_pwa_settings[install_dismiss_days]" value="<?php echo esc_attr( $settings['install_dismiss_days'] ); ?>" min="1" max="90" class="small-text"> <?php esc_html_e( 'days', 'neonews-pwa' ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Platform hints', 'neonews-pwa' ); ?></th>
                        <td>
                            <label><input type="checkbox" name="neonews_pwa_settings[install_show_ios]" value="1" <?php checked( ! empty( $settings['install_show_ios'] ) ); ?>> <?php esc_html_e( 'Show iPhone/iPad steps (Share → Add to Home Screen)', 'neonews-pwa' ); ?></label><br>
                            <label><input type="checkbox" name="neonews_pwa_settings[install_show_desktop]" value="1" <?php checked( ! empty( $settings['install_show_desktop'] ) ); ?>> <?php esc_html_e( 'Show desktop hint when browser has no install button yet', 'neonews-pwa' ); ?></label>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <div class="nn-pwa-info" style="background: #fff; padding: 20px; margin-top: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
                <h2><?php esc_html_e( 'PWA Requirements', 'neonews-pwa' ); ?></h2>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><?php esc_html_e( 'Your site must use HTTPS for PWA features to work.', 'neonews-pwa' ); ?></li>
                    <li><?php esc_html_e( 'Set a Site Icon in Appearance > Customize > Site Identity for app icons.', 'neonews-pwa' ); ?></li>
                    <li><?php esc_html_e( 'After making changes, clear your browser cache and service worker.', 'neonews-pwa' ); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Default settings.
     *
     * @return array
     */
    public function get_default_settings() {
        return array(
            'enable_pwa'               => true,
            'enable_install_banner'    => true,
            'install_banner_title'     => '',
            'install_banner_message'   => '',
            'install_dismiss_days'     => 7,
            'install_banner_position'  => 'bottom',
            'install_show_ios'         => true,
            'install_show_desktop'     => true,
            'app_name'                 => get_bloginfo( 'name' ),
            'app_short_name'           => substr( get_bloginfo( 'name' ), 0, 12 ),
            'app_description'          => get_bloginfo( 'description' ),
            'theme_color'              => '#e63946',
            'background_color'         => '#ffffff',
            'display'                  => 'standalone',
            'orientation'              => 'any',
        );
    }

    /**
     * Get settings
     */
    public function get_settings() {
        $settings = get_option( 'neonews_pwa_settings', array() );
        return wp_parse_args( $settings, $this->get_default_settings() );
    }

    /**
     * Check if PWA is enabled
     */
    public function is_enabled() {
        $settings = $this->get_settings();
        return ! empty( $settings['enable_pwa'] );
    }
}

/**
 * Initialize plugin
 */
function neonews_pwa() {
    return NeoNews_PWA::get_instance();
}

neonews_pwa();
