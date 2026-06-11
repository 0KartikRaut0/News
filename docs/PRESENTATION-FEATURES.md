# NewsPulse Platform — Complete Feature Overview

**Product name:** NewsPulse (NeoNews Platform)  
**Type:** WordPress news & magazine publishing system  
**Version:** Theme 1.6.3 | Core 1.2.4 | Security 1.0.0 | Membership 2.0.0 | PWA 1.0.0 | Demo Importer 1.1.0  
**Purpose of this document:** Presentation-ready list of all included features for client or stakeholder review. Convert this file to PDF using any Markdown-to-PDF tool.

---

## 1. Platform Summary

- Complete WordPress-based news website solution — theme plus plugins working together as one product
- Built for modern news sites, digital magazines, and editorial publishers
- Mobile-first responsive design on all screen sizes (phone, tablet, desktop)
- Single admin hub under **NewsPulse** in WordPress dashboard for almost all settings
- One-click demo import to launch a full sample news site in minutes
- Production-ready code following WordPress security and coding standards
- No dependency on heavy JavaScript frameworks — fast loading out of the box
- Optional add-ons: membership paywall, PWA installable app, security center, push notifications
- Suitable for independent newsrooms, student media, niche publishers, and corporate news portals

---

## 2. NeoNews Theme — Design & User Interface

### 2.1 Visual design & branding

- Clean, modern editorial layout inspired by professional news websites
- Orange editorial color palette with full light and dark mode support
- Custom logo upload or text-based logo mark character when no image logo is set
- Google Fonts integration with 12 curated font choices for body and headings
- System font option for maximum page speed (skips external font requests)
- Full color customization through WordPress Customizer (primary, secondary, accent, backgrounds, text, borders)
- Separate dark mode color controls for background and text
- Adjustable border radius for cards and UI elements
- Category color badges — assign custom hex colors per category slug
- CSS custom properties (variables) for consistent theming across the site
- SEO-friendly structure with Schema.org **NewsArticle** markup on single posts
- Theme screenshot included for WordPress Appearance → Themes preview

### 2.2 Layout & page structure

- Dedicated homepage template (`front-page.php`) with configurable sections
- Standard blog archive, category archive, search results, and author archive templates
- Single post template with engagement features and reading time
- 404 error page styled to match the site
- Comments template integrated with member badges for premium users
- Full-width page template for landing pages and page builders
- 12 custom page templates: About, Contact, Careers, Advertise, My Account, My Profile, Legal, Terms, Cookies, Cache & Storage, and more
- Sidebar layout for blog and archive pages
- Homepage sidebar rail with built-in widgets (weather, trending, topics, newsletter)
- Four footer widget columns plus footer menu support
- Three optional homepage widget sections for extra content blocks
- Header widget area for optional promotional content
- Posts per row control on desktop (2, 3, or 4 columns)

### 2.3 Homepage sections (all toggleable from admin)

- Full-width homepage banner — image or embedded YouTube video with optional click-through link
- Hero headline block with main title, accent title line, and subtitle text
- Featured posts carousel with large images, category badges, and post meta
- Carousel auto-rotation with previous/next controls and dot indicators
- Touch swipe support on mobile for carousel navigation
- Keyboard arrow key navigation for carousel accessibility
- Category content blocks — grid of posts grouped by category slug
- Configurable number of categories and posts per category block
- Mid-page advertisement slot after a chosen category block number
- **Watch / Videos** section for YouTube-linked video posts
- Reader tipline call-to-action (“Have a tip?” style section)
- Latest posts grid at bottom of homepage
- Sidebar newsletter signup box on homepage
- All section visibility and counts controlled without code

### 2.4 Header features

