# NeoNews Platform (Stable Edition)

A complete WordPress-based modern news publishing system built with production-ready code, following WordPress coding standards and security best practices.

## Features

### Theme (neonews-theme)
- Clean, modern news UI with original design
- Sign-in / sign-up modal with optional CAPTCHA (Turnstile, reCAPTCHA, hCaptcha)
- Lightweight performance mode (on by default) — deferred JS, lazy images, trimmed WP assets
- Mobile-first responsive layout
- Dark mode / Light mode (persistent)
- Fast loading (no heavy JS frameworks)
- SEO optimized with Schema.org NewsArticle markup
- Breaking news ticker
- Featured posts carousel
- Trending posts based on view count
- Full Elementor compatibility

### Core Plugin (neonews-core)
- Custom user roles (Reporter, Editor)
- Frontend news submission system
- User activity tracking with admin log (logins, reads, likes, shares, submissions, comments)
- Dashboard widgets, email alerts, Slack/webhook notifications, REST API, and CAPTCHA for login/signup
- Post view counter with bot protection
- Breaking news management
- OneSignal push notification integration

### Membership Plugin (neonews-membership)
- Free vs Premium user system
- Content restriction with paywall
- Shortcodes for premium content
- Configurable free article limits
- Stripe integration placeholder

### PWA Plugin (neonews-pwa)
- Web App Manifest
- Service Worker with offline support
- "Add to Home Screen" capability
- Offline fallback page

### Security Plugin (neonews-security)
- Security score scanner with 15+ checks
- AI-style threat analysis & prioritized fixes
- One-click automated hardening
- Encrypted API keys (AES-256-GCM)
- Login brute-force protection
- Security activity audit log

### Smart Tools Plugin (neonews-smart)
- Auto excerpt, story glance, key points, table of contents (100% on-server)
- Smart related posts, trending block, personalized homepage picks
- Submission spam checks and weekly email digest (no external AI)

See [docs/SMART-TOOLS.md](docs/SMART-TOOLS.md).

### Demo Importer
- 10 sample news articles
- 5 categories
- 2 sample users
- Idempotent (safe to re-run)

## Directory Structure

Theme and plugins live under `wp-content/` so you can pull straight into WordPress.

```
News/
├── wp-content/
│   ├── themes/
│   │   └── neonews-theme/      # WordPress theme
│   └── plugins/
│       ├── neonews-core/       # Required
│       ├── neonews-membership/
│       ├── neonews-pwa/
│       ├── neonews-security/
│       ├── neonews-seo/
│       ├── neonews-seo-lite/
│       ├── neonews-smart/
│       └── demo-importer/
├── scripts/
│   ├── link-to-wordpress.sh    # Mac/Linux: symlink into wp-content
│   ├── sync-to-wordpress.sh    # Mac/Linux: rsync copy into wp-content
│   └── link-to-wordpress.ps1   # Windows: junction into wp-content
├── docs/                       # Documentation
    ├── README.md           # Documentation hub (start here)
    ├── COMPLETE-GUIDE.md   # Everything in one file
    ├── GETTING-STARTED.md
    ├── PAGES-AND-TEMPLATES.md
    ├── CONTENT-MANAGEMENT.md
    ├── USERS-AUTH-MEMBERSHIP.md
    ├── MONITORING-AND-API.md
    ├── PERFORMANCE.md
    ├── FRONTEND-FEATURES.md
    ├── TROUBLESHOOTING.md
    ├── INSTALLATION.md
    ├── SETUP.md
    ├── ELEMENTOR.md
    ├── PUSH-NOTIFICATIONS.md
    └── SECURITY.md
```

## Quick Start

1. Install WordPress 5.9+
2. Copy or link `wp-content/themes/neonews-theme` and plugins into your site `wp-content/`
3. Activate **NeoNews Theme** and **NeoNews Core** (required)
4. Upload and activate `neonews-security` (recommended)
5. (Optional) Activate membership, PWA, demo importer
6. Import demo content via **NeoNews → Demo Content**
7. Run **NewsPulse → Security Center → Run Security Scan**
8. Configure at **NewsPulse → Platform** and **Appearance → Customize**

See [docs/INSTALLATION.md](docs/INSTALLATION.md) for detailed instructions.

## Git pull workflow (Windows → GitHub → Mac)

**One-time setup on Mac** (replace site name):

```bash
git clone https://github.com/0KartikRaut0/News.git ~/Projects/News
chmod +x ~/Projects/News/scripts/*.sh
~/Projects/News/scripts/link-to-wordpress.sh "$HOME/Local Sites/my-site/app/public/wp-content"
```

**After every change on Windows:**

```powershell
cd C:\Users\me7v9cs59\Desktop\News
git add .
git commit -m "Your change"
git push
```

**On Mac:**

```bash
cd ~/Projects/News
git pull
```

