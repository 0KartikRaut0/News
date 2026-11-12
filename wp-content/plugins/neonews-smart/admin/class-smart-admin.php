<?php
/**
 * Smart Tools admin settings.
 *
 * @package NeoNews_Smart
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Smart_Admin {

    public static function get_instance() {
        static $i = null;
        return $i ?: ( $i = new self() );
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'menu' ) );
        add_action( 'admin_init', array( $this, 'register' ) );
    }

    public function menu() {
        add_submenu_page(
            'neonews-settings',
            __( 'Smart Tools', 'neonews-smart' ),
            __( 'Smart Tools', 'neonews-smart' ),
            'manage_options',
            'neonews-smart',
            array( $this, 'render_page' )
        );
    }

    public function register() {
        register_setting(
            'neonews_smart_group',
            'neonews_smart_settings',
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize' ),
            )
        );
    }

    /**
     * @param array $input Input.
     * @return array
     */
    public function sanitize( $input ) {
        $defaults = function_exists( 'neonews_smart_default_settings' ) ? neonews_smart_default_settings() : array();
        if ( class_exists( 'NeoNews_FC_Feature_Overrides' ) ) {
            $prev = NeoNews_FC_Feature_Overrides::get_stored_option_array( 'neonews_smart_settings', $defaults );
        } else {
            $prev = NeoNews_Smart::get_settings();
        }
        $out  = $prev;

        $bools = array(
            'enable_auto_excerpt', 'enable_story_glance', 'enable_key_points', 'enable_toc',
            'enable_smart_related', 'enable_personalized_home', 'enable_submission_checks', 'enable_weekly_digest',
        );
        foreach ( $bools as $k ) {
            $out[ $k ] = ! empty( $input[ $k ] );
        }

        $out['excerpt_max_sentences']    = max( 1, min( 5, absint( $input['excerpt_max_sentences'] ?? 2 ) ) );
        $out['submission_max_links']     = max( 0, absint( $input['submission_max_links'] ?? 8 ) );
        $out['submission_banned_words']   = sanitize_text_field( $input['submission_banned_words'] ?? $prev['submission_banned_words'] );
        $out['digest_day']               = min( 6, max( 0, absint( $input['digest_day'] ?? 1 ) ) );
        $out['digest_hour']              = min( 23, max( 0, absint( $input['digest_hour'] ?? 9 ) ) );

        if ( ! empty( $input['reschedule_digest'] ) ) {
            NeoNews_Smart_Digest::unschedule_cron();
            NeoNews_Smart_Digest::schedule_cron();
        }

        return $out;
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $s = NeoNews_Smart::get_settings();
        $on  = 0;
        foreach ( array( 'enable_auto_excerpt', 'enable_story_glance', 'enable_smart_related', 'enable_personalized_home' ) as $k ) {
            if ( ! empty( $s[ $k ] ) ) {
                ++$on;
            }
        }
        if ( function_exists( 'neonews_admin_shell_begin' ) ) {
            neonews_admin_shell_begin(
                array(
                    'id'       => 'nn-smart-tools',
                    'icon'     => '🧠',
                    'title'    => __( 'Smart Tools', 'neonews-smart' ),
                    'subtitle' => __( 'Server-local editorial helpers — no external AI or API keys.', 'neonews-smart' ),
                    'stats'    => array(
                        array( 'icon' => '⚡', 'value' => (string) $on, 'label' => __( 'Key features on', 'neonews-smart' ) ),
                        array( 'icon' => '📬', 'value' => ! empty( $s['enable_weekly_digest'] ) ? __( 'On', 'neonews-smart' ) : __( 'Off', 'neonews-smart' ), 'label' => __( 'Weekly digest', 'neonews-smart' ) ),
                    ),
                    'actions'  => function_exists( 'neonews_admin_shell_default_actions' ) ? neonews_admin_shell_default_actions() : array(),
                )
            );
            neonews_admin_shell_panel_open();
        } else {
            echo '<div class="wrap">';
        }
        $days = array(
            0 => __( 'Sunday', 'neonews-smart' ),
            1 => __( 'Monday', 'neonews-smart' ),
            2 => __( 'Tuesday', 'neonews-smart' ),
            3 => __( 'Wednesday', 'neonews-smart' ),
            4 => __( 'Thursday', 'neonews-smart' ),
            5 => __( 'Friday', 'neonews-smart' ),
            6 => __( 'Saturday', 'neonews-smart' ),
        );
        ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'neonews_smart_group' ); ?>

                <h2><?php esc_html_e( 'Editorial (owner)', 'neonews-smart' ); ?></h2>
                <table class="form-table">
                    <tr><td colspan="2"><label><input type="checkbox" name="neonews_smart_settings[enable_auto_excerpt]" value="1" <?php checked( ! empty( $s['enable_auto_excerpt'] ) ); ?> /> <?php esc_html_e( 'Auto-generate excerpt from first sentences when empty', 'neonews-smart' ); ?></label></td></tr>
                    <tr>
                        <th><?php esc_html_e( 'Excerpt sentences', 'neonews-smart' ); ?></th>
                        <td><input type="number" name="neonews_smart_settings[excerpt_max_sentences]" value="<?php echo esc_attr( $s['excerpt_max_sentences'] ); ?>" min="1" max="5" class="small-text" /></td>
                    </tr>
                    <tr><td colspan="2"><label><input type="checkbox" name="neonews_smart_settings[enable_submission_checks]" value="1" <?php checked( ! empty( $s['enable_submission_checks'] ) ); ?> /> <?php esc_html_e( 'Block submissions with banned words or too many links', 'neonews-smart' ); ?></label></td></tr>
                    <tr>
                        <th><?php esc_html_e( 'Max links per submission', 'neonews-smart' ); ?></th>
                        <td><input type="number" name="neonews_smart_settings[submission_max_links]" value="<?php echo esc_attr( $s['submission_max_links'] ); ?>" min="0" class="small-text" /></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Banned words (comma-separated)', 'neonews-smart' ); ?></th>
                        <td><input type="text" name="neonews_smart_settings[submission_banned_words]" value="<?php echo esc_attr( $s['submission_banned_words'] ); ?>" class="large-text" /></td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Reader (frontend)', 'neonews-smart' ); ?></h2>
                <table class="form-table">
                    <tr><td colspan="2"><label><input type="checkbox" name="neonews_smart_settings[enable_story_glance]" value="1" <?php checked( ! empty( $s['enable_story_glance'] ) ); ?> /> <?php esc_html_e( 'Story at a glance box on single posts', 'neonews-smart' ); ?></label></td></tr>
                    <tr><td colspan="2"><label><input type="checkbox" name="neonews_smart_settings[enable_key_points]" value="1" <?php checked( ! empty( $s['enable_key_points'] ) ); ?> /> <?php esc_html_e( 'Key points from headings', 'neonews-smart' ); ?></label></td></tr>
                    <tr><td colspan="2"><label><input type="checkbox" name="neonews_smart_settings[enable_toc]" value="1" <?php checked( ! empty( $s['enable_toc'] ) ); ?> /> <?php esc_html_e( 'Table of contents (3+ headings)', 'neonews-smart' ); ?></label></td></tr>
                    <tr><td colspan="2"><label><input type="checkbox" name="neonews_smart_settings[enable_smart_related]" value="1" <?php checked( ! empty( $s['enable_smart_related'] ) ); ?> /> <?php esc_html_e( 'Smart related posts + trending homepage block', 'neonews-smart' ); ?></label></td></tr>
                    <tr><td colspan="2"><label><input type="checkbox" name="neonews_smart_settings[enable_personalized_home]" value="1" <?php checked( ! empty( $s['enable_personalized_home'] ) ); ?> /> <?php esc_html_e( 'Recommended for you (logged-in, uses activity log)', 'neonews-smart' ); ?></label></td></tr>
                </table>

                <h2><?php esc_html_e( 'Email digest', 'neonews-smart' ); ?></h2>
                <table class="form-table">
                    <tr><td colspan="2"><label><input type="checkbox" name="neonews_smart_settings[enable_weekly_digest]" value="1" <?php checked( ! empty( $s['enable_weekly_digest'] ) ); ?> /> <?php esc_html_e( 'Weekly digest to premium members (or admins if no premium users)', 'neonews-smart' ); ?></label></td></tr>
                    <tr>
                        <th><?php esc_html_e( 'Send day', 'neonews-smart' ); ?></th>
                        <td>
                            <select name="neonews_smart_settings[digest_day]">
                                <?php foreach ( $days as $num => $label ) : ?>
                                    <option value="<?php echo esc_attr( $num ); ?>" <?php selected( (int) $s['digest_day'], $num ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Send hour (site time)', 'neonews-smart' ); ?></th>
                        <td><input type="number" name="neonews_smart_settings[digest_hour]" value="<?php echo esc_attr( $s['digest_hour'] ); ?>" min="0" max="23" class="small-text" /></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td><label><input type="checkbox" name="neonews_smart_settings[reschedule_digest]" value="1" /> <?php esc_html_e( 'Reschedule digest cron after save', 'neonews-smart' ); ?></label></td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        <?php
        if ( function_exists( 'neonews_admin_shell_panel_close' ) ) {
            neonews_admin_shell_panel_close();
            neonews_admin_shell_end();
        } else {
            echo '</div>';
        }
    }
}