- Live date and time bar in header (updates in real time)
- Breaking news ticker scrolling latest breaking headlines
- Customizable “LIVE” and “Breaking” label text
- Configurable maximum number of breaking items in ticker
- Primary navigation menu with WordPress menu manager integration
- Separate mobile menu location for mobile-specific navigation
- Automatic fallback navigation from category and page slugs when no menu is assigned
- Header search bar with separate desktop and mobile placeholder text
- Full-screen / overlay search experience for finding articles
- Search limited to posts by default (fast, focused results)
- Sign In / Sign Up button opening modal authentication (no redirect to ugly login page)
- Logged-in account dropdown: My Activity, Profile, Sign Out
- Dark mode / light mode toggle in header with instant switch
- Theme mode preference saved per user (logged in) or in browser cookie (guests)
- Default light or dark mode set site-wide from Customizer
- Weather conditions chip in header bar (temperature and conditions icon)
- Custom site logo from WordPress Site Identity settings

### 2.5 Footer features

- Brand tagline text under logo area
- Custom copyright line (also editable in Customizer)
- Social media links: X (Twitter), Facebook, LinkedIn, Instagram
- Auto-generated category links column from configured category slugs
- Company pages column from configured page slugs (About, Careers, Advertise, Contact, etc.)
- Footer newsletter signup block with title, description, and optional external form action URL (e.g. Mailchimp)
- Footer menu location for additional legal and utility links
- Four footer widget columns for custom WordPress blocks

### 2.6 Single article (post) experience

- Featured image support with responsive sizing
- Category badge on post cards and single view
- Post meta line: author, date, reading time estimate
- Reading time calculated from word count and configurable words-per-minute setting
- View count display on posts (when enabled)
- Engagement bar on single posts: like, share, and view metrics
- One-click like button for logged-in users (AJAX, no page reload)
- Share buttons: X (Twitter), Facebook, LinkedIn, and copy link to clipboard
- Share count tracking per post
- Excerpt length control for cards and listings (word count setting)
- YouTube video meta on posts for video section and rich cards
- Video duration display on video cards
- Member badge on comments for premium subscribers
- Scroll reveal animations on homepage cards (can be reduced for performance)
- Respects user’s “prefers reduced motion” browser setting when performance option enabled

### 2.7 Sidebar (built-in blocks — no widget plugin required)

- Weather widget card with city-based or coordinate-based location
- **Fresh Reads** widget — trending posts by view count with custom title and post count
- **Topics** widget — category pill list with configurable number of categories
- Standard WordPress Sidebar widget area for Search, Recent Posts, etc.
- Sidebar weather toggle independent from header weather chip

---

## 3. Authentication & User Accounts

### 3.1 Sign-in modal (frontend)

- Modern popup modal for login — no need to send users to default WordPress login screen
- Sign In tab: username or email plus password
- Sign Up tab: new user registration from the frontend
- Forgot password link to WordPress password reset flow
- AJAX-powered login and registration (smooth experience, no full page reload)
- Toast notifications for success and error messages
- Account menu in header when user is logged in
- **My Account** page template with activity history (likes, shares, reads, comments)
- **My Profile** page template for profile photo and settings
- `[neonews_account]` shortcode for member dashboard content

### 3.2 CAPTCHA protection (Core + Theme)

- Optional CAPTCHA on login and registration — configurable per action
- Supported providers: Cloudflare Turnstile, Google reCAPTCHA v2, Google reCAPTCHA v3, hCaptcha
- Site key and secret key settings in NewsPulse → Platform
- reCAPTCHA v3 score threshold setting (0.1 to 1.0)
- Protects AJAX sign-in modal AND standard `wp-login.php` page
- Lazy CAPTCHA loading — provider scripts load only when user opens sign-in modal (performance optimization)
- Secret keys can be encrypted at rest when Security plugin encryption is enabled

### 3.3 Author & profile features

- Author photo upload on user profile in WordPress admin
- Author photos displayed on author archives and bylines
- Profile update activity logged in user activity system
- Profile photo change events logged separately

---

## 4. NeoNews Core Plugin — Editorial & Engagement

### 4.1 Custom user roles