If you used `link-to-wordpress.sh`, WordPress updates immediately. If you prefer copies, run `scripts/sync-to-wordpress.sh` after each pull.

## Documentation

**Start here when stuck:** [docs/README.md](docs/README.md) — full index of every guide.

| Guide | Covers |
|-------|--------|
| [COMPLETE-GUIDE.md](docs/COMPLETE-GUIDE.md) | Every feature, setting, template, and task in one document |
| [GETTING-STARTED.md](docs/GETTING-STARTED.md) | First 30 minutes + demo import |
| [PAGES-AND-TEMPLATES.md](docs/PAGES-AND-TEMPLATES.md) | Creating pages like the demo, templates, menus |
| [CONTENT-MANAGEMENT.md](docs/CONTENT-MANAGEMENT.md) | Posts, categories, featured/breaking, submissions |
| [USERS-AUTH-MEMBERSHIP.md](docs/USERS-AUTH-MEMBERSHIP.md) | Roles, sign-in, CAPTCHA, paywall |
| [MONITORING-AND-API.md](docs/MONITORING-AND-API.md) | Activity log, alerts, REST API |
| [PERFORMANCE.md](docs/PERFORMANCE.md) | Performance score panel and speed |
| [FRONTEND-FEATURES.md](docs/FRONTEND-FEATURES.md) | Dark mode, ads, cookies, weather, engagement |
| [TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md) | Common problems and fixes |

## Requirements

- WordPress 5.9+
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+
- HTTPS (required for PWA)

## Security

This platform implements comprehensive WordPress security:

- All inputs sanitized with appropriate functions
- All outputs properly escaped
- Nonce verification on all forms
- Capability checks on all actions
- Prepared statements for all database queries
- Direct file access prevention
- Rate limiting on view counter
- Bot detection

See [docs/SECURITY.md](docs/SECURITY.md) for complete security documentation.

## Customization

### Theme Customizer

Access via **Appearance → Customize**:
- Site identity (logo, icon)
- NeoNews Options (theme mode, breaking news, carousel)
- Colors (primary, secondary)
- Footer settings

### CSS Variables

Customize colors in `wp-content/themes/neonews-theme/style.css`:

```css
:root {
    --nn-primary: #e63946;
    --nn-secondary: #1d3557;
    --nn-accent: #457b9d;
}
```

## Shortcodes

| Shortcode | Description |
|-----------|-------------|
| `[neonews_submit_form]` | News submission form |
| `[premium_content]...[/premium_content]` | Premium-only content |
| `[free_content]...[/free_content]` | Free content wrapper |
| `[membership_status]` | Display user membership status |

## Hooks & Filters

### Actions

```php
// After article submitted via frontend
do_action('neonews_article_submitted', $post_id, $user_id);

// When post marked as breaking news
do_action('neonews_post_marked_breaking', $post_id, $post);

// When user gains/loses premium status
do_action('neonews_user_became_premium', $user_id);
do_action('neonews_user_lost_premium', $user_id);
```

### Filters

```php
// Customize breaking news expiry (default 24 hours)
add_filter('neonews_breaking_news_expiry', function($hours) {
    return 48; // 48 hours
});
```

## Functions

### Check Premium Status

```php
// Check if user is premium
if (neonews_is_user_premium()) {
    // Show premium content
}

// Check specific user
if (neonews_is_user_premium($user_id)) {
    // User is premium
}
```

### Get Post Stats

```php
// Get view count
$views = neonews_get_post_views($post_id);

// Get formatted views
$formatted = neonews_format_views($views); // "1.2K"

// Get reading time
$minutes = neonews_get_reading_time($post_id);
```

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome for Android)

## Performance

- **NewsPulse → Platform** — performance score panel (grade, savings estimate, one-click apply)
- Lightweight mode enabled by default (defer JS, lazy images, trim WP bloat)
- No jQuery dependency in theme
- Minimal vanilla JavaScript
- Lazy loading images
- Deferred script loading
- CSS custom properties for theming
- Optimized database queries

## License

GPL v2 or later

## Credits

- Built with WordPress native APIs
- Icons: Inline SVG (no external dependencies)
- Fonts: System font stack

## Support

For documentation, open **[docs/README.md](docs/README.md)** — the documentation hub.

Quick links:
- [Complete Guide](docs/COMPLETE-GUIDE.md) — search here first
- [Installation Guide](docs/INSTALLATION.md)
- [Getting Started](docs/GETTING-STARTED.md)
- [Pages & Templates](docs/PAGES-AND-TEMPLATES.md)
- [Setup Guide](docs/SETUP.md)
- [Elementor Guide](docs/ELEMENTOR.md)
- [Push Notifications](docs/PUSH-NOTIFICATIONS.md)
- [Security Guide](docs/SECURITY.md)
- [Troubleshooting](docs/TROUBLESHOOTING.md)
