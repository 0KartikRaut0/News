<?php
/**
 * Security Center admin dashboard.
 *
 * @package NeoNews_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin dashboard.
 */
class NeoNews_Security_Dashboard {

    /**
     * Init.
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'menu' ), 25 );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
        add_action( 'wp_ajax_neonews_security_scan', array( __CLASS__, 'ajax_scan' ) );
        add_action( 'wp_ajax_neonews_security_analyze', array( __CLASS__, 'ajax_analyze' ) );
        add_action( 'wp_ajax_neonews_security_fix', array( __CLASS__, 'ajax_fix' ) );
        add_action( 'wp_ajax_neonews_security_fix_all', array( __CLASS__, 'ajax_fix_all' ) );
        add_action( 'wp_ajax_neonews_security_get_log', array( __CLASS__, 'ajax_get_log' ) );
        add_action( 'admin_post_neonews_security_save_settings', array( __CLASS__, 'save_settings' ) );
    }

    /**
     * Admin menu.
     */
    public static function menu() {
        add_submenu_page(
            'neonews-settings',
            __( 'Security Center', 'neonews-security' ),
            __( 'Security Center', 'neonews-security' ),
            'manage_options',
            'neonews-security',
            array( __CLASS__, 'render' )
        );
    }

    /**
     * Enqueue assets.
     *
     * @param string $hook Hook.
     */
    public static function assets( $hook ) {
        if ( strpos( $hook, 'neonews-security' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'neonews-security-admin',
            NEONEWS_SECURITY_URL . 'admin/css/admin.css',
            array(),
            NEONEWS_SECURITY_VERSION
        );

        wp_enqueue_script(
            'neonews-security-admin',
            NEONEWS_SECURITY_URL . 'admin/js/admin.js',
            array(),
            NEONEWS_SECURITY_VERSION,
            true
        );

        $last = NeoNews_Security_Scanner::get_last_scan();

        wp_localize_script( 'neonews-security-admin', 'neonewsSecurity', array(
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'neonews_security' ),
            'lastScan'  => $last,
            'i18n'      => array(
                'scanning'    => __( 'Scanning your site…', 'neonews-security' ),
                'analyzing'   => __( 'AI is analyzing threats…', 'neonews-security' ),
                'fixing'      => __( 'Applying fix…', 'neonews-security' ),
                'fixAll'      => __( 'Fix all automatically', 'neonews-security' ),
                'fixSuccess'  => __( 'Fix applied successfully.', 'neonews-security' ),
                'fixError'    => __( 'Could not apply fix.', 'neonews-security' ),
                'rescan'      => __( 'Run security scan', 'neonews-security' ),
                'score'       => __( 'Security Score', 'neonews-security' ),
                'noFindings'  => __( 'No issues found — excellent!', 'neonews-security' ),
            ),
        ) );
    }

    /**
     * Render dashboard.
     */
    public static function render() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $settings = NeoNews_Security::get_settings();
        $last     = NeoNews_Security_Scanner::get_last_scan();
        $analysis = ! empty( $last['findings'] ) ? NeoNews_Threat_Analyzer::analyze( $last['findings'] ) : null;
        $counts   = NeoNews_Security_Log::count_by_severity();
        $logs     = NeoNews_Security_Log::get_recent( 20 );
        ?>
        <div class="wrap nn-sec-wrap">
            <header class="nn-sec-header">
                <div>
                    <h1><?php esc_html_e( 'NeoNews Security Center', 'neonews-security' ); ?></h1>
                    <p><?php esc_html_e( 'Scan, monitor, and fix security issues with AI-powered analysis and one-click hardening.', 'neonews-security' ); ?></p>
                </div>
                <div class="nn-sec-header-actions">
                    <button type="button" class="button button-primary" id="nn-sec-scan"><?php esc_html_e( 'Run Security Scan', 'neonews-security' ); ?></button>
                    <?php if ( $analysis && ! empty( $analysis['fix_all_ids'] ) ) : ?>
                        <button type="button" class="button button-secondary" id="nn-sec-fix-all" data-ids="<?php echo esc_attr( wp_json_encode( $analysis['fix_all_ids'] ) ); ?>">
                            <?php esc_html_e( 'Fix All (Auto)', 'neonews-security' ); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </header>