- **Reporter** role — can write and submit articles but cannot publish directly (pending review workflow)
- **Premium Subscriber** role — read-only role for membership system
- Custom capabilities for submission management and post approval
- Editors and Administrators can approve pending submissions
- Demo import creates sample Reporter and Editor users for testing

### 4.2 Frontend news submission system

- Full article submission form via `[neonews_submit_form]` shortcode
- Submit Story page included in demo import
- Logged-in users with Reporter, Author, Editor, or Admin role can submit
- Submitted posts enter **Pending** status for editorial review
- **NewsPulse → Pending Submissions** admin shortcut to review queue
- Required fields: title, content, category
- Optional: featured image, excerpt
- Submission source and date stored in post meta for audit trail
- Posts list shows submission flag icon in admin
- Enable/disable submission system from Core Features settings
- `neonews_article_submitted` developer hook fires after successful submission

### 4.3 Post view counter

- Automatic view counting on single post pages
- Bot detection and rate limiting to prevent inflated counts
- Total views and 7-day views shown in post editor meta box
- Views column in WordPress Posts list admin table
- View counts on frontend post meta and engagement bar (when enabled)
- Formatted view display helper (e.g. “1.2K” for large numbers)
- Enable/disable view counter from Core Features settings
- Cookie consent can gate statistics tracking until user accepts cookies

### 4.4 Featured & breaking news management

- **Featured Post** checkbox in post editor — adds post to homepage carousel
- **Breaking News** checkbox in post editor — adds post to header ticker
- Breaking news automatically expires after 24 hours (configurable via developer filter)
- Multiple breaking items scroll together in ticker
- Featured and breaking flags shown as icons in Posts list admin column
- Breaking news can trigger push notification automatically when OneSignal is configured

### 4.5 OneSignal push notifications

- Web push notification integration with OneSignal service
- OneSignal App ID and REST API Key settings in Core Features
- Per-post **Send Push Notification** checkbox in post editor
- Automatic push when post marked as Breaking News (with “BREAKING:” title prefix)
- Notification includes post title, excerpt snippet, permalink, and featured image
- Server-side API calls only — keys never exposed to frontend
- Free OneSignal tier supported

### 4.6 Post editor enhancements

- **NeoNews Options** meta box: Featured, Breaking, Send Push
- **Post Statistics** meta box: total views, 7-day views, submission info
- **YouTube Video** meta box: video URL and duration for Watch section
- Posts list columns: Views count and Flags (featured, breaking, submission icons)

---

## 5. User Activity Log & Monitoring

### 5.1 Activity tracking

- Dedicated database table for user activity events
- Tracks: login, logout, read article, submitted article, profile updated, profile photo changed, liked article, shared article, posted comment, admin actions
- Enable/disable tracking from Core Features settings
- Respects privacy — can be turned off if not required

### 5.2 Admin activity log UI

- **NewsPulse → User Activity** full log page
- Filter by user, event type, date range, and text search
- Statistics cards: events in last 7 days, active users, top event types
- Paginated table: time, user, event, details, reference link
- Export filtered results to CSV file
- Per-user activity summary on WordPress user edit profile page (last 10 events)

### 5.3 Dashboard widgets

- **NewsPulse Activity Overview** widget on WordPress Dashboard — 7-day activity summary
- **Recent User Activity** widget on WordPress Dashboard — latest events list

### 5.4 Anomaly detection & alerts

- Automatic detection of suspicious activity patterns
- Email alerts for anomalies — configurable recipient address
- Optional alert when Administrator account logs in
- Slack Incoming Webhook support for team notifications
- Generic JSON webhook support for custom integrations (Zapier, Discord bots, etc.)
- **Send test alert** button to verify email and webhook without waiting for real event
- Enable/disable alerts independently from activity tracking

### 5.5 REST API for activity data

- REST namespace: `/wp-json/neonews/v1/`
- `GET /activity` — paginated log with filters (user, type, dates, search)
- `GET /activity/stats` — aggregate statistics for configurable day range
- `GET /activity/types` — list of all event type labels
- `GET /activity/{id}` — single log entry detail
- Admin authentication required (Application Passwords or admin session)
- Suitable for external dashboards, reporting tools, and custom admin panels

