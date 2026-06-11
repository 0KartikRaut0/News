# Users, Authentication & Membership

Roles, sign-in modal, CAPTCHA, premium membership v2 (activation keys, badges, exclusive content), and account pages.

---

## User roles

| Role | Slug | Purpose |
|------|------|---------|
| Administrator | `administrator` | Full site control |
| Editor | `editor` | Publish/edit all posts, approve submissions |
| Author | `author` | Publish own posts |
| **Reporter** | `nn_reporter` | Submit articles (pending review) |
| **Premium Subscriber** | `nn_premium_subscriber` | Read premium content |
| Subscriber | `subscriber` | Basic registered user |

### Reporter capabilities

- Read site
- Create/edit own posts (cannot publish — status pending)
- Upload media

---

## Sign-in modal

Visitors click **Sign In** in header.

| Tab | Fields |
|-----|--------|
| **Sign In** | Email or username, password |
| **Sign Up** | First name, last name, education, occupation, email, password (fields configurable) |
| **Forgot password** | In-modal reset via email (no redirect to `wp-login.php`) |

Logged-in users see account menu: **My Activity**, **Profile**, **Log Out**.

**NewsPulse → Platform → Login & sign up** — allow registration, field toggles, custom fields.

**NewsPulse → Header → Sign-in / account menu** — show/hide auth UI.

---

## Login & signup CAPTCHA {#login-signup--captcha}

**NewsPulse → Platform → Login & signup CAPTCHA**

Providers: Cloudflare Turnstile (recommended), reCAPTCHA v2/v3, hCaptcha.

Enable **NewsPulse → Security Center → Encrypt API keys** to store CAPTCHA secrets encrypted.

Enable **NewsPulse → Platform → Performance → Lazy CAPTCHA** so provider scripts load only when the auth modal opens.

---

## Account pages

| Page | Template | Content |
|------|----------|---------|
| My Account | My Account | `[neonews_account]` |
| My Profile | My Profile | Profile form + membership panel |
| Exclusive News | Exclusive News | Auto-created at `/exclusive/` |

### My Account shows

- Plan badge tag (Basic Plan / Premium Plan)
- Avatar plan pill (Basic / Premium)
- Expiry countdown when plan ends within configured days
- Enroll to Premium section (non-premium users)
- Liked, shared, read history, comments

### My Profile shows

- Avatar with plan badge overlay
- Plan tag under name
- Activation key form (redeem premium)
- Enroll section with WhatsApp + email contact (non-premium)
- Expiry timer when applicable

---

## Membership v2 (NeoNews Membership 2.0.0) {#membership--paywall}

Requires **neonews-membership** plugin.

### How premium works

| Method | Description |
|--------|-------------|
| **Activation key** | Admin generates key → user pays manually → redeems on Profile |
| **Manual grant** | **Users → Edit User → Premium Status** + expiry date |
| **Payment gateway** | Stripe fields reserved under **Payment Gateway** tab (future) |

Premium duration **stacks** when a user redeems another key before expiry.

### Admin: NewsPulse → Membership

| Tab | Controls |
|-----|----------|
| **General** | Paywall, ad-free for premium, free article limits, exclusive page slug, default key duration |
| **Badges & Timer** | Basic/Premium labels, show/hide badges on avatar, profile, account, comments; expiry reminder days |
| **Activation Keys** | Generate keys (duration, batch label, validity), revoke, view usage |
| **Plan & Contact** | Plan title/price/features, enrollment note, WhatsApp + email templates |
| **Content & Lock** | Blur lock preview, blur strength, paywall excerpt length |
| **Popup & Home** | Upgrade popup delay/interval/copy; homepage **Premium Picks** section |
| **Payment Gateway** | Stripe test/live keys (encrypted when Security Center encryption is on) |

### Plan badges (frontend)

| User type | Avatar pill | Profile / Account tag |
|-----------|-------------|------------------------|
| Basic (logged in) | **Basic** | **Basic Plan** |
| Premium | **Premium** | **Premium Plan** |
| Expiring soon | Red ring on avatar pill | Countdown on Profile + My Activity |

Toggle each location under **Badges & Timer**.

### Activation keys workflow

