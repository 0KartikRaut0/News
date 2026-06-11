# NeoNews Platform - Security Guide

## Built-in Security Features

NeoNews Platform includes comprehensive security measures following WordPress best practices.

### Input Sanitization

All user inputs are sanitized using WordPress functions:

| Data Type | Function Used |
|-----------|--------------|
| Text fields | `sanitize_text_field()` |
| Textarea | `sanitize_textarea_field()` |
| HTML content | `wp_kses_post()` |
| URLs | `esc_url()` |
| Emails | `sanitize_email()` |
| Keys/slugs | `sanitize_key()` |
| Numbers | `absint()` or `intval()` |
| File names | `sanitize_file_name()` |

### Output Escaping

All output is escaped to prevent XSS:

| Context | Function Used |
|---------|--------------|
| HTML | `esc_html()` |
| Attributes | `esc_attr()` |
| URLs | `esc_url()` |
| JavaScript | `esc_js()` |
| Textarea | `esc_textarea()` |

### Nonce Verification

All forms and AJAX requests use WordPress nonces:

```php
// Form includes nonce field
wp_nonce_field('action_name', 'nonce_field_name');

// Verification on submission
if (!wp_verify_nonce($_POST['nonce'], 'action_name')) {
    die('Security check failed');
}
```

### Capability Checks

All actions verify user capabilities:

```php
if (!current_user_can('edit_posts')) {
    wp_die('Unauthorized access');
}
```

### SQL Injection Prevention

All database queries use prepared statements:

```php
$wpdb->prepare(
    "SELECT * FROM {$wpdb->posts} WHERE ID = %d",
    $post_id
);
```

### Direct File Access Prevention

All PHP files include:

```php
if (!defined('ABSPATH')) {
    exit;
}
```

## File Upload Security

### News Submission Uploads

The submission form validates uploads:

1. **File type validation**: Only JPEG, PNG, GIF, WebP allowed
2. **MIME type check**: Verified against actual file content
3. **Size limit**: 2MB maximum
4. **WordPress handling**: Uses `media_handle_upload()` for safe processing

### Preventing Execution

- No direct uploads to arbitrary directories
- All uploads go through WordPress media library
- PHP execution blocked in uploads folder (recommended .htaccess)

## Rate Limiting

### View Counter Protection

Prevents artificial view inflation:

```php
// 60-second cooldown per IP per post
$transient_key = 'nn_view_' . $post_id . '_' . $ip_hash;
if (get_transient($transient_key)) {
    return; // Don't count
}
set_transient($transient_key, true, 60);
```

### Login Attempt Limiting

Recommended: Use a security plugin like:
- Limit Login Attempts Reloaded
- Wordfence
- Sucuri Security

## Privacy Protections

### IP Address Handling

- IP addresses are hashed (MD5) before storage
- Combined with daily salt for privacy
- Original IP never stored

### Cookie Compliance

Cookies used:
| Cookie | Purpose | Duration |
|--------|---------|----------|
| `neonews_theme` | Dark/light mode preference | 1 year |
| `nn_articles_read` | Free article tracking | Until period end |

For GDPR compliance:
- Add cookie notice plugin
- Update privacy policy
- Provide opt-out mechanism

## API Security

### OneSignal Integration

- API keys stored in database (not files)
- Server-side API calls only
- No credentials exposed to frontend

### AJAX Requests

All AJAX handlers include:
1. Nonce verification
2. Capability checks
3. Data validation
4. Sanitized responses

## Recommended Security Plugins

### Essential

1. **Wordfence Security** (free)
   - Firewall
   - Malware scanner
   - Login security

2. **Limit Login Attempts Reloaded** (free)
   - Brute force protection
   - IP blocking

### Additional

3. **Sucuri Security** (free/paid)
   - Security auditing
   - Malware monitoring

4. **Two Factor Authentication** (free)
   - 2FA for admin accounts

## WordPress Hardening

### wp-config.php Settings

Add these security constants:

```php
// Disable file editing from admin
define('DISALLOW_FILE_EDIT', true);

// Limit post revisions
define('WP_POST_REVISIONS', 5);

// Disable debug in production
define('WP_DEBUG', false);

// Force SSL for admin
define('FORCE_SSL_ADMIN', true);
```