            <div class="nn-sec-grid">
                <div class="nn-sec-card nn-sec-score-card">
                    <h2><?php esc_html_e( 'Security Score', 'neonews-security' ); ?></h2>
                    <div class="nn-sec-score-ring" data-score="<?php echo esc_attr( $last['score'] ); ?>">
                        <span class="nn-sec-score-value"><?php echo esc_html( (int) $last['score'] ); ?></span>
                        <span class="nn-sec-score-label">/100</span>
                    </div>
                    <?php if ( $last['time'] ) : ?>
                        <p class="nn-sec-muted"><?php printf( esc_html__( 'Last scan: %s', 'neonews-security' ), esc_html( wp_date( 'M j, Y g:i A', $last['time'] ) ) ); ?></p>
                    <?php else : ?>
                        <p class="nn-sec-muted"><?php esc_html_e( 'No scan yet — click Run Security Scan.', 'neonews-security' ); ?></p>
                    <?php endif; ?>
                </div>

                <div class="nn-sec-card">
                    <h2><?php esc_html_e( '7-Day Activity', 'neonews-security' ); ?></h2>
                    <ul class="nn-sec-stat-list">
                        <li><span class="sev critical"><?php esc_html_e( 'Critical', 'neonews-security' ); ?></span> <strong><?php echo esc_html( $counts['critical'] ); ?></strong></li>
                        <li><span class="sev high"><?php esc_html_e( 'High', 'neonews-security' ); ?></span> <strong><?php echo esc_html( $counts['high'] ); ?></strong></li>
                        <li><span class="sev medium"><?php esc_html_e( 'Medium', 'neonews-security' ); ?></span> <strong><?php echo esc_html( $counts['medium'] ); ?></strong></li>
                        <li><span class="sev low"><?php esc_html_e( 'Low', 'neonews-security' ); ?></span> <strong><?php echo esc_html( $counts['low'] ); ?></strong></li>
                    </ul>
                </div>

                <div class="nn-sec-card nn-sec-ai-card">
                    <h2><?php esc_html_e( 'AI Threat Analysis', 'neonews-security' ); ?></h2>
                    <div id="nn-sec-ai-content">
                        <?php if ( $analysis ) : ?>
                            <p class="nn-sec-risk nn-sec-risk-<?php echo esc_attr( $analysis['risk_level'] ); ?>">
                                <?php echo esc_html( $analysis['summary'] ); ?>
                            </p>
                            <div class="nn-sec-narrative"><?php echo nl2br( esc_html( $analysis['narrative'] ) ); ?></div>
                        <?php else : ?>
                            <p class="nn-sec-muted"><?php esc_html_e( 'Run a scan to generate AI security analysis.', 'neonews-security' ); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="nn-sec-two-col">
                <div class="nn-sec-card nn-sec-findings-card">
                    <h2><?php esc_html_e( 'Findings & Quick Fixes', 'neonews-security' ); ?></h2>
                    <div id="nn-sec-findings">
                        <?php self::render_findings( $analysis ? $analysis['actions'] : array() ); ?>
                    </div>
                </div>

                <div class="nn-sec-card">
                    <h2><?php esc_html_e( 'Security Activity Log', 'neonews-security' ); ?></h2>
                    <div id="nn-sec-log" class="nn-sec-log">
                        <?php self::render_log( $logs ); ?>
                    </div>
                </div>
            </div>

