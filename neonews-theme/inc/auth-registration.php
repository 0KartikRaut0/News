<?php
/**
 * Auth modal: registration fields, sign-up, and password reset.
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Built-in registration field definitions.
 *
 * @return array
 */
function neonews_get_builtin_registration_field_defs() {
    return array(
        'first_name' => array(
            'label'    => __( 'First name', 'neonews' ),
            'type'     => 'text',
            'meta_key' => 'first_name',
            'required' => true,
            'enabled'  => true,
        ),
        'last_name'  => array(
            'label'    => __( 'Last name', 'neonews' ),
            'type'     => 'text',
            'meta_key' => 'last_name',
            'required' => true,
            'enabled'  => true,
        ),
        'education'  => array(
            'label'    => __( 'Education', 'neonews' ),
            'type'     => 'text',
            'meta_key' => 'nn_education',
            'required' => false,
            'enabled'  => true,
        ),
        'occupation' => array(
            'label'    => __( 'Occupation', 'neonews' ),
            'type'     => 'text',
            'meta_key' => 'nn_occupation',
            'required' => false,
            'enabled'  => true,
        ),
        'email'      => array(
            'label'    => __( 'Email', 'neonews' ),
            'type'     => 'email',
            'system'   => 'email',
            'required' => true,
            'enabled'  => true,
        ),
        'password'   => array(
            'label'    => __( 'Password', 'neonews' ),
            'type'     => 'password',
            'system'   => 'password',
            'required' => true,
            'enabled'  => true,
        ),
    );
}

/**
 * Is front-end registration enabled?
 *
 * @return bool
 */
function neonews_auth_registration_enabled() {
    return neonews_is_feature_enabled( 'auth_allow_registration' );
}

/**
 * Merge saved config with built-in + custom registration fields.
 *
 * @return array
 */
function neonews_get_registration_fields() {
    $settings = neonews_get_settings();
    $saved    = isset( $settings['reg_fields'] ) && is_array( $settings['reg_fields'] ) ? $settings['reg_fields'] : array();
    $fields   = array();

    foreach ( neonews_get_builtin_registration_field_defs() as $key => $def ) {
        $cfg = isset( $saved[ $key ] ) && is_array( $saved[ $key ] ) ? $saved[ $key ] : array();

        if ( isset( $cfg['enabled'] ) && ! $cfg['enabled'] ) {
            continue;
        }
        if ( ! $def['enabled'] && ! isset( $cfg['enabled'] ) ) {
            continue;
        }

        $fields[] = array_merge(
            $def,
            array(
                'key'      => $key,
                'required' => isset( $cfg['required'] ) ? (bool) $cfg['required'] : $def['required'],
            )
        );
    }

    $custom_raw = isset( $settings['reg_custom_fields'] ) ? (string) $settings['reg_custom_fields'] : '';
    foreach ( preg_split( '/\r\n|\r|\n/', $custom_raw ) as $line ) {
        $line = trim( $line );
        if ( '' === $line || 0 === strpos( $line, '#' ) ) {
            continue;
        }

        $parts = array_map( 'trim', explode( '|', $line ) );
        if ( count( $parts ) < 2 ) {
            continue;
        }

        $key = sanitize_key( $parts[0] );
        if ( '' === $key || in_array( $key, array( 'email', 'password', 'user_login', 'user_email', 'user_pass' ), true ) ) {
            continue;
        }

        $type = in_array( $parts[2] ?? 'text', array( 'text', 'email', 'tel', 'textarea' ), true ) ? $parts[2] : 'text';

        $fields[] = array(
            'key'      => $key,
            'label'    => sanitize_text_field( $parts[1] ),
            'type'     => $type,
            'meta_key' => 'nn_reg_' . $key,
            'required' => ! empty( $parts[3] ) && '1' === $parts[3],
            'custom'   => true,
        );
    }

    return $fields;
}

/**
 * Generate unique username from email local part.
 *
 * @param string $email Email address.
 * @return string
 */
function neonews_generate_username_from_email( $email ) {
    $local = sanitize_user( current( explode( '@', $email ) ), true );
    if ( '' === $local ) {
        $local = 'user';
    }

    $username = $local;
    $suffix   = 1;

    while ( username_exists( $username ) ) {
        $username = $local . $suffix;
        ++$suffix;
    }

    return $username;
}