### .htaccess Security

Add to root `.htaccess`:

```apache
# Block access to sensitive files
<FilesMatch "^(wp-config\.php|readme\.html|license\.txt)">
    Order allow,deny
    Deny from all
</FilesMatch>

# Disable directory browsing
Options -Indexes

# Block PHP in uploads
<Directory "/wp-content/uploads/">
    <Files "*.php">
        Deny from all
    </Files>
</Directory>
```

### Disable XML-RPC

NeoNews Security Center blocks XML-RPC when enabled (recommended):

```php
add_filter('xmlrpc_enabled', '__return_false');
```

## NeoNews Security Center (Built-in Plugin)

Install `neonews-security/` and open **NewsPulse → Security Center**.

| Feature | Description |
|---------|-------------|
| Security Dashboard | Score, scan results, activity log |
| AI Threat Analysis | Plain-language risk report with priorities |
| One-Click Fixes | Hardening, uploads protection, encryption |
| AES-256 Encryption | API keys & scripts encrypted at rest |
| Login Guard | Brute-force lockout + email alerts |
| Activity Log | Login, scans, fixes (encrypted context) |

**Workflow:** Run Scan → Read AI Analysis → Fix automatically / Fix All → Review Activity Log.

### Membership & monetization security {#membership--monetization-security}

When **neonews-membership** is active, the scanner adds checks for:

| Finding | Severity | Fix |
|---------|----------|-----|
| Stripe keys stored without encryption | High | Enable **Encrypt API keys** |
| Login brute-force off while paywall active | Medium | Enable **Login brute-force protection** |

**Activation keys:**

- Failed key redemption attempts are rate-limited (5 failures / 15 minutes per user+IP)
- Failures are logged to **Security Center → Activity Log** as `membership` events
- Revoke compromised keys under **NewsPulse → Membership → Activation Keys**

**Encrypted at rest (when encryption enabled):**

| Option | Keys encrypted |
|--------|----------------|
| `neonews_core_settings` | OneSignal REST key |
| `neonews_platform_settings` | CAPTCHA secret, cookie custom script |
| `neonews_membership_settings` | Stripe test/live secret keys |

Manual fixes still required for: HTTPS/SSL, PHP upgrades, plugin updates, renaming `admin` user.

## User Role Security

### Role Capabilities

| Role | Risk Level | Mitigation |
|------|------------|------------|
| Reporter | Low | Can only submit pending posts |
| Editor | Medium | Regular audits recommended |
| Admin | High | Use strong passwords, 2FA |

### Reporter Restrictions

Reporters cannot:
- Publish posts (require approval)
- Delete posts
- Access sensitive settings
- Modify other users' content

## Security Checklist

### Before Launch

- [ ] Change default admin username
- [ ] Use strong passwords (12+ characters)
- [ ] Enable 2FA for admin accounts
- [ ] Install security plugin
- [ ] Update WordPress core
- [ ] Update all themes and plugins
- [ ] Enable HTTPS site-wide
- [ ] Configure backup solution
- [ ] Set proper file permissions (755/644)
- [ ] Disable directory listing
- [ ] Block PHP execution in uploads

### Ongoing Maintenance

- [ ] Weekly: Check for updates
- [ ] Monthly: Review user accounts
- [ ] Monthly: Check security logs
- [ ] Quarterly: Audit installed plugins
- [ ] Quarterly: Test backup restoration

## Incident Response

### If Hacked

1. **Don't panic**
2. Take site offline (maintenance mode)
3. Change all passwords immediately
4. Scan for malware (Wordfence/Sucuri)
5. Restore from clean backup
6. Update everything
7. Check for backdoors
8. Document incident
9. Monitor closely

### Reporting Vulnerabilities

If you discover a security issue in NeoNews:

1. **Do not** disclose publicly
2. Email details to security team
3. Allow 90 days for fix
4. Coordinate disclosure

## Security Updates

Keep everything updated:

```bash
# Check for updates
wp core check-update
wp plugin update --all
wp theme update --all
```

Or enable auto-updates in WordPress settings.

## Audit Logging

Recommended: Install activity log plugin to track:
- Login attempts
- Content changes
- Settings modifications
- User changes

Popular options:
- WP Activity Log
- Simple History
- User Activity Log
