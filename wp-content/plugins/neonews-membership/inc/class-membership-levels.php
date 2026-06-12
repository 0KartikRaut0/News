<?php
/**
 * Membership Levels Handler
 *
 * @package NeoNews_Membership
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Membership Levels Class
 */
class NeoNews_Membership_Levels {

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
        add_action( 'show_user_profile', array( $this, 'add_membership_fields' ) );
        add_action( 'edit_user_profile', array( $this, 'add_membership_fields' ) );
        add_action( 'personal_options_update', array( $this, 'save_membership_fields' ) );
        add_action( 'edit_user_profile_update', array( $this, 'save_membership_fields' ) );
    }

    /**
     * Activation - Add premium subscriber role
     */
    public static function activate() {
        $roles_added = get_option( 'neonews_membership_roles_added' );
        
        if ( $roles_added ) {
            return;
        }

        add_role(
            'nn_premium_subscriber',
            __( 'Premium Subscriber', 'neonews-membership' ),
            array(
                'read' => true,
            )
        );

        update_option( 'neonews_membership_roles_added', true );
    }

    /**
     * Check if user is premium
     */
    public static function is_premium( $user_id = null ) {
        if ( ! $user_id ) {
            if ( ! is_user_logged_in() ) {
                return false;
            }
            $user_id = get_current_user_id();
        }

        if ( user_can( $user_id, 'manage_options' ) ) {
            return true;
        }

        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return false;
        }

        if ( in_array( 'nn_premium_subscriber', (array) $user->roles, true ) ) {
            $expiry = self::get_premium_expiry( $user_id );
            if ( $expiry ) {
                $expiry_time = strtotime( $expiry );
                if ( $expiry_time && $expiry_time < time() ) {
                    self::remove_premium_status( $user_id );
                    return false;
                }
            }
            return true;
        }

        $is_premium_meta = get_user_meta( $user_id, '_neonews_is_premium', true );
        if ( '1' === $is_premium_meta ) {
            $expiry = self::get_premium_expiry( $user_id );
            if ( $expiry ) {
                $expiry_time = strtotime( $expiry );
                if ( $expiry_time && $expiry_time < time() ) {
                    self::remove_premium_status( $user_id );
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Get premium expiry date
     */
    public static function get_premium_expiry( $user_id = null ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        return get_user_meta( $user_id, '_neonews_premium_expiry', true );
    }

    /**
     * Days until premium expiry (null = lifetime or not premium).
     *
     * @param int|null $user_id User ID.
     * @return int|null
     */
    public static function get_days_until_expiry( $user_id = null ) {
        if ( ! self::is_premium( $user_id ) ) {
            return null;
        }

        $expiry = self::get_premium_expiry( $user_id );
        if ( ! $expiry ) {
            return null;
        }

        $expiry_time = strtotime( $expiry );
        if ( ! $expiry_time ) {
            return null;
        }

        $diff = (int) ceil( ( $expiry_time - time() ) / DAY_IN_SECONDS );
        return max( 0, $diff );
    }

    /**
     * Extend premium access by number of days.
     *
     * @param int $user_id User ID.
     * @param int $days    Days to add.
     * @return bool
     */
    public static function extend_premium( $user_id, $days ) {
        $days = max( 1, absint( $days ) );
        $base = time();

        $current_expiry = self::get_premium_expiry( $user_id );
        if ( $current_expiry && strtotime( $current_expiry ) > $base ) {
            $base = strtotime( $current_expiry );
        }

        $new_expiry = gmdate( 'Y-m-d', $base + ( $days * DAY_IN_SECONDS ) );
        return self::set_premium_status( $user_id, $new_expiry );
    }

    /**
     * Set user as premium
     */
    public static function set_premium_status( $user_id, $expiry_date = null ) {
        if ( ! $user_id ) {
            return false;
        }

        update_user_meta( $user_id, '_neonews_is_premium', '1' );
        update_user_meta( $user_id, '_neonews_premium_start', current_time( 'mysql' ) );

        if ( $expiry_date ) {
            update_user_meta( $user_id, '_neonews_premium_expiry', sanitize_text_field( $expiry_date ) );
        } else {
            delete_user_meta( $user_id, '_neonews_premium_expiry' );
        }

        $user = get_userdata( $user_id );
        if ( $user && ! in_array( 'nn_premium_subscriber', (array) $user->roles, true ) ) {
            $user->add_role( 'nn_premium_subscriber' );
        }

        do_action( 'neonews_user_became_premium', $user_id );

        return true;
    }

    /**
     * Remove premium status
     */
    public static function remove_premium_status( $user_id ) {
        if ( ! $user_id ) {
            return false;
        }

        update_user_meta( $user_id, '_neonews_is_premium', '0' );
        delete_user_meta( $user_id, '_neonews_premium_expiry' );

        $user = get_userdata( $user_id );
        if ( $user ) {
            $user->remove_role( 'nn_premium_subscriber' );
        }

        do_action( 'neonews_user_lost_premium', $user_id );

        return true;
    }

    /**
     * Add membership fields to user profile
     */
    public function add_membership_fields( $user ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $is_premium = self::is_premium( $user->ID );
        $expiry = self::get_premium_expiry( $user->ID );
        $start_date = get_user_meta( $user->ID, '_neonews_premium_start', true );
        ?>
        <h3><?php esc_html_e( 'NeoNews Membership', 'neonews-membership' ); ?></h3>
        
        <table class="form-table">
            <tr>
                <th><label for="neonews_is_premium"><?php esc_html_e( 'Premium Status', 'neonews-membership' ); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" name="neonews_is_premium" id="neonews_is_premium" value="1" <?php checked( $is_premium ); ?>>
                        <?php esc_html_e( 'This user is a premium member', 'neonews-membership' ); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th><label for="neonews_premium_expiry"><?php esc_html_e( 'Expiry Date', 'neonews-membership' ); ?></label></th>
                <td>
                    <input type="date" name="neonews_premium_expiry" id="neonews_premium_expiry" value="<?php echo esc_attr( $expiry ? date( 'Y-m-d', strtotime( $expiry ) ) : '' ); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e( 'Leave empty for lifetime access.', 'neonews-membership' ); ?></p>
                </td>
            </tr>
            <?php if ( $start_date ) : ?>
            <tr>
                <th><?php esc_html_e( 'Member Since', 'neonews-membership' ); ?></th>
                <td>
                    <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $start_date ) ) ); ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php if ( $is_premium ) : ?>
            <tr>
                <th><?php esc_html_e( 'Revoke access', 'neonews-membership' ); ?></th>
                <td>
                    <button type="submit" name="neonews_revoke_premium" value="1" class="button button-secondary" onclick="return confirm('<?php echo esc_js( __( 'Revoke premium access for this user immediately?', 'neonews-membership' ) ); ?>');">
                        <?php esc_html_e( 'Revoke Premium Access', 'neonews-membership' ); ?>
                    </button>
                    <p class="description"><?php esc_html_e( 'Removes premium role, badge, and exclusive content access.', 'neonews-membership' ); ?></p>
                </td>
            </tr>
            <?php endif; ?>
        </table>
        <?php
        wp_nonce_field( 'neonews_save_membership', 'neonews_membership_nonce' );
    }

    /**
     * Save membership fields
     */
    public function save_membership_fields( $user_id ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( ! isset( $_POST['neonews_membership_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['neonews_membership_nonce'] ) ), 'neonews_save_membership' ) ) {
            return;
        }

        if ( isset( $_POST['neonews_revoke_premium'] ) ) {
            self::remove_premium_status( $user_id );
            return;
        }

        $is_premium = isset( $_POST['neonews_is_premium'] );
        $expiry = isset( $_POST['neonews_premium_expiry'] ) ? sanitize_text_field( wp_unslash( $_POST['neonews_premium_expiry'] ) ) : '';

        if ( $is_premium ) {
            self::set_premium_status( $user_id, $expiry ? $expiry : null );
        } else {
            self::remove_premium_status( $user_id );
        }
    }

    /**
     * Get all premium users
     */
    public static function get_premium_users( $args = array() ) {
        $defaults = array(
            'meta_key'     => '_neonews_is_premium',
            'meta_value'   => '1',
            'meta_compare' => '=',
        );

        $args = wp_parse_args( $args, $defaults );

        return get_users( $args );
    }

    /**
     * Get premium users count
     */
    public static function get_premium_count() {
        $users = self::get_premium_users( array( 'count_total' => true ) );
        return count( $users );
    }
}