/**
 * Render dynamic registration inputs.
 */
function neonews_render_registration_form_fields() {
    foreach ( neonews_get_registration_fields() as $field ) {
        $id    = 'nn-register-' . $field['key'];
        $name  = 'reg_' . $field['key'];
        $req   = ! empty( $field['required'] );
        $type  = $field['type'];
        $label = $field['label'];

        echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $label );
        if ( $req ) {
            echo ' <span class="nn-required">*</span>';
        }
        echo '</label>';

        if ( 'textarea' === $type ) {
            printf(
                '<textarea id="%1$s" name="%2$s" class="nn-form-input" rows="3"%3$s></textarea>',
                esc_attr( $id ),
                esc_attr( $name ),
                $req ? ' required' : ''
            );
            continue;
        }

        $extra = '';
        if ( 'password' === $type ) {
            $extra = ' autocomplete="new-password" minlength="8"';
        } elseif ( 'email' === $type ) {
            $extra = ' autocomplete="email"';
        } else {
            $extra = ' autocomplete="off"';
        }

        printf(
            '<input type="%1$s" id="%2$s" name="%3$s" class="nn-form-input"%4$s%5$s />',
            esc_attr( 'password' === $type ? 'password' : ( 'email' === $type ? 'email' : 'text' ) ),
            esc_attr( $id ),
            esc_attr( $name ),
            $req ? ' required' : '',
            $extra
        );
    }
}

/**
 * Auth modal markup.
 */
