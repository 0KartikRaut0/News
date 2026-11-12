<?php
/**
 * Email sending — confirmation & welcome emails via wp_mail().
 *
 * @package NeoNews_Newsletter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NeoNews_Newsletter_Mailer {

    /**
     * Send double opt-in confirmation email.
     *
     * @param string $email      Subscriber email.
     * @param string $first_name First name (optional).
     * @param string $token      Confirmation token.
     * @return bool
     */
    public static function send_confirmation( $email, $first_name, $token ) {
        $site_name    = get_bloginfo( 'name' );
        $confirm_url  = add_query_arg(
            array(
                'nn_confirm' => rawurlencode( $token ),
            ),
            home_url( '/' )
        );
        $unsub_url = add_query_arg(
            array(
                'nn_unsub' => rawurlencode( $token ),
            ),
            home_url( '/' )
        );

        $greeting = $first_name ? 'Hi ' . $first_name . ',' : 'Hi there,';

        $subject = sprintf( __( 'Confirm your subscription to %s', 'neonews-newsletter' ), $site_name );

        $message = self::wrap_email(
            $subject,
            "
            <p>{$greeting}</p>
            <p>Thanks for subscribing to <strong>{$site_name}</strong>! Click the button below to confirm your email address and start receiving our daily briefing.</p>
            <p style='text-align:center;margin:30px 0;'>
                <a href='" . esc_url( $confirm_url ) . "' style='background:#e63946;color:#fff;text-decoration:none;padding:14px 32px;border-radius:6px;font-weight:600;display:inline-block;'>
                    Confirm my subscription
                </a>
            </p>
            <p style='font-size:13px;color:#888;'>If the button doesn't work, copy and paste this link:<br>
                <a href='" . esc_url( $confirm_url ) . "' style='color:#e63946;word-break:break-all;'>" . esc_html( $confirm_url ) . "</a>
            </p>
            <p style='font-size:12px;color:#aaa;margin-top:30px;'>
                If you didn't subscribe, you can <a href='" . esc_url( $unsub_url ) . "' style='color:#aaa;'>ignore this email</a>.
            </p>
            "
        );

        return self::send( $email, $subject, $message );
    }

    /**
     * Send welcome email after confirmation.
     *
     * @param string $email      Subscriber email.
     * @param string $first_name First name.
     * @param string $token      Token for unsubscribe link.
     * @return bool
     */
    public static function send_welcome( $email, $first_name, $token ) {
        $site_name  = get_bloginfo( 'name' );
        $site_url   = home_url( '/' );
        $unsub_url  = add_query_arg( array( 'nn_unsub' => rawurlencode( $token ) ), $site_url );
        $greeting   = $first_name ? 'Welcome, ' . $first_name . '!' : 'Welcome!';

        $subject = sprintf( __( "You're confirmed — welcome to %s", 'neonews-newsletter' ), $site_name );

        $message = self::wrap_email(
            $subject,
            "
            <p><strong>{$greeting}</strong></p>
            <p>You're now subscribed to <strong>{$site_name}</strong>. You'll receive our daily briefing with the most important stories of the day.</p>
            <p style='text-align:center;margin:30px 0;'>
                <a href='" . esc_url( $site_url ) . "' style='background:#e63946;color:#fff;text-decoration:none;padding:14px 32px;border-radius:6px;font-weight:600;display:inline-block;'>
                    Read the latest news
                </a>
            </p>
            <p style='font-size:12px;color:#aaa;margin-top:30px;'>
                Don't want emails? <a href='" . esc_url( $unsub_url ) . "' style='color:#aaa;'>Unsubscribe</a>
            </p>
            "
        );

        return self::send( $email, $subject, $message );
    }

    /**
     * Wrap content in a clean HTML email template.
     *
     * @param string $title   Email title.
     * @param string $content HTML body content.
     * @return string
     */
    private static function wrap_email( $title, $content ) {
        $site_name = get_bloginfo( 'name' );
        $year      = gmdate( 'Y' );

        return "
<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width,initial-scale=1'>
<title>" . esc_html( $title ) . "</title>
</head>
<body style='margin:0;padding:0;background:#f4f4f4;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f4f4;padding:30px 0;'>
    <tr><td align='center'>
      <table width='600' cellpadding='0' cellspacing='0' style='background:#fff;border-radius:8px;overflow:hidden;max-width:600px;width:100%;'>

        <!-- Header -->
        <tr>
          <td style='background:#1d3557;padding:24px 32px;text-align:center;'>
            <span style='color:#fff;font-size:22px;font-weight:700;letter-spacing:-0.5px;'>{$site_name}</span>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style='padding:32px;color:#333;font-size:15px;line-height:1.6;'>
            {$content}
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style='background:#f9f9f9;padding:20px 32px;text-align:center;font-size:12px;color:#aaa;border-top:1px solid #eee;'>
            © {$year} {$site_name} · <a href='" . esc_url( home_url( '/privacy-policy/' ) ) . "' style='color:#aaa;'>Privacy Policy</a>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>";
    }

    /**
     * Send an HTML email.
     *
     * @param string $to      Recipient email.
     * @param string $subject Subject.
     * @param string $body    HTML body.
     * @return bool
     */
    private static function send( $to, $subject, $body ) {
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>',
        );

        return wp_mail( $to, $subject, $body, $headers );
    }
}