---

## 6. Membership & Premium Content (v2.0)

### 6.1 Paywall & exclusive content

- Free vs Premium content model with blur-lock preview for basic users
- Enable/disable paywall; configurable free article limits (day/week/month)
- **Premium / exclusive post** checkbox per post + **Show in homepage Premium Picks**
- Exclusive News page (`/exclusive/`) with locked card grid
- Homepage **Premium Picks** section for flagged stories
- Premium subscribers: full access + optional ad-free reading

### 6.2 Activation keys (manual monetization)

- Admin generates unique keys with configurable premium duration
- Optional key expiry for unused codes
- User redeems on Profile page after manual payment
- WhatsApp + email enrollment templates (editable)
- Rate-limited failed attempts; logged to Security Center

### 6.3 Plan badges & expiry reminders

- **Basic** / **Premium** pills on avatar (header, profile, account)
- **Basic Plan** / **Premium Plan** tags on Profile and My Activity
- Expiry countdown when plan ends within N days (configurable)
- Red warning ring on avatar when expiring soon

### 6.4 Membership admin (7 tabs)

General · Badges & Timer · Activation Keys · Plan & Contact · Content & Lock · Popup & Home · Payment Gateway

### 6.5 Upgrade engagement

- Configurable upgrade popup (delay, interval, copy)
- Enroll to Premium panel on Profile and My Activity

### 6.6 Shortcodes & developer API

- `[premium_content]`, `[free_content]`, `[membership_status]`
- `neonews_is_user_premium( $user_id )`
- Hooks: `neonews_user_became_premium`, `neonews_user_lost_premium`, `neonews_activation_key_redeemed`
- Filter: `neonews_should_render_ad_zone` (ad-free premium)

### 6.7 Stripe integration (roadmap)

- Stripe test/live key fields under Payment Gateway tab
- Secrets encrypted at rest when Security Center encryption is enabled
- Automated checkout reserved for future release

---

## 7. Security Center Plugin

### 7.1 Security scanner

- One-click **Run Security Scan** from Security Center dashboard
- Membership-aware checks when paywall/Stripe keys are configured
- Failed premium activation key attempts logged to activity log
- 15+ automated security checks across WordPress configuration
- Security score out of 100 displayed on dashboard
- Findings listed with severity levels: Critical, High, Medium, Low
- 7-day security activity summary chart
- AI-style threat analysis with prioritized recommendations
- **Fix automatically** button on individual findings where auto-fix is available
- **Fix All (Auto)** button to apply all automated fixes at once
- Security activity audit log with filterable history

### 7.2 Automated hardening options

- Encrypt API keys and sensitive scripts at rest using AES-256-GCM encryption
- Send HTTP security headers (HSTS, X-Frame-Options, X-Content-Type-Options, etc.)
- Block XML-RPC endpoint (prevents brute force and pingback abuse)
- Hide WordPress version number in HTML output
- Disable theme and plugin file editor in admin (prevents code editing if admin compromised)
- Block user and author enumeration via URL probing
- Block PHP file execution in uploads directory
- Force secure authentication cookies (HTTPS-only cookies)

### 7.3 Login brute-force protection

- Configurable maximum failed login attempts before lockout
- Configurable lockout duration in minutes
- Email alert when account or IP is locked out
- Separate alert email address for security notifications
- Works alongside CAPTCHA for layered login protection

### 7.4 Platform-wide security practices (built into all components)

- All form inputs sanitized with WordPress sanitization functions
- All HTML output escaped to prevent XSS attacks
- Nonce verification on every form submission and AJAX request
- Capability checks on all admin actions and REST endpoints
- Prepared SQL statements for all custom database queries
- Direct file access blocked on all PHP files (`ABSPATH` check)
- Rate limiting on view counter and login attempts