function neonews_render_auth_modal() {
    if ( is_user_logged_in() ) {
        return;
    }

    $can_register = neonews_auth_registration_enabled();
    ?>
    <div id="nn-auth-modal" class="nn-modal" aria-hidden="true" role="dialog" aria-labelledby="nn-auth-title">
        <div class="nn-modal-backdrop" data-close-modal></div>
        <div class="nn-modal-dialog nn-modal-dialog-auth">
            <button type="button" class="nn-modal-close" data-close-modal aria-label="<?php esc_attr_e( 'Close', 'neonews' ); ?>">×</button>

            <?php if ( $can_register ) : ?>
            <div class="nn-auth-tabs" role="tablist">
                <button type="button" class="nn-auth-tab is-active" data-auth-tab="login" role="tab" aria-selected="true"><?php esc_html_e( 'Sign In', 'neonews' ); ?></button>
                <button type="button" class="nn-auth-tab" data-auth-tab="register" role="tab" aria-selected="false"><?php esc_html_e( 'Sign Up', 'neonews' ); ?></button>
            </div>
            <?php endif; ?>

            <div id="nn-auth-panel-login" class="nn-auth-panel is-active" data-auth-panel="login">
                <h2 id="nn-auth-title"><?php esc_html_e( 'Welcome back', 'neonews' ); ?></h2>
                <p><?php esc_html_e( 'Sign in to track your reading, likes, and comments.', 'neonews' ); ?></p>
                <form id="nn-login-form" class="nn-auth-form">
                    <label for="nn-login-user"><?php esc_html_e( 'Email or username', 'neonews' ); ?></label>
                    <input type="text" id="nn-login-user" name="log" class="nn-form-input" required autocomplete="username" />
                    <label for="nn-login-pass"><?php esc_html_e( 'Password', 'neonews' ); ?></label>
                    <input type="password" id="nn-login-pass" name="pwd" class="nn-form-input" required autocomplete="current-password" />
                    <label class="nn-checkbox-label"><input type="checkbox" name="rememberme" value="forever" /> <?php esc_html_e( 'Remember me', 'neonews' ); ?></label>
                    <?php
                    if ( class_exists( 'NeoNews_Captcha' ) ) {
                        NeoNews_Captcha::render_widget( 'login' );
                    }
                    ?>
                    <button type="submit" class="nn-btn nn-btn-full"><?php esc_html_e( 'Sign In', 'neonews' ); ?></button>
                    <p class="nn-auth-error" hidden></p>
                </form>
                <p class="nn-auth-footer">
                    <button type="button" class="nn-auth-link-btn" data-auth-tab="forgot"><?php esc_html_e( 'Forgot password?', 'neonews' ); ?></button>
                </p>
            </div>

            <div id="nn-auth-panel-forgot" class="nn-auth-panel" data-auth-panel="forgot" hidden>
                <h2><?php esc_html_e( 'Reset your password', 'neonews' ); ?></h2>
                <p><?php esc_html_e( 'Enter the email address on your account. We will send a link to reset your password.', 'neonews' ); ?></p>
                <form id="nn-forgot-form" class="nn-auth-form">
                    <label for="nn-forgot-email"><?php esc_html_e( 'Email address', 'neonews' ); ?></label>
                    <input type="email" id="nn-forgot-email" name="user_email" class="nn-form-input" required autocomplete="email" />
                    <button type="submit" class="nn-btn nn-btn-full"><?php esc_html_e( 'Send reset link', 'neonews' ); ?></button>
                    <p class="nn-auth-success" hidden></p>
                    <p class="nn-auth-error" hidden></p>
                </form>
                <p class="nn-auth-footer">
                    <button type="button" class="nn-auth-link-btn" data-auth-tab="login"><?php esc_html_e( 'Back to sign in', 'neonews' ); ?></button>
                    <?php if ( $can_register ) : ?>
                        <?php esc_html_e( ' · ', 'neonews' ); ?>
                        <button type="button" class="nn-auth-link-btn" data-auth-tab="register"><?php esc_html_e( 'Create an account', 'neonews' ); ?></button>
                    <?php endif; ?>
                </p>
            </div>

            <?php if ( $can_register ) : ?>
            <div id="nn-auth-panel-register" class="nn-auth-panel" data-auth-panel="register" hidden>
                <h2><?php esc_html_e( 'Create your account', 'neonews' ); ?></h2>
                <p><?php esc_html_e( 'Join to save likes, follow stories, and submit news.', 'neonews' ); ?></p>
                <form id="nn-register-form" class="nn-auth-form nn-auth-form-register">
                    <?php neonews_render_registration_form_fields(); ?>
                    <?php
                    if ( class_exists( 'NeoNews_Captcha' ) ) {
                        NeoNews_Captcha::render_widget( 'register' );
                    }
                    ?>
                    <button type="submit" class="nn-btn nn-btn-full"><?php esc_html_e( 'Create Account', 'neonews' ); ?></button>
                    <p class="nn-auth-error" hidden></p>
                </form>
                <p class="nn-auth-footer">
                    <?php esc_html_e( 'Already have an account?', 'neonews' ); ?>
                    <button type="button" class="nn-auth-link-btn" data-auth-tab="login"><?php esc_html_e( 'Sign in', 'neonews' ); ?></button>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * AJAX login handler.
 */
function neonews_ajax_login() {
    check_ajax_referer( 'neonews_nonce', 'nonce' );

    if ( function_exists( 'neonews_verify_captcha' ) ) {
        $captcha = neonews_verify_captcha( 'login' );
        if ( is_wp_error( $captcha ) ) {
            wp_send_json_error( array( 'message' => $captcha->get_error_message() ) );
        }
    }

    $username = isset( $_POST['log'] ) ? sanitize_text_field( wp_unslash( $_POST['log'] ) ) : '';
    $password = isset( $_POST['pwd'] ) ? wp_unslash( $_POST['pwd'] ) : ''; // phpcs:ignore
    $remember = ! empty( $_POST['rememberme'] );

    $user = wp_signon(
        array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember,
        ),
        is_ssl()
    );

    if ( is_wp_error( $user ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid username or password.', 'neonews' ) ) );
    }

    wp_send_json_success(
        array(
            'redirect' => neonews_get_account_url(),
            'message'  => __( 'Signed in successfully!', 'neonews' ),
        )
    );
}
add_action( 'wp_ajax_nopriv_neonews_ajax_login', 'neonews_ajax_login' );
add_action( 'wp_ajax_neonews_ajax_login', 'neonews_ajax_login' );

/**
 * AJAX registration handler.
 */
function neonews_ajax_register() {
    check_ajax_referer( 'neonews_nonce', 'nonce' );

    if ( ! neonews_auth_registration_enabled() ) {
        wp_send_json_error( array( 'message' => __( 'Registration is currently disabled.', 'neonews' ) ) );
    }

    if ( function_exists( 'neonews_verify_captcha' ) ) {
        $captcha = neonews_verify_captcha( 'register' );
        if ( is_wp_error( $captcha ) ) {
            wp_send_json_error( array( 'message' => $captcha->get_error_message() ) );
        }
    }

    $email    = '';
    $password = '';
    $meta     = array();

    foreach ( neonews_get_registration_fields() as $field ) {
        $post_key = 'reg_' . $field['key'];
        $raw      = isset( $_POST[ $post_key ] ) ? wp_unslash( $_POST[ $post_key ] ) : '';
        $value    = 'textarea' === $field['type'] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );

        if ( ! empty( $field['required'] ) && '' === $value ) {
            /* translators: %s: field label */
            wp_send_json_error( array( 'message' => sprintf( __( 'Please fill in %s.', 'neonews' ), $field['label'] ) ) );
        }

        if ( isset( $field['system'] ) && 'email' === $field['system'] ) {
            $email = sanitize_email( $value );
            if ( ! is_email( $email ) ) {
                wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'neonews' ) ) );
            }
            continue;
        }

        if ( isset( $field['system'] ) && 'password' === $field['system'] ) {
            $password = $raw;
            if ( strlen( $password ) < 8 ) {
                wp_send_json_error( array( 'message' => __( 'Password must be at least 8 characters.', 'neonews' ) ) );
            }
            continue;
        }

        if ( '' !== $value && ! empty( $field['meta_key'] ) ) {
            $meta[ $field['meta_key'] ] = $value;
        }
    }

    if ( empty( $email ) || empty( $password ) ) {
        wp_send_json_error( array( 'message' => __( 'Email and password are required.', 'neonews' ) ) );
    }

    if ( email_exists( $email ) ) {
        wp_send_json_error( array( 'message' => __( 'That email is already registered. Try signing in or reset your password.', 'neonews' ) ) );
    }

    $username = neonews_generate_username_from_email( $email );
    if ( ! neonews_is_feature_enabled( 'auth_auto_username' ) && ! empty( $_POST['user_login'] ) ) {
        $username = sanitize_user( wp_unslash( $_POST['user_login'] ), true );
    }

    if ( username_exists( $username ) ) {
        wp_send_json_error( array( 'message' => __( 'Could not create account. Please try a different email.', 'neonews' ) ) );
    }

    $user_id = wp_create_user( $username, $password, $email );

    if ( is_wp_error( $user_id ) ) {
        wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
    }

    $first = $meta['first_name'] ?? '';
    $last  = $meta['last_name'] ?? '';
    $display = trim( $first . ' ' . $last );
    if ( '' !== $display ) {
        wp_update_user(
            array(
                'ID'           => $user_id,
                'display_name' => $display,
                'first_name'   => $first,
                'last_name'    => $last,
            )
        );
    }

    foreach ( $meta as $meta_key => $meta_value ) {
        if ( in_array( $meta_key, array( 'first_name', 'last_name' ), true ) ) {
            continue;
        }
        update_user_meta( $user_id, $meta_key, $meta_value );
    }

    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id, true );

    wp_send_json_success(
        array(
            'redirect' => neonews_get_account_url(),
            'message'  => __( 'Account created successfully!', 'neonews' ),
        )
    );
}
add_action( 'wp_ajax_nopriv_neonews_ajax_register', 'neonews_ajax_register' );