            <div class="nn-sec-card nn-sec-settings-card">
                <h2><?php esc_html_e( 'Protection Settings', 'neonews-security' ); ?></h2>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'neonews_security_settings' ); ?>
                    <input type="hidden" name="action" value="neonews_security_save_settings" />
                    <table class="form-table">
                        <?php self::render_toggle( 'encrypt_api_keys', __( 'Encrypt API keys & scripts at rest (AES-256)', 'neonews-security' ), $settings ); ?>
                        <?php self::render_toggle( 'security_headers', __( 'Send security headers (HSTS, X-Frame-Options, etc.)', 'neonews-security' ), $settings ); ?>
                        <?php self::render_toggle( 'block_xmlrpc', __( 'Block XML-RPC', 'neonews-security' ), $settings ); ?>
                        <?php self::render_toggle( 'hide_wp_version', __( 'Hide WordPress version', 'neonews-security' ), $settings ); ?>
                        <?php self::render_toggle( 'disable_file_edit', __( 'Disable theme/plugin file editor', 'neonews-security' ), $settings ); ?>
                        <?php self::render_toggle( 'block_user_enum', __( 'Block user/author enumeration', 'neonews-security' ), $settings ); ?>
                        <?php self::render_toggle( 'uploads_php_block', __( 'Block PHP execution in uploads', 'neonews-security' ), $settings ); ?>
                        <?php self::render_toggle( 'login_limit_enabled', __( 'Login brute-force protection', 'neonews-security' ), $settings ); ?>
                        <?php self::render_toggle( 'force_secure_auth_cookie', __( 'Force secure authentication cookies (HTTPS)', 'neonews-security' ), $settings ); ?>
                        <?php self::render_toggle( 'email_alerts', __( 'Email alerts for lockouts', 'neonews-security' ), $settings ); ?>
                        <tr>
                            <th><?php esc_html_e( 'Max login attempts', 'neonews-security' ); ?></th>
                            <td><input type="number" name="login_max_attempts" value="<?php echo esc_attr( $settings['login_max_attempts'] ); ?>" min="3" max="20" class="small-text" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Lockout duration (minutes)', 'neonews-security' ); ?></th>
                            <td><input type="number" name="login_lockout_minutes" value="<?php echo esc_attr( $settings['login_lockout_minutes'] ); ?>" min="5" max="120" class="small-text" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Alert email', 'neonews-security' ); ?></th>
                            <td><input type="email" name="alert_email" value="<?php echo esc_attr( $settings['alert_email'] ); ?>" class="regular-text" /></td>
                        </tr>
                    </table>
                    <?php submit_button( __( 'Save Protection Settings', 'neonews-security' ) ); ?>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Render toggle row.
     *
     * @param string $key      Key.
     * @param string $label    Label.
     * @param array  $settings Settings.
     */
    private static function render_toggle( $key, $label, $settings ) {
        ?>
        <tr>
            <th><?php echo esc_html( $label ); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( ! empty( $settings[ $key ] ) ); ?> />
                    <?php esc_html_e( 'Enabled', 'neonews-security' ); ?>
                </label>
            </td>
        </tr>
        <?php
    }

    /**
     * Render findings list.
     *
     * @param array $actions Actions.
     */
    private static function render_findings( $actions ) {
        if ( empty( $actions ) ) {
            echo '<p class="nn-sec-muted">' . esc_html__( 'No findings — run a scan.', 'neonews-security' ) . '</p>';
            return;
        }
        echo '<ul class="nn-sec-finding-list">';
        foreach ( $actions as $a ) {
            ?>
            <li class="nn-sec-finding sev-<?php echo esc_attr( $a['severity'] ); ?>">
                <div class="nn-sec-finding-head">
                    <strong><?php echo esc_html( $a['title'] ); ?></strong>
                    <span class="nn-sec-badge"><?php echo esc_html( strtoupper( $a['severity'] ) ); ?></span>
                </div>
                <p><?php echo esc_html( $a['detail'] ); ?></p>
                <?php if ( ! empty( $a['fixable'] ) ) : ?>
                    <button type="button" class="button button-small nn-sec-fix-btn" data-fix-id="<?php echo esc_attr( $a['id'] ); ?>">
                        <?php esc_html_e( 'Fix automatically', 'neonews-security' ); ?>
                    </button>
                <?php else : ?>
                    <p class="nn-sec-instruction"><em><?php echo esc_html( $a['instruction'] ); ?></em></p>
                <?php endif; ?>
            </li>
            <?php
        }
        echo '</ul>';
    }

    /**
     * Render activity log.
     *
     * @param array $logs Logs.
     */
    private static function render_log( $logs ) {
        if ( empty( $logs ) ) {
            echo '<p class="nn-sec-muted">' . esc_html__( 'No activity yet.', 'neonews-security' ) . '</p>';
            return;
        }
        echo '<ul class="nn-sec-log-list">';
        foreach ( $logs as $row ) {
            ?>
            <li class="sev-<?php echo esc_attr( $row['severity'] ); ?>">
                <time><?php echo esc_html( mysql2date( 'M j, g:i A', $row['created_at'] ) ); ?></time>
                <span class="nn-sec-log-type"><?php echo esc_html( $row['event_type'] ); ?></span>
                <span><?php echo esc_html( $row['message'] ); ?></span>
                <?php if ( ! empty( $row['user_label'] ) ) : ?>
                    <span class="nn-sec-muted">— <?php echo esc_html( $row['user_label'] ); ?></span>
                <?php endif; ?>
            </li>
            <?php
        }
        echo '</ul>';
    }

    /**
     * Save settings form.
     */
    public static function save_settings() {
        if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'neonews_security_settings' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'neonews-security' ) );
        }

        $keys = array(
            'encrypt_api_keys', 'security_headers', 'block_xmlrpc', 'hide_wp_version',
            'disable_file_edit', 'block_user_enum', 'uploads_php_block', 'login_limit_enabled',
            'force_secure_auth_cookie', 'email_alerts',
        );

        $settings = NeoNews_Security::get_settings();
        foreach ( $keys as $key ) {
            $settings[ $key ] = ! empty( $_POST[ $key ] );
        }
        $settings['login_max_attempts']    = max( 3, absint( $_POST['login_max_attempts'] ?? 5 ) );
        $settings['login_lockout_minutes'] = max( 5, absint( $_POST['login_lockout_minutes'] ?? 15 ) );
        $settings['alert_email']           = sanitize_email( wp_unslash( $_POST['alert_email'] ?? '' ) );

        NeoNews_Security::update_settings( $settings );
        NeoNews_Auto_Fix::write_uploads_htaccess();
        if ( ! empty( $settings['disable_file_edit'] ) ) {
            NeoNews_Auto_Fix::write_mu_plugin_hardening();
        }

        NeoNews_Security_Log::log( 'settings', 'info', __( 'Security settings updated.', 'neonews-security' ) );

        wp_safe_redirect( add_query_arg( 'updated', '1', admin_url( 'admin.php?page=neonews-security' ) ) );
        exit;
    }

    /**
     * AJAX scan.
     */
    public static function ajax_scan() {
        check_ajax_referer( 'neonews_security', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }

        $findings = NeoNews_Security_Scanner::run_scan();
        $analysis = NeoNews_Threat_Analyzer::analyze( $findings );
        $last     = NeoNews_Security_Scanner::get_last_scan();

        ob_start();
        self::render_findings( $analysis['actions'] );
        $findings_html = ob_get_clean();

        wp_send_json_success( array(
            'score'         => $last['score'],
            'analysis'      => $analysis,
            'findings_html' => $findings_html,
            'fix_all_ids'   => $analysis['fix_all_ids'],
        ) );
    }

    /**
     * AJAX analyze only.
     */
    public static function ajax_analyze() {
        check_ajax_referer( 'neonews_security', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        $last = NeoNews_Security_Scanner::get_last_scan();
        wp_send_json_success( NeoNews_Threat_Analyzer::analyze( $last['findings'] ) );
    }

    /**
     * AJAX single fix.
     */
    public static function ajax_fix() {
        check_ajax_referer( 'neonews_security', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }

        $fix_id = isset( $_POST['fix_id'] ) ? sanitize_key( wp_unslash( $_POST['fix_id'] ) ) : '';
        $result = NeoNews_Auto_Fix::apply( $fix_id );

        if ( empty( $result['success'] ) ) {
            wp_send_json_error( $result );
        }

        $last     = NeoNews_Security_Scanner::get_last_scan();
        $analysis = NeoNews_Threat_Analyzer::analyze( $last['findings'] );

        ob_start();
        self::render_findings( $analysis['actions'] );
        $findings_html = ob_get_clean();

        ob_start();
        self::render_log( NeoNews_Security_Log::get_recent( 20 ) );
        $log_html = ob_get_clean();

        wp_send_json_success( array(
            'message'       => $result['message'],
            'score'         => $last['score'],
            'analysis'      => $analysis,
            'findings_html' => $findings_html,
            'log_html'      => $log_html,
            'fix_all_ids'   => $analysis['fix_all_ids'],
        ) );
    }

    /**
     * AJAX fix all.
     */
    public static function ajax_fix_all() {
        check_ajax_referer( 'neonews_security', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }

        $ids = isset( $_POST['fix_ids'] ) ? (array) wp_unslash( $_POST['fix_ids'] ) : array();
        $ids = array_map( 'sanitize_key', $ids );

        if ( empty( $ids ) ) {
            NeoNews_Auto_Fix::apply( 'enable_all_hardening' );
        } else {
            NeoNews_Auto_Fix::apply_multiple( $ids );
        }

        $last     = NeoNews_Security_Scanner::get_last_scan();
        $analysis = NeoNews_Threat_Analyzer::analyze( $last['findings'] );

        ob_start();
        self::render_findings( $analysis['actions'] );
        $findings_html = ob_get_clean();

        wp_send_json_success( array(
            'message'       => __( 'All available fixes applied.', 'neonews-security' ),
            'score'         => $last['score'],
            'analysis'      => $analysis,
            'findings_html' => $findings_html,
            'fix_all_ids'   => $analysis['fix_all_ids'],
        ) );
    }

    /**
     * AJAX refresh log.
     */
    public static function ajax_get_log() {
        check_ajax_referer( 'neonews_security', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        ob_start();
        self::render_log( NeoNews_Security_Log::get_recent( 50 ) );
        wp_send_json_success( array( 'html' => ob_get_clean() ) );
    }
}