---

## 8. Performance & Speed Optimization

### 8.1 Lightweight mode (enabled by default)

- Master performance switch controls all optimizations site-wide
- JavaScript files loaded with defer attribute — non-blocking page render
- Lazy loading on images in post content and theme templates
- Non-blocking CSS preload pattern for main theme stylesheet
- No jQuery dependency on frontend — vanilla JavaScript only
- Idle initialization for heavy JavaScript features (loads after browser is idle)
- IntersectionObserver for weather widget (loads only when visible on screen)

### 8.2 WordPress bloat removal options

- Disable WordPress emoji detection scripts and styles
- Disable wp-embed.js oEmbed helper on frontend
- Disable Dashicons icon font for non-logged-in visitors
- Remove block editor (Gutenberg) CSS on frontend for classic-layout sites
- Disable WordPress Heartbeat AJAX polling on public frontend pages

### 8.3 Smart asset loading

- Core plugin CSS and JavaScript loaded only on post pages and submission pages (not every page)
- Membership plugin CSS/JS loaded on posts, home, archives, profile, account, and exclusive pages (when conditional assets enabled)
- CAPTCHA provider scripts lazy-loaded on sign-in modal open only
- Reduced scroll and motion animations option for lower CPU usage
- System fonts option skips all Google Fonts network requests (~120 KB saved)

### 8.4 Performance Score admin panel

- Visual score ring (0–100) with letter grade A+ through F on Platform settings page
- Lists all currently active optimizations with estimated savings per item
- Shows total estimated KB saved, HTTP requests saved, and faster interactivity estimate
- Recommendations list with jump links to relevant settings
- **Apply N recommended fixes** one-click button to enable all missing optimizations
- Suggestions for weather widget, analytics, and external page cache plugin

---

## 9. Advertisements & Monetization

### 9.1 Ad zone system

- Master toggle to show or hide all ad zones site-wide instantly
- Eight individual ad placement zones, each independently enabled or disabled
- Ad zones implemented as WordPress widget areas — paste any ad network HTML code
- Cookie consent can gate ad loading until user accepts all cookies

### 9.2 Ad placement locations

- **Header banner** — top of site, leaderboard size (728×90 style)
- **Home — below hero carousel** — high-visibility placement after featured content
- **Home — between category blocks** — mid-page placement after configurable category number
- **Home — before videos section** — placement before Watch/Video grid
- **In-content ad** — within homepage content flow
- **Home — sidebar rail** — beside homepage content column
- **Home — footer area** — bottom of homepage before site footer
- **Mobile sticky bottom** — fixed ad bar on mobile devices

### 9.3 Other monetization pages

- **Advertise With Us** page template included for ad sales information
- Newsletter signup forms in footer and homepage sidebar with external form action URL support
- Membership / Go Premium page for subscription revenue

---

## 10. Cookie Consent & Analytics

### 10.1 Cookie consent banner

- GDPR-style cookie consent bar at bottom of site on first visit
- Two clear choices: **Essential only** and **Accept all cookies**
- User choice stored and remembered on return visits
- Enable/disable banner from Platform settings

### 10.2 Consent-gated features

- **Gate ad zones** — advertisements load only after full cookie consent
- **Gate statistics** — view counts and share tracking respect consent choice
- **Google Analytics 4** — loads only after consent when enabled
- GA4 Measurement ID setting (G-XXXXXXXXXX format)
- **Custom tracking scripts** field — any third-party script loaded only after consent

### 10.3 Legal pages (auto-create)

- One-click **Create / Update Legal Pages** button in Platform settings
- Privacy Policy page with Legal Page template
- Terms of Service page with dedicated template
- Cookies Policy page with dedicated template
- Cache & Storage Policy page with dedicated template
- All legal pages use styled templates matching site design

---

## 11. Weather Widget