/**
 * AJAX lost password handler.
 */
function neonews_ajax_lost_password() {
    check_ajax_referer( 'neonews_nonce', 'nonce' );

    $email = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';

    if ( ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'neonews' ) ) );
    }

    $user = get_user_by( 'email', $email );

    if ( ! $user ) {
        wp_send_json_error(
            array(
                'message' => __( 'No account found with that email. Please sign up to create one.', 'neonews' ),
                'code'    => 'not_found',
            )
        );
    }

    $result = retrieve_password( $user->user_login );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( array( 'message' => $result->get_error_message() ) );
    }

    wp_send_json_success(
        array(
            'message' => __( 'Password reset email sent. Check your inbox and follow the link to choose a new password.', 'neonews' ),
        )
    );
}
add_action( 'wp_ajax_nopriv_neonews_ajax_lost_password', 'neonews_ajax_lost_password' );

/**
 * Keep WordPress membership setting aligned with NewsPulse registration toggle.
 */
function neonews_sync_wp_registration_setting() {
    $settings = neonews_get_settings();
    $allow    = ! empty( $settings['auth_allow_registration'] ) ? 1 : 0;
    if ( (int) get_option( 'users_can_register' ) !== $allow ) {
        update_option( 'users_can_register', $allow );
    }
}
add_action( 'init', 'neonews_sync_wp_registration_setting', 20 );

