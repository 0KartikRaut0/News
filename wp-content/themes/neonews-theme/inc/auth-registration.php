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
    // ORDER MATTERS: email → username → password always appear first (top of form),
    // then optional profile fields below the password field.
    return array(
        'email'      => array(
            'label'    => __( 'Email', 'neonews' ),
            'type'     => 'email',
            'system'   => 'email',
            'required' => true,
            'enabled'  => true,
        ),
        'username'   => array(
            'label'    => __( 'Username', 'neonews' ),
            'type'     => 'text',
            'system'   => 'username',
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
        'first_name' => array(
            'label'    => __( 'First name', 'neonews' ),
            'type'     => 'text',
            'meta_key' => 'first_name',
            'required' => false,
            'enabled'  => true,
        ),
        'last_name'  => array(
            'label'    => __( 'Last name', 'neonews' ),
            'type'     => 'text',
            'meta_key' => 'last_name',
            'required' => false,
            'enabled'  => true,
        ),
        'education'  => array(
            'label'    => __( 'Education', 'neonews' ),
            'type'     => 'text',
            'meta_key' => 'nn_education',
            'required' => false,
            'enabled'  => false, // hidden by default; admin can enable
        ),
        'occupation' => array(
            'label'    => __( 'Occupation', 'neonews' ),
            'type'     => 'text',
            'meta_key' => 'nn_occupation',
            'required' => false,
            'enabled'  => false, // hidden by default; admin can enable
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
    $defs     = neonews_get_builtin_registration_field_defs();

    foreach ( $defs as $key => $def ) {
        $cfg       = isset( $saved[ $key ] ) && is_array( $saved[ $key ] ) ? $saved[ $key ] : array();
        $is_system = ! empty( $def['system'] );

        // Username: controlled by its own "enabled" saved setting.
        // If the admin has never saved a setting, fall back to whether auto-username is OFF.
        if ( 'username' === $key ) {
            $username_enabled = isset( $cfg['enabled'] )
                ? (bool) $cfg['enabled']
                : ! neonews_is_feature_enabled( 'auth_auto_username' );
            if ( ! $username_enabled ) {
                continue;
            }
        } elseif ( $is_system ) {
            // email & password: always show, cannot be disabled.
        } else {
            // Non-system optional fields: respect the "enabled" checkbox.
            $enabled = isset( $cfg['enabled'] ) ? (bool) $cfg['enabled'] : (bool) $def['enabled'];
            if ( ! $enabled ) {
                continue;
            }
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
        } elseif ( isset( $field['system'] ) && 'username' === $field['system'] ) {
            $extra = ' autocomplete="username" pattern="[a-zA-Z0-9_\-\.]{3,60}" title="' . esc_attr__( '3–60 characters: letters, numbers, _ - .', 'neonews' ) . '"';
            $name  = 'user_login'; // matches what the AJAX register handler reads.
        } else {
            $extra = ' autocomplete="off"';
        }

        $input_html = sprintf(
            '<input type="%1$s" id="%2$s" name="%3$s" class="nn-form-input"%4$s%5$s />',
            esc_attr( 'password' === $type ? 'password' : ( 'email' === $type ? 'email' : 'text' ) ),
            esc_attr( $id ),
            esc_attr( $name ),
            $req ? ' required' : '',
            $extra
        );

        if ( 'password' === $type ) {
            // Wrap with show/hide toggle.
            printf(
                '<div class="nn-pass-wrap">%s<button type="button" class="nn-pass-toggle" aria-label="%s" data-target="%s"><span class="nn-pass-icon nn-pass-show">&#128065;</span><span class="nn-pass-icon nn-pass-hide" hidden>&#128584;</span></button></div>',
                $input_html,
                esc_attr__( 'Show password', 'neonews' ),
                esc_attr( $id )
            );
        } elseif ( isset( $field['system'] ) && 'username' === $field['system'] ) {
            // Wrap username with availability feedback.
            echo $input_html;
            echo '<span class="nn-username-feedback" id="nn-username-feedback" aria-live="polite"></span>';
        } else {
            echo $input_html;
        }
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
                    <div class="nn-pass-wrap">
                        <input type="password" id="nn-login-pass" name="pwd" class="nn-form-input" required autocomplete="current-password" />
                        <button type="button" class="nn-pass-toggle" aria-label="<?php esc_attr_e( 'Show password', 'neonews' ); ?>" data-target="nn-login-pass">
                            <span class="nn-pass-icon nn-pass-show">👁</span>
                            <span class="nn-pass-icon nn-pass-hide" hidden>🙈</span>
                        </button>
                    </div>
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
    // Suppress any stray output/warnings that would corrupt the JSON response.
    ob_start();

    // Use soft nonce check — if expired return a fresh nonce so JS can retry.
    $nonce_result = check_ajax_referer( 'neonews_nonce', 'nonce', false );
    // Helper: clean buffer then send JSON error.
    $json_error = function( $data ) {
        ob_end_clean();
        wp_send_json_error( $data );
    };

    if ( false === $nonce_result ) {
        $json_error( array(
            'message'     => __( 'Session expired. Please refresh the page and try again.', 'neonews' ),
            'code'        => 'nonce_fail',
            'fresh_nonce' => wp_create_nonce( 'neonews_nonce' ),
        ) );
    }

    $username = isset( $_POST['log'] ) ? sanitize_text_field( wp_unslash( $_POST['log'] ) ) : '';
    $password = isset( $_POST['pwd'] ) ? wp_unslash( $_POST['pwd'] ) : '';
    $remember = ! empty( $_POST['rememberme'] );

    if ( '' === $username || '' === $password ) {
        $json_error( array( 'message' => __( 'Please enter your username and password.', 'neonews' ) ) );
    }

    // Verify credentials — independent of cookies.
    $user = wp_authenticate( $username, $password );

    if ( is_wp_error( $user ) ) {
        $code = $user->get_error_code();
        if ( in_array( $code, array( 'invalid_username', 'invalid_email' ), true ) ) {
            $msg = __( 'No account found with that username or email.', 'neonews' );
        } elseif ( 'incorrect_password' === $code ) {
            $msg = __( 'Incorrect password. Please try again.', 'neonews' );
        } else {
            $msg = wp_strip_all_tags( $user->get_error_message() );
        }
        $json_error( array( 'message' => $msg ) );
    }

    // Credentials correct — set auth cookie.
    // Force $secure = false on local/dev (http:// only, no SSL).
    $is_local = in_array( wp_get_environment_type(), array( 'local', 'development' ), true );
    $secure   = $is_local ? false : is_ssl();

    wp_set_current_user( $user->ID );
    wp_set_auth_cookie( $user->ID, $remember, $secure );
    do_action( 'wp_login', $user->user_login, $user );

    ob_end_clean();
    wp_send_json_success( array(
        'redirect' => neonews_get_account_url(),
        'message'  => __( 'Signed in successfully!', 'neonews' ),
    ) );
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
        // Username uses name="user_login" — handled separately below; skip here.
        if ( isset( $field['system'] ) && 'username' === $field['system'] ) {
            continue;
        }

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

    // Determine if the username field was shown on the form (user entered their own).
    $username_field_shown = false;
    foreach ( neonews_get_registration_fields() as $_f ) {
        if ( isset( $_f['system'] ) && 'username' === $_f['system'] ) {
            $username_field_shown = true;
            break;
        }
    }

    if ( $username_field_shown ) {
        // User picked their own username.
        if ( empty( $_POST['user_login'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Please choose a username.', 'neonews' ) ) );
        }
        $username = sanitize_user( wp_unslash( $_POST['user_login'] ), true );
        if ( strlen( $username ) < 3 ) {
            wp_send_json_error( array( 'message' => __( 'Username must be at least 3 characters.', 'neonews' ) ) );
        }
        if ( username_exists( $username ) ) {
            wp_send_json_error( array( 'message' => __( 'That username is already taken. Please choose another.', 'neonews' ) ) );
        }
    } else {
        // Auto-generate a unique username from the email address.
        $username = neonews_generate_username_from_email( $email );
        // neonews_generate_username_from_email already guarantees uniqueness via while-loop.
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
 * AJAX: real-time username availability check.
 */
function neonews_ajax_check_username() {
    check_ajax_referer( 'neonews_nonce', 'nonce' );

    $username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ), true ) : '';

    if ( strlen( $username ) < 3 ) {
        wp_send_json_error( array( 'message' => __( 'Username must be at least 3 characters.', 'neonews' ) ) );
    }
    if ( ! validate_username( $username ) ) {
        wp_send_json_error( array( 'message' => __( 'Username can only contain letters, numbers, spaces, _, -, and .', 'neonews' ) ) );
    }
    if ( username_exists( $username ) ) {
        wp_send_json_error( array( 'message' => __( 'That username is already taken.', 'neonews' ) ) );
    }

    wp_send_json_success( array( 'message' => __( 'Username is available!', 'neonews' ) ) );
}
add_action( 'wp_ajax_nopriv_neonews_ajax_check_username', 'neonews_ajax_check_username' );
add_action( 'wp_ajax_neonews_ajax_check_username',        'neonews_ajax_check_username' );

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
            <th><?php esc_html_e( 'Sign-up fields', 'neonews' ); ?></th>
            <td>
                <table class="widefat striped" style="max-width:680px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Field', 'neonews' ); ?></th>
                            <th><?php esc_html_e( 'Show on sign-up', 'neonews' ); ?></th>
                            <th><?php esc_html_e( 'Required', 'neonews' ); ?></th>
                            <th><?php esc_html_e( 'Notes', 'neonews' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $defs as $key => $def ) : ?>
                            <?php
                            $cfg      = isset( $saved[ $key ] ) && is_array( $saved[ $key ] ) ? $saved[ $key ] : array();
                            $required = isset( $cfg['required'] ) ? ! empty( $cfg['required'] ) : ! empty( $def['required'] );

                            // email & password are mandatory system fields — always shown.
                            $is_core = in_array( $key, array( 'email', 'password' ), true );

                            // username: enabled = Show checkbox; default = auto_username OFF.
                            if ( 'username' === $key ) {
                                $enabled = isset( $cfg['enabled'] )
                                    ? ! empty( $cfg['enabled'] )
                                    : ! neonews_is_feature_enabled( 'auth_auto_username' );
                            } else {
                                $enabled = ! isset( $cfg['enabled'] ) || ! empty( $cfg['enabled'] );
                            }
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html( $def['label'] ); ?></strong><br /><code><?php echo esc_html( $key ); ?></code></td>
                                <td>
                                    <?php if ( $is_core ) : ?>
                                        <em style="color:#888"><?php esc_html_e( 'Always shown', 'neonews' ); ?></em>
                                        <input type="hidden" name="neonews_platform_settings[reg_fields][<?php echo esc_attr( $key ); ?>][enabled]" value="1" />
                                    <?php else : ?>
                                        <input type="checkbox"
                                            name="neonews_platform_settings[reg_fields][<?php echo esc_attr( $key ); ?>][enabled]"
                                            value="1"
                                            <?php checked( $enabled ); ?>
                                            <?php if ( 'username' === $key ) : ?>id="nn-admin-username-show"<?php endif; ?>
                                        />
                                        <?php if ( 'username' === $key ) : ?>
                                            <span style="color:#555;font-size:12px;margin-left:4px;"><?php esc_html_e( '(uncheck to auto-generate username from email)', 'neonews' ); ?></span>
                                            <?php // Mirror to auth_auto_username: checked Show = auto OFF, unchecked Show = auto ON. ?>
                                            <input type="hidden" id="nn-admin-auto-username-mirror"
                                                name="neonews_platform_settings[auth_auto_username]" value="<?php echo $enabled ? '0' : '1'; ?>" />
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( $is_core || 'username' === $key ) : ?>
                                        <em style="color:#888"><?php esc_html_e( 'Always required', 'neonews' ); ?></em>
                                        <input type="hidden" name="neonews_platform_settings[reg_fields][<?php echo esc_attr( $key ); ?>][required]" value="1" />
                                    <?php else : ?>
                                        <input type="checkbox" name="neonews_platform_settings[reg_fields][<?php echo esc_attr( $key ); ?>][required]" value="1" <?php checked( $required ); ?> />
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:12px;color:#888;">
                                    <?php if ( $is_core ) : ?>
                                        <?php esc_html_e( 'Core — cannot be hidden', 'neonews' ); ?>
                                    <?php elseif ( 'username' === $key ) : ?>
                                        <?php esc_html_e( 'User logs in with email or username', 'neonews' ); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="description" style="margin-top:8px;">
                    <?php esc_html_e( 'Email and Password are always required. Username can be shown (user picks their own) or hidden (auto-generated from email).', 'neonews' ); ?>
                </p>
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
    <script>
    (function(){
        var showCb  = document.getElementById('nn-admin-username-show');
        var mirror  = document.getElementById('nn-admin-auto-username-mirror');
        if ( ! showCb || ! mirror ) return;
        showCb.addEventListener('change', function(){
            // Show checked  → user picks username → auto_username = 0 (OFF)
            // Show unchecked → auto-generate     → auto_username = 1 (ON)
            mirror.value = showCb.checked ? '0' : '1';
        });
    })();
    </script>
    <?php
}