- Live weather display in header chip and sidebar card
- Powered by Open-Meteo API — completely free, no API key required
- Default city name setting with automatic geocoding
- Optional precise latitude and longitude for exact location
- Enable/disable weather globally or independently in header vs sidebar
- Weather data fetched via AJAX — does not slow initial page HTML load
- IntersectionObserver ensures sidebar weather loads only when scrolled into view

---

## 12. Progressive Web App (PWA)

- Installable web app — users can **Add to Home Screen** on mobile and desktop
- Web App Manifest generated dynamically from admin settings
- Service Worker caches key assets for faster repeat visits
- Offline fallback page displayed when user has no internet connection (“You’re Offline” message)
- Configurable app name and short name (12 character limit for short name)
- Configurable app description for install prompt
- Theme color and background color for splash screen and browser chrome
- Display mode options: Standalone, Fullscreen, Minimal UI, Browser
- Requires HTTPS and WordPress Site Icon (512×512 recommended)
- Enable/disable PWA from NewsPulse → PWA settings page

---

## 13. Demo Importer — Turnkey Site Setup

### 13.1 One-click import

- **Import Full Demo Now** button creates complete working news site
- Idempotent — safe to re-run without duplicating content destructively
- **Re-run Import (safe)** for refreshing demo after changes
- **Remove Demo** deletes all demo-tagged content, menus, widgets, and users cleanly
- **Fix Missing Images** regenerates local placeholder images if GD extension was missing
- **View Site** shortcut after successful import

### 13.2 What demo import creates

- Homepage set as WordPress front page automatically
- Latest News page set as blog/posts page automatically
- 15+ sample news articles with locally generated featured images
- 5 content categories: Politics, Technology, Business, Sports, Culture
- Videos category with YouTube-linked sample video posts
- Featured and breaking news flags set on selected demo posts
- Primary navigation menu with categories and key pages pre-linked
- Footer navigation menu with company and legal pages
- Sidebar and all four footer widget columns pre-filled with content
- Sample pages: About, Contact, Careers, Advertise, Submit Story, Go Premium, all legal pages
- Demo Reporter user (Zara Malik) and Demo Editor user (Alex Rivera)
- Site title, tagline, theme colors, and site icon configured
- Reading settings (posts per page) configured automatically

---

## 14. NewsPulse Admin Panel — All Settings Tabs

### 14.1 Platform tab — Ads & Integrations

- Performance Score panel at top of page
- Master ad toggle and 8 individual ad zone toggles
- Homepage full-width banner: image or YouTube video with link
- Header date and time bar toggle
- Cookie consent banner and gating settings
- Google Analytics 4 integration settings
- Custom post-consent tracking scripts field
- CAPTCHA provider and key settings
- All performance optimization toggles
- Weather widget city and coordinate settings
- Author photo management link
- Legal pages one-click create button

### 14.2 Homepage tab

- Toggle and text for hero headline section
- Featured carousel toggle and post count
- Category blocks toggle, slug list, and posts per block count
- Mid-page ad placement after category number
- Watch/Videos section toggle, category slug, count, title, and subtitle
- Reader tipline toggle
- Latest posts section toggle and post count
- Homepage sidebar newsletter toggle

### 14.3 Header tab

- Breaking news ticker toggle, labels, and item count
- Logo mark character setting
- Search bar toggle and placeholder texts
- Sign-in/account menu toggle
- Fallback navigation category and page slugs

### 14.4 Footer tab

- Brand tagline and copyright text
- Social media URL fields (X, Facebook, LinkedIn, Instagram)
- Footer category column slug list
- Footer company pages slug list
- Footer newsletter toggle, title, text, and form action URL

### 14.5 Sidebar tab

- Sidebar weather widget toggle
- Fresh Reads (trending) widget toggle, title, and post count
- Topics (categories) widget toggle and category count

### 14.6 Features & Colors tab

- Engagement bar master toggle
- Individual toggles for likes, shares, and view counts on posts
- Excerpt word length setting
- Reading speed (WPM) for reading time calculation
- Custom category color map (slug:hex pairs, one per line)
- Link to Customizer for full font and color palette control