1. **NewsPulse → Membership → Activation Keys**
2. Set **Premium duration (days)** and optional **Key valid for (days)**
3. Click **Generate Keys** — copy the code (format `NN-XXXX-XXXX`)
4. User contacts team via WhatsApp/email (templates under **Plan & Contact**)
5. After payment, send the key
6. User opens **Profile** → enters key → **Activate Premium**

**Errors shown to user:** invalid key, already used, expired, too many failed attempts (rate limited).

Failed attempts are logged in **Security Center → Activity Log** when `neonews-security` is active.

### Exclusive content

- Page: **`/exclusive/`** (Exclusive News template, auto-created)
- Mark posts: **Premium Content** meta box → **Premium / exclusive post**
- Optional: **Show in homepage Premium Picks**
- Basic users: blurred preview + lock overlay on single posts
- Premium users: full access, no ads (if enabled)

### Upgrade popup

Non-premium visitors see a configurable popup after a delay (default 45s, re-shows after 24h if dismissed).

Edit under **Membership → Popup & Home**.

### Ad-free premium

**Membership → General → Hide ads for premium** — skips all theme ad zones for premium members.

---

## Mark premium articles

Edit post → **Premium Content**:

| Checkbox | Effect |
|----------|--------|
| **Premium / exclusive post** | Paywall + blur lock for basic users |
| **Show in homepage Premium Picks** | Appears in home Premium Picks section |

Posts list **Premium** column: lock icon; home flag shows house emoji.

---

## Shortcodes

```
[premium_content]
Exclusive analysis for members.
[/premium_content]

[free_content]
Free preview text.
[/free_content]

[membership_status]
```

---

## PHP helpers

```php
if ( neonews_is_user_premium() ) {
    // current user is premium
}

if ( neonews_is_user_premium( $user_id ) ) {
    // specific user
}

// Render plan tag in custom templates
if ( function_exists( 'neonews_membership_render_plan_tag' ) ) {
    neonews_membership_render_plan_tag( 'profile', $user_id );
}
```

---

## Hooks

```php
do_action( 'neonews_user_became_premium', $user_id );
do_action( 'neonews_user_lost_premium', $user_id );
do_action( 'neonews_activation_key_redeemed', $user_id, $key_code, $duration_days );

do_action( 'neonews_profile_membership_panel' );  // Profile membership UI
do_action( 'neonews_account_membership_panel' );   // My Activity membership UI
do_action( 'neonews_home_after_hero' );            // Homepage Premium Picks hook
```

### Filters

```php
apply_filters( 'neonews_should_render_ad_zone', true, $zone_id ); // ad-free premium
```

---

## User meta keys (membership)

| Meta key | Purpose |
|----------|---------|
| `_neonews_is_premium` | `1` or `0` |
| `_neonews_premium_expiry` | Date string; empty = lifetime |
| `_neonews_premium_start` | When premium was granted |

---

## Post meta keys (premium)

| Meta key | Purpose |
|----------|---------|
| `_neonews_premium_post` | `1` = exclusive/premium post |
| `_neonews_premium_on_home` | `1` = show in Premium Picks on homepage |

---

## Database: activation keys

Table: `{prefix}neonews_activation_keys`

Stores generated keys, duration, status (`active`, `used`, `revoked`, `expired`), and redemption user.

---

## Security recommendations

- Enable **Security Center → Encrypt API keys** (encrypts Stripe secrets in Membership settings)
- Enable **Login brute-force protection** when paywall is active (scanner warns if off)
- Enable CAPTCHA on login/register
- Revoke unused activation keys promptly
- Use short key validity windows for high-value plans

See [SECURITY.md#membership--monetization-security](SECURITY.md#membership--monetization-security).

---

## Performance notes

- Membership CSS/JS loads conditionally (posts, home, archives, profile, account, exclusive page)
- Enable **Conditional plugin assets** under Platform → Performance
- Upgrade popup JS is small and deferred; tune delay if intrusive

See [PERFORMANCE.md#membership--performance](PERFORMANCE.md#membership--performance).

---

## Activity logged for users

Login, logout, read, submit, like, share, comment, profile update, profile photo.

Failed activation key attempts appear in Security Center activity log.

See [MONITORING-AND-API.md](MONITORING-AND-API.md).