/**
 * Admin UI for registration settings.
 *
 * @param array $settings Current settings.
 */
function neonews_admin_render_registration_settings( $settings ) {
    $defs = neonews_get_builtin_registration_field_defs();
    $saved = isset( $settings['reg_fields'] ) && is_array( $settings['reg_fields'] ) ? $settings['reg_fields'] : array();
    ?>
    <h2><?php esc_html_e( 'Login & sign up', 'neonews' ); ?></h2>
    <p class="description"><?php esc_html_e( 'Controls the sign-in modal, registration form fields, and password reset by email.', 'neonews' ); ?></p>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Allow registration', 'neonews' ); ?></th>
            <td>
                <label><input type="checkbox" name="neonews_platform_settings[auth_allow_registration]" value="1" <?php checked( ! empty( $settings['auth_allow_registration'] ) ); ?> /> <?php esc_html_e( 'Show Sign Up tab and registration buttons', 'neonews' ); ?></label>
                <p class="description"><?php esc_html_e( 'Also updates WordPress Settings → General → Membership.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Username', 'neonews' ); ?></th>
            <td>
                <label><input type="checkbox" name="neonews_platform_settings[auth_auto_username]" value="1" <?php checked( ! empty( $settings['auth_auto_username'] ) ); ?> /> <?php esc_html_e( 'Auto-generate username from email (recommended)', 'neonews' ); ?></label>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Sign-up fields', 'neonews' ); ?></th>
            <td>
                <table class="widefat striped" style="max-width:640px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Field', 'neonews' ); ?></th>
                            <th><?php esc_html_e( 'Show', 'neonews' ); ?></th>
                            <th><?php esc_html_e( 'Required', 'neonews' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $defs as $key => $def ) : ?>
                            <?php
                            $cfg = isset( $saved[ $key ] ) && is_array( $saved[ $key ] ) ? $saved[ $key ] : array();
                            $enabled  = ! isset( $cfg['enabled'] ) || ! empty( $cfg['enabled'] );
                            $required = isset( $cfg['required'] ) ? ! empty( $cfg['required'] ) : ! empty( $def['required'] );
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html( $def['label'] ); ?></strong><br /><code><?php echo esc_html( $key ); ?></code></td>
                                <td><input type="checkbox" name="neonews_platform_settings[reg_fields][<?php echo esc_attr( $key ); ?>][enabled]" value="1" <?php checked( $enabled ); ?> /></td>
                                <td><input type="checkbox" name="neonews_platform_settings[reg_fields][<?php echo esc_attr( $key ); ?>][required]" value="1" <?php checked( $required ); ?> /></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Additional fields', 'neonews' ); ?></th>
            <td>
                <textarea name="neonews_platform_settings[reg_custom_fields]" rows="6" class="large-text code" placeholder="phone|Phone number|tel|1&#10;city|City|text|0"><?php echo esc_textarea( $settings['reg_custom_fields'] ?? '' ); ?></textarea>
                <p class="description">
                    <?php esc_html_e( 'One field per line:', 'neonews' ); ?>
                    <code>meta_key|Label|type|required</code>.
                    <?php esc_html_e( 'Types: text, email, tel, textarea. Required: 1 or 0. Saved to user profile as nn_reg_meta_key.', 'neonews' ); ?>
                </p>
            </td>
        </tr>
    </table>
    <?php
}