### 14.7 Core Features page

- Enable/disable news submission, view counter, and activity tracking
- Activity email alert settings and webhook/Slack settings
- OneSignal App ID and REST API Key fields
- Send test alert button
- Quick links to Pending Submissions, User Activity, and Reporters list
- REST API base URL reference on page

### 14.8 Other admin pages

- **Pending Submissions** — direct link to pending posts queue
- **User Activity** — full activity log with export
- **Membership** — paywall and premium settings
- **Security Center** — scan, fix, and protection settings
- **Demo Content** — import and remove demo
- **PWA** — installable app settings

---

## 15. WordPress Customizer Integration

- **NewsPulse Design** panel in Appearance → Customize
- Default theme mode: Light or Dark
- Breaking news ticker on/off from Customizer
- Featured carousel on/off from Customizer
- Posts per row on desktop: 2, 3, or 4
- Body font selection from 12 Google Fonts
- Heading font selection from 12 Google Fonts
- Full light mode color palette (10 color controls)
- Full dark mode color palette (4 color controls)
- Large and extra-large border radius controls
- Footer copyright text
- Live preview of all Customizer changes before publishing
- Site Identity section: logo, site icon, site title, tagline

---

## 16. Navigation & Widget Areas

### 16.1 Menu locations

- Primary Menu — main desktop navigation
- Footer Menu — footer link row
- Mobile Menu — optional separate mobile drawer navigation

### 16.2 Widget areas

- Sidebar — main blog sidebar
- Header Widget Area — optional header content
- Homepage Section 1, 2, and 3 — extra homepage blocks
- Footer Column 1, 2, 3, and 4 — footer content columns
- All 8 ad zone widget areas (listed in Section 9)

---

## 17. Page Templates Reference

| Template | Purpose |
|----------|---------|
| Default | Standard page with sidebar |
| Full Width | Wide layout for page builders and landing pages |
| About Us (Fancy) | Styled about page with editorial layout |
| Contact Us | Contact page with built-in contact form |
| Careers | Job listings and careers information page |
| Advertise | Advertising and sponsorship information page |
| My Account | Member dashboard with activity history |
| My Profile | User profile and avatar management page |
| Legal Page | Generic legal content page |
| Terms of Service | Terms and conditions page |
| Cookies Policy | Cookie policy page |
| Cache & Storage | Browser cache and storage policy page |

---

## 18. Shortcodes Reference

| Shortcode | Description |
|-----------|-------------|
| `[neonews_submit_form]` | Full frontend news article submission form |
| `[neonews_account]` | Logged-in member account dashboard |
| `[premium_content]…[/premium_content]` | Content block visible only to premium members |
| `[free_content]…[/free_content]` | Content block visible to all users |
| `[membership_status]` | Displays current user membership status text |

---

## 19. Elementor Page Builder Compatibility

- Fully compatible with Elementor free and Elementor Pro
- Full Width page template recommended for Elementor pages
- Theme header, footer, breaking ticker, and navigation remain active on Elementor pages
- Elementor editor stylesheet included for consistent backend editing experience
- Theme Builder support with Elementor Pro for custom single and archive templates
- Recommended Elementor widgets documented for news grid, category tabs, and featured layouts

---

## 20. Developer & Integration Features

### 20.1 WordPress hooks (actions)

- `neonews_article_submitted` — fires after frontend article submission with post ID and user ID
- `neonews_post_marked_breaking` — fires when post marked as breaking news
- `neonews_user_became_premium` — fires when user gains premium status
- `neonews_user_lost_premium` — fires when user loses premium status

### 20.2 WordPress hooks (filters)

- `neonews_breaking_news_expiry` — customize breaking news auto-expiry hours (default 24)

### 20.3 Helper functions

- `neonews_get_settings()` — retrieve all platform settings array
- `neonews_get_post_views( $post_id )` — get raw view count integer
- `neonews_format_views( $count )` — format views as human string (e.g. “1.2K”)
- `neonews_get_reading_time( $post_id )` — get reading time in minutes
- `neonews_is_user_premium( $user_id )` — check premium membership status
- `neonews_get_performance_report()` — get performance audit array with score and recommendations
- `neonews_apply_performance_recommendations()` — enable all recommended performance toggles

### 20.4 Stored settings options

- `neonews_platform_settings` — all theme and platform display settings
- `neonews_core_settings` — core plugin feature toggles and integration keys
- `neonews_membership_settings` — paywall and membership configuration
- `neonews_security_settings` — security hardening and login protection settings
- `neonews_pwa_settings` — progressive web app configuration

---

## 21. System Requirements

- WordPress 5.9 or higher (tested up to WordPress 6.4)
- PHP 7.4 or higher (PHP 8.1+ recommended for best performance)
- MySQL 5.7+ or MariaDB 10.3+
- HTTPS required for PWA, push notifications, and secure cookie features
- PHP GD extension recommended for demo image generation
- 128 MB PHP memory minimum (256 MB recommended)
- Compatible with standard WordPress hosting (shared, VPS, cloud)

---

## 22. Browser & Device Support

- Google Chrome (latest version)
- Mozilla Firefox (latest version)
- Apple Safari (latest version) including iOS Safari
- Microsoft Edge (latest version)
- Chrome for Android
- Responsive layout tested on phone, tablet, and desktop breakpoints
- Touch gestures supported on carousel and mobile navigation

---

## 23. Recommended Third-Party Integrations

- **OneSignal** — web push notifications (free tier available)
- **Cloudflare Turnstile** — CAPTCHA (free)
- **Google reCAPTCHA or hCaptcha** — CAPTCHA alternatives
- **Google Analytics 4** — traffic analytics (with cookie consent gating)
- **Mailchimp or similar** — newsletter form action URL in footer settings
- **LiteSpeed Cache, WP Rocket, or WP Super Cache** — page caching for maximum speed
- **Cloudflare CDN** — free CDN and DDoS protection
- **Elementor** — visual page builder for custom layouts
- **Slack Incoming Webhook** — activity alert notifications to team channel

---

## 24. Package Components Summary

| Component | Version | Role |
|-----------|---------|------|
| NeoNews Theme | 1.6.1 | Design, homepage, admin UI, performance, CAPTCHA UI |
| NeoNews Core | 1.2.3 | Roles, submission, views, activity, CAPTCHA logic, push, REST API |
| NeoNews Security | 1.0.0 | Scanner, hardening, encryption, login guard |
| NeoNews Membership | 2.0.0 | Activation keys, badges, exclusive content, blur lock, ad-free |
| NeoNews PWA | 1.0.0 | Installable app, service worker, offline page |
| Demo Importer | 1.1.0 | One-click turnkey site setup |

---

## 25. Key Selling Points for Presentation

- **Launch fast** — one-click demo import creates a complete news site in under 2 minutes
- **Edit everything from admin** — no coding required for homepage, header, footer, ads, or features
- **Monetize built-in** — ad zones, membership paywall, newsletter, and Advertise page included
- **Engage readers** — likes, shares, views, push notifications, and breaking news ticker
- **Trust and compliance** — cookie consent, legal page templates, and security scanner included
- **Editorial workflow** — reporter submission, pending review queue, and custom roles
- **Know your audience** — full activity log, CSV export, REST API, and email/Slack alerts
- **Fast by default** — lightweight mode, performance score panel, and smart asset loading on from day one
- **Secure by design** — CAPTCHA, brute-force protection, encrypted keys, and 15+ security checks
- **Future-ready** — PWA installable app, REST API, developer hooks, and Elementor compatibility

---

*Document generated for NewsPulse / NeoNews Platform presentation use.  
For technical setup instructions see [COMPLETE-GUIDE.md](COMPLETE-GUIDE.md) in the docs folder.*
