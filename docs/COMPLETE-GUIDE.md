# NeoNews Platform — Complete Guide

> **One document for everything.** Use Ctrl+F to search for any admin label, feature name, or task.  
> For a shorter path to common tasks, start at [docs/README.md](README.md).

---

## Table of contents

1. [What this platform includes](#what-this-platform-includes)
2. [Installation summary](#installation-summary)
3. [Demo import — turnkey site](#demo-import--turnkey-site)
4. [Recreate demo pages manually](#recreate-demo-pages-manually)
5. [Admin menu reference](#admin-menu-reference)
6. [NewsPulse → Platform settings](#newspulse--platform-settings)
7. [NewsPulse → Core Features](#newspulse--core-features)
8. [NewsPulse → User Activity](#newspulse--user-activity)
9. [NewsPulse → Membership](#newspulse--membership)
10. [NewsPulse → Security Center](#newspulse--security-center)
11. [NewsPulse → Demo Content](#newspulse--demo-content)
12. [NewsPulse → PWA](#newspulse--pwa)
13. [Appearance → Customize](#appearance--customize)
14. [Homepage configuration](#homepage-configuration)
15. [Posts, categories & media](#posts-categories--media)
16. [Page templates](#page-templates)
17. [Menus & navigation](#menus--navigation)
18. [Widgets & sidebars](#widgets--sidebars)
19. [User roles & accounts](#user-roles--accounts)
20. [Login, sign-up & CAPTCHA](#login-sign-up--captcha)
21. [News submission system](#news-submission-system)
22. [Membership & paywall](#membership--paywall)
23. [Engagement features](#engagement-features)
24. [Ads & monetization](#ads--monetization)
25. [Cookie consent & analytics](#cookie-consent--analytics)
26. [Weather widget](#weather-widget)
27. [Performance & speed](#performance--speed)
28. [Push notifications](#push-notifications)
29. [PWA (installable app)](#pwa-installable-app)
30. [Activity monitoring & REST API](#activity-monitoring--rest-api)
31. [Shortcodes](#shortcodes)
32. [Frontend visitor experience](#frontend-visitor-experience)
33. [Developer reference](#developer-reference)
34. [Maintenance checklist](#maintenance-checklist)

---

## What this platform includes

| Package | What it does |
|---------|--------------|
| **neonews-theme** | News site design, homepage layout, header/footer, dark mode, admin settings UI |
| **neonews-core** | User roles, submission, views, breaking news, activity log, CAPTCHA, OneSignal, REST API |
| **neonews-security** | Security scanner, hardening, encrypted API keys, login brute-force protection |
| **neonews-membership** | Free vs premium, paywall, premium post flag, shortcodes |
| **neonews-pwa** | Installable app, service worker, offline page |
| **demo-importer** | One-click demo: pages, menus, widgets, sample articles, users |

**Requirements:** WordPress 5.9+, PHP 7.4+, MySQL 5.7+. HTTPS required for PWA and push notifications.

---

## Installation summary

Detailed steps: [INSTALLATION.md](INSTALLATION.md)

1. Upload and activate **neonews-theme**.
2. Upload and activate **neonews-core** (required).
3. Upload and activate **neonews-security** (recommended).
4. Optionally activate **neonews-membership**, **neonews-pwa**, **demo-importer**.
5. Go to **Settings → Permalinks** → choose **Post name** → Save.
6. Go to **Appearance → Customize → Site Identity** → upload logo and **Site Icon** (512×512 for PWA).
7. Import demo (**NewsPulse → Demo Content**) or configure manually (below).
8. Run **NewsPulse → Security Center → Run Security Scan**.

---

## Demo import — turnkey site

**Path:** **NewsPulse → Demo Content**

### What one click creates

| Item | Details |
|------|---------|
| **Pages** | Home, Latest News (blog), About, Contact, Careers, Advertise, Submit Story, Go Premium, Privacy, Terms, Cookies, Cache & Storage |
| **Reading settings** | Home = front page, Latest News = posts page |
| **Menus** | Main menu (categories + pages), Footer menu |
| **Widgets** | Sidebar + 4 footer columns filled |
| **Content** | 15+ articles with featured images, 5 categories |
| **Users** | Demo reporter + demo editor |
| **Theme** | Site title, tagline, colors, site icon |

### Demo buttons

| Button | Action |
|--------|--------|
| **Import Full Demo Now** | First import |
| **Re-run Import (safe)** | Idempotent — safe to run again |
| **Remove Demo** | Deletes all content tagged as demo |
| **Fix Missing Images** | Regenerates local placeholder images (needs PHP GD) |
| **View Site** | Opens frontend after import |

### Demo categories

Politics, Technology, Business, Sports, Culture (slug: `entertainment`), Videos

---

## Recreate demo pages manually

If you do not use the demo importer, create these pages yourself.

### Step 1 — Create pages

Go to **Pages → Add New** for each row:

| Page title | Slug (suggested) | Template | Page content |
|------------|------------------|----------|--------------|
| Home | `home` | *(default — uses theme front-page.php)* | Leave empty or add intro text |
| Latest News | `latest-news` | Default | Leave empty (becomes blog index) |
| About Us | `about` | **About Us (Fancy)** | Your about copy |
| Contact Us | `contact` | **Contact Us** | Optional intro; form is built into template |
| Careers | `careers` | **Careers** | Job listings content |
| Advertise | `advertise` | **Advertise** | Advertising info |
| Submit Story | `submit-story` | Default | `[neonews_submit_form]` |
| Go Premium | `go-premium` | Default | Membership marketing copy |
| Privacy Policy | `privacy-policy` | **Legal Page** | Policy text (or use auto-create button) |
| Terms of Service | `terms-of-service` | **Terms of Service** | Terms text |
| Cookies Policy | `cookies-policy` | **Cookies Policy** | Cookie policy text |
| Cache & Storage | `cache-storage` | **Cache & Storage** | Cache policy text |
| My Account | `my-account` | **My Account** | `[neonews_account]` |
| My Profile | `my-profile` | **My Profile** | Leave empty (profile UI in template) |

**Auto-create legal pages:** **NewsPulse → Platform** (bottom) → **Create / Update Legal Pages**.

### Step 2 — Set homepage

1. **Settings → Reading**
2. **Your homepage displays:** A static page
3. **Homepage:** Home
4. **Posts page:** Latest News
5. Save

### Step 3 — Create categories

**Posts → Categories** — add at least:

- politics, business, technology, sports, entertainment, videos

Use slugs matching **NewsPulse → Homepage → Category slugs** setting.

### Step 4 — Create menus

**Appearance → Menus**

**Primary Menu** (assign to **Primary Menu**):
- Home, category links, Submit Story, About, Contact

**Footer Menu** (assign to **Footer Menu**):
- About, Careers, Advertise, Contact, Privacy, Terms

**Mobile Menu** (optional, assign to **Mobile Menu**):
- Simplified mobile links

If no menu is assigned, the theme uses **fallback category/page slugs** from **NewsPulse → Header**.

### Step 5 — Add widgets

**Appearance → Widgets** — add blocks to:
- **Sidebar** — Search, Recent Posts, etc.
- **Footer Column 1–4** — About text, links, categories, newsletter
- **Ad zones** — Custom HTML or ad network code (see [Ads](#ads--monetization))

---

## Admin menu reference

| Menu item | Opens | Capability |
|-----------|-------|------------|
| **Platform** | Theme settings (6 tabs) | Administrator |
| **Core Features** | Submission, views, activity, OneSignal, alerts | Administrator |
| **Pending Submissions** | Posts with status Pending | Editor+ |
| **User Activity** | Activity log | Administrator |
| **Membership** | Paywall settings | Administrator |
| **Security Center** | Scanner & hardening | Administrator |
| **Demo Content** | Import/remove demo | Administrator |
| **PWA** | App manifest settings | Administrator |

---

## NewsPulse → Platform settings

**Path:** **NewsPulse → Platform**  
**Save:** Click **Save Changes** at bottom of each tab.

Tabs: **Ads & Integrations** | **Homepage** | **Header** | **Footer** | **Sidebar** | **Features & Colors**

---

### Performance Score panel (top of Ads & Integrations tab)

Shows score 0–100, grade, estimated KB/requests saved, and TTI improvement.

- **Apply N recommended fixes** — enables all disabled performance toggles in one click.
- **Active optimizations** — list of what's on.
- **Recommendations** — jump links to settings below.

See [PERFORMANCE.md](PERFORMANCE.md) for full details.

---

### Tab: Ads & Integrations

#### Advertisements

| Setting | What it does |
|---------|--------------|
| **Enable all ads** | Master switch — off hides every ad zone |
| **Individual ad zones** | Toggle each placement (see [Ads](#ads--monetization)) |

#### Homepage full-width banner

| Setting | What it does |
|---------|--------------|
| **Enable banner** | Full-width strip below header on homepage |
| **Banner type** | Image or YouTube video |
| **Banner image ID** | Media Library attachment ID |
| **YouTube URL** | Video URL if type is video |
| **Banner link** | Optional click-through URL |

#### Header bar

| Setting | What it does |
|---------|--------------|
| **Date & time bar** | Live clock in header |

#### Cookie consent banner

| Setting | What it does |
|---------|--------------|
| **Enable banner** | Shows cookie consent bar |
| **Gate ad zones** | Ads load only after consent |
| **Gate statistics** | View/share stats only after consent |
| **Google Analytics** | Enable GA4 |
| **GA Measurement ID** | e.g. `G-XXXXXXXXXX` |
| **Custom scripts** | Extra tracking scripts (loaded after consent) |

#### Login & signup CAPTCHA

| Setting | What it does |
|---------|--------------|
| **Enable CAPTCHA** | Master switch |
| **Provider** | Turnstile, reCAPTCHA v2/v3, hCaptcha |
| **Site key / Secret key** | From provider dashboard |
| **Protect login** | Modal + wp-login.php |
| **Protect registration** | Sign-up tab + wp-login.php register |
| **reCAPTCHA v3 threshold** | Score 0.1–1.0 (default 0.5) |

Secret key can be encrypted if **Security Center → Encrypt API keys** is on.

#### Performance (lightweight mode)

| Setting | What it does |
|---------|--------------|
| **Lightweight mode** | Master performance switch |
| **System fonts** | Skip Google Fonts (fastest) |
| **Disable emoji scripts** | Removes WP emoji JS/CSS |
| **Disable wp-embed.js** | Removes oEmbed helper |
| **Disable dashicons for visitors** | Removes admin icon font on frontend |
| **Remove block editor CSS** | Dequeues block library styles |
| **Disable Heartbeat on frontend** | Stops AJAX polling on public pages |
| **Conditional plugin assets** | Core JS/CSS only on posts/submission pages |
| **Lazy CAPTCHA** | CAPTCHA loads when sign-in modal opens |
| **Reduce scroll/motion animations** | Less animation work |

#### Weather widget

| Setting | What it does |
|---------|--------------|
| **Enable weather** | Header chip + sidebar widget |
| **Default city** | City name (geocoded via Open-Meteo) |
| **Latitude / Longitude** | Optional precise location |

#### Author photos & legal pages (below form)

- **Manage Users** — link to user list for author photos
- **Create / Update Legal Pages** — creates Privacy, Terms, Cookies, Cache pages

---

### Tab: Homepage

| Setting | What it does |
|---------|--------------|
| **Hero headline** | Large headline block on homepage |
| **Hero title / accent / subtitle** | Text lines |
| **Featured carousel** | Rotating featured posts |
| **Carousel post count** | Number of slides (default 6) |
| **Category blocks** | Grid sections per category |
| **Category slugs** | Comma-separated slugs (e.g. `business,technology,sports`) |
| **Posts per category** | Posts shown per block |
| **Mid-page ad after category #** | Inserts ad after Nth category block |
| **Watch / Videos** | YouTube video grid section |
| **Video category slug** | Usually `videos` |
| **Video count** | Number of video cards |
| **Watch section title / subtitle** | Section headings |
| **Reader tipline** | "Have a tip?" call-to-action |
| **Latest posts** | Bottom news grid |
| **Latest post count** | Number of posts |
| **Sidebar newsletter** | Newsletter box in homepage sidebar |

---

### Tab: Header

| Setting | What it does |
|---------|--------------|
| **Date & time bar** | Same as platform tab |
| **Breaking news ticker** | Scrolling breaking headlines |
| **Breaking labels** | LIVE and Breaking tag text |
| **Breaking item count** | Max items in ticker |
| **Logo mark** | Single character if no custom logo uploaded |
| **Search bar** | Header search field |
| **Search placeholders** | Desktop and mobile placeholder text |
| **Sign-in / account menu** | Auth buttons and account dropdown |
| **Fallback menu categories** | Slugs used when no Primary Menu assigned |
| **Fallback menu pages** | Page slugs for fallback nav |

---

### Tab: Footer

| Setting | What it does |
|---------|--------------|
| **Brand tagline** | Text under logo |
| **Copyright text** | Footer copyright line |
| **Social links** | X, Facebook, LinkedIn, Instagram URLs |
| **Sections column** | Category slugs for footer column |
| **Company column** | Page slugs (about, careers, etc.) |
| **Footer newsletter** | Newsletter signup block |
| **Newsletter title / text** | Copy for signup |
| **Form action URL** | Mailchimp or external form endpoint |

---

### Tab: Sidebar

| Setting | What it does |
|---------|--------------|
| **Weather widget** | Sidebar weather card |
| **Fresh Reads** | Trending posts widget |
| **Fresh Reads title / count** | Widget label and post count |
| **Topics** | Category pill list |
| **Topics count** | Number of categories shown |

---

### Tab: Features & Colors

| Setting | What it does |
|---------|--------------|
| **Engagement bar** | Like/share/view bar on single posts |
| **Post likes / shares / views** | Toggle each metric |
| **Excerpt length** | Words in excerpts |
| **Reading speed (WPM)** | Used for "X min read" |
| **Custom colors** | One per line: `slug:#hex` for category colors |

Fonts and full color palette: **Appearance → Customize → NewsPulse Design**.

---

## NewsPulse → Core Features

**Path:** **NewsPulse → Core Features**

### General Settings

| Setting | What it does |
|---------|--------------|
| **Enable News Submission** | Frontend `[neonews_submit_form]` works |
| **Enable View Counter** | Tracks post views (bot-filtered) |
| **Enable Activity Tracking** | Logs user events to activity table |

### Activity Alerts

| Setting | What it does |
|---------|--------------|
| **Email Suspicious Activity Alerts** | Sends email on anomalies |
| **Alert Email** | Recipient address |
| **Alert on Administrator Login** | Email when admin logs in |
| **Enable Webhook / Slack Alerts** | POST to webhook URL |
| **Webhook Type** | Generic JSON or Slack Incoming Webhook |
| **Webhook URL** | Destination URL |

**Send test alert** button — verifies email + webhook without waiting for a real event.

### OneSignal Push Notifications

| Setting | What it does |
|---------|--------------|
| **OneSignal App ID** | From OneSignal dashboard |
| **OneSignal REST API Key** | Server-side API key |

See [PUSH-NOTIFICATIONS.md](PUSH-NOTIFICATIONS.md).

### Post editor — NeoNews Options meta box

| Field | What it does |
|-------|--------------|
| **Featured Post** | Appears in homepage carousel |
| **Breaking News** | Appears in ticker; auto-expires 24h; can trigger push |
| **Send Push Notification** | Sends OneSignal notification on publish |

### Post editor — Post Statistics meta box

Shows total views, 7-day views, submission source/date.

### Posts list columns

**Views**, **Flags** (★ featured, ⚡ breaking, 📝 submission)

---

## NewsPulse → User Activity

**Path:** **NewsPulse → User Activity**

### Filters

- User, event type, date range, search
- **Filter**, **Reset**, **Export CSV**

### Event types logged

Login, Logout, Read article, Submitted article, Profile updated, Profile photo, Liked article, Shared article, Posted comment, Admin action

### Dashboard widgets

- **NewsPulse Activity Overview** — 7-day stats
- **Recent User Activity** — latest events

### User profile

**Users → Edit User** — scroll to **NewsPulse Activity** for last 10 events for that user.

---

## NewsPulse → Membership

**Path:** **NewsPulse → Membership** (plugin v2.0.0)

| Tab | What it does |
|-----|--------------|
| **General** | Paywall, ad-free premium, free limits, exclusive slug, key defaults |
| **Badges & Timer** | Basic/Premium labels, badge visibility, expiry reminder |
| **Activation Keys** | Generate/revoke keys, set premium duration |
| **Plan & Contact** | Plan copy, WhatsApp/email enrollment templates |
| **Content & Lock** | Blur lock preview for basic users |
| **Popup & Home** | Upgrade popup + homepage Premium Picks |
| **Payment Gateway** | Stripe keys (encrypted via Security Center) |

### Mark premium posts

Edit post → **Premium Content** → check **Premium / exclusive post** and optionally **Show in homepage Premium Picks**.

### Grant premium

| Method | Steps |
|--------|-------|
| **Activation key** | Membership → Activation Keys → generate → user redeems on Profile |
| **Manual** | Users → Edit User → Premium Status + expiry |

See [USERS-AUTH-MEMBERSHIP.md](USERS-AUTH-MEMBERSHIP.md) for full workflow.

---

## NewsPulse → Security Center

**Path:** **NewsPulse → Security Center**

| Action | What it does |
|--------|--------------|
| **Run Security Scan** | Checks 15+ security items including membership monetization |
| **Fix All (Auto)** | Applies automated fixes |
| **Fix automatically** (per finding) | Single-item fix |
| **AI Threat Analysis** | Prioritized recommendations |

### Protection Settings

| Setting | What it does |
|---------|--------------|
| **Encrypt API keys** | AES-256 for CAPTCHA, OneSignal, cookie scripts, Membership Stripe secrets |
| **Send security headers** | HSTS, X-Frame-Options, etc. |
| **Block XML-RPC** | Disables XML-RPC endpoint |
| **Hide WordPress version** | Removes version from HTML |
| **Disable theme/plugin file editor** | Disables file editor in admin |
| **Block user enumeration** | Blocks `?author=` probing |
| **Block PHP in uploads** | Hardens uploads directory |
| **Login brute-force protection** | Rate limits failed logins |
| **Force secure auth cookies** | Requires HTTPS for cookies |
| **Email alerts for lockouts** | Notifies on lockout |
| **Max login attempts / Lockout duration** | Thresholds |
| **Alert email** | Lockout notification address |

Full details: [SECURITY.md](SECURITY.md).

---

## NewsPulse → Demo Content

See [Demo import](#demo-import--turnkey-site) above.

---

## NewsPulse → PWA

| Setting | What it does |
|---------|--------------|
| **Enable PWA** | Manifest + service worker |
| **App Name / Short Name** | Install prompt labels (short max 12 chars) |
| **Description** | App store-style description |
| **Theme Color / Background Color** | Splash screen colors |
| **Display Mode** | Standalone, fullscreen, minimal-ui, browser |

**Requirements:** HTTPS, Site Icon set, clear browser cache after changes.

Offline page shows **"You're Offline"** when network unavailable.

---

## Appearance → Customize

**Path:** **Appearance → Customize → NewsPulse Design**

| Section | Controls |
|---------|----------|
| **Layout & Features** | Default theme mode (light/dark), breaking ticker, featured carousel, posts per row (2/3/4) |
| **Typography** | Body font, heading font (12 Google Font choices) |
| **Colors (Light Mode)** | Primary, secondary, accent, backgrounds, text, borders |
| **Colors (Dark Mode)** | Background and text overrides |
| **Layout** | Border radius sizes |
| **Footer Copyright** | Copyright text |

**Site Identity** (separate section): Logo, site icon, title, tagline.

---

## Homepage configuration

The theme uses `front-page.php` when **Settings → Reading** points to a static Home page.

### Built-in homepage sections (top to bottom)

1. Breaking news ticker (header)
2. Full-width banner (optional)
3. Hero headline
4. Featured carousel
5. Category blocks (with optional mid-page ad)
6. Watch / Videos section
7. Reader tipline
8. Latest posts grid + sidebar (weather, fresh reads, topics, newsletter, ads)

Toggle each in **NewsPulse → Homepage**.

### Using Elementor instead

See [ELEMENTOR.md](ELEMENTOR.md). Theme header/footer/ticker remain theme-controlled.

---

## Posts, categories & media

### Create a post

1. **Posts → Add New**
2. Title, content, excerpt
3. **Featured image** — set for cards and push notifications
4. **Categories** — assign at least one
5. **NeoNews Options** — Featured, Breaking, Push (optional)
6. **YouTube Video** meta box — URL + duration for video section
7. **Premium Content** — if membership plugin active
8. Publish

### Categories

**Posts → Categories** — Name, slug, description (SEO).

Match slugs to homepage/footer settings.

### Featured posts

Check **Featured Post** in NeoNews Options → appear in carousel (homepage).

### Breaking news

Check **Breaking News** → ticker + optional push with "BREAKING:" prefix. Expires after 24 hours.

### Video posts

Add YouTube URL in post meta → appears in **Watch** section if category matches **Video category slug**.

---

## Page templates

Select under **Page Attributes → Template** when editing a page.

| Template | Use for |
|----------|---------|
| **About Us (Fancy)** | About page with styled layout |
| **Advertise** | Advertising information |
| **Careers** | Jobs page |
| **Contact Us** | Contact form (built-in) |
| **Full Width** | No sidebar; good for Elementor |
| **My Account** | Member dashboard — use `[neonews_account]` |
| **My Profile** | Profile photo and settings |
| **Legal Page** | Generic legal content |
| **Terms of Service** | Terms layout |
| **Cookies Policy** | Cookie policy layout |
| **Cache & Storage** | Cache policy layout |

---

## Menus & navigation

**Appearance → Menus**

| Location | Purpose |
|----------|---------|
| **Primary Menu** | Desktop main navigation |
| **Footer Menu** | Footer links |
| **Mobile Menu** | Mobile drawer (optional) |

Without a Primary Menu, theme builds nav from **fallback category/page slugs** in **NewsPulse → Header**.

---

## Widgets & sidebars

**Appearance → Widgets**

| Area | Purpose |
|------|---------|
| **Sidebar** | Blog/archive sidebar |
| **Header Widget Area** | Optional header widgets |
| **Homepage Section 1–3** | Extra homepage blocks |
| **Footer Column 1–4** | Footer columns |
| **Ad zones** | See ads section |

Built-in sidebar blocks (no WP widget needed): Weather, Fresh Reads, Topics — controlled in **NewsPulse → Sidebar**.

---

## User roles & accounts

| Role | Can do |
|------|--------|
| **Administrator** | Everything |
| **Editor** | Edit/publish all posts, approve submissions |
| **Author** | Publish own posts, submit |
| **Reporter** (`nn_reporter`) | Submit posts (pending review), upload media |
| **Premium Subscriber** (`nn_premium_subscriber`) | Read premium content |
| **Subscriber** | Read, comment, use account pages |

### Create a reporter

**Users → Add New** → Role: **Reporter**

### Author photos

**Users → Edit User** → **Author Photo** — upload or URL.

### Frontend account

Logged-in users see **My Activity**, **Profile**, **Sign Out** in header.

Pages: **My Account** (`[neonews_account]`), **My Profile** template.

---

## Login, sign-up & CAPTCHA

### Sign-in modal

Header **Sign In** opens modal with:
- **Sign In** tab — username/email + password
- **Sign Up** tab — registration fields
- Forgot password → `wp-login.php?action=lostpassword`

Works via AJAX (`neonews_ajax_login`, `neonews_ajax_register`).

### Enable CAPTCHA

**NewsPulse → Platform → Login & signup CAPTCHA**

1. Enable CAPTCHA
2. Choose provider, enter keys
3. Check protect login / registration
4. Save

Providers: Cloudflare Turnstile, reCAPTCHA v2/v3, hCaptcha.

With **Lazy CAPTCHA** on, scripts load only when modal opens.

---

## News submission system

### Setup

1. **NewsPulse → Core Features** → enable **Enable News Submission**
2. Create page with `[neonews_submit_form]` (demo: Submit Story)
3. Add page to Primary Menu

### Who can submit

Logged-in users with role: Reporter, Author, Editor, or Administrator.

### Workflow

1. Reporter submits → post status **Pending**
2. **NewsPulse → Pending Submissions** (or **Posts → Pending**)
3. Editor reviews, edits, publishes

Required: title, content, category. Optional: featured image, excerpt.

---

## Membership & paywall

Full reference: [USERS-AUTH-MEMBERSHIP.md](USERS-AUTH-MEMBERSHIP.md)

1. Activate **neonews-membership** (v2.0)
2. **NewsPulse → Membership** — configure tabs (keys, badges, plan, lock, popup)
3. Generate activation keys or grant premium manually
4. Mark posts as **Premium / exclusive** (+ optional homepage flag)
5. Exclusive page auto-created at `/exclusive/`

### PHP check

```php
if ( neonews_is_user_premium() ) {
    // show premium content
}
```

### Hooks

```php
do_action( 'neonews_activation_key_redeemed', $user_id, $key_code, $duration_days );
```

---

## Engagement features

On single posts (when enabled in **Features & Colors**):

| Feature | Behavior |
|---------|----------|
| **Likes** | Logged-in users toggle like (AJAX) |
| **Shares** | X, Facebook, LinkedIn, copy link — counts tracked |
| **Views** | Auto-increment with bot filtering |
| **Reading time** | Based on WPM setting |

Activity logged when tracking enabled.

---

## Ads & monetization

### Enable ads

**NewsPulse → Platform** → **Enable all ads** + individual zones.

### Ad widget areas

Add **Custom HTML** or ad network code in **Appearance → Widgets**:

| Zone | Placement |
|------|-----------|
| Header Ad Banner | Top of site |
| Home — Below Hero | After carousel |
| Home — Between Categories | Mid homepage |
| Home — Before Videos | Before Watch section |
| In-Content Ad | Within homepage content |
| Home — Sidebar / Rail | Homepage sidebar |
| Home — Footer Area | Bottom of homepage |
| Mobile Sticky Ad | Fixed bottom on mobile |

### Cookie gating

If **Gate ad zones** is on, ads load only after **Accept all cookies**.

---

## Cookie consent & analytics

Banner text: **"We use cookies"** with **Essential only** / **Accept all cookies**.

| Consent level | Loads |
|---------------|-------|
| Essential only | Core site only |
| Accept all | Ads (if gated), statistics, GA4, custom scripts |

Configure in **NewsPulse → Platform → Cookie consent banner**.

---

## Weather widget

Uses **Open-Meteo** (free, no API key).

Shows in header chip and/or sidebar when enabled.

Set city name or lat/lon in **NewsPulse → Platform → Weather widget**.

Disable for better performance (shown in Performance recommendations).

---

## Performance & speed

See [PERFORMANCE.md](PERFORMANCE.md).

Quick wins:
1. Open **NewsPulse → Platform** → check Performance Score
2. Click **Apply recommended fixes**
3. Enable **System fonts**
4. Install page cache plugin (LiteSpeed Cache, WP Rocket)
5. Optimize images (WebP)

---

## Push notifications

See [PUSH-NOTIFICATIONS.md](PUSH-NOTIFICATIONS.md).

Summary:
1. Create OneSignal app
2. Enter App ID + REST key in **NewsPulse → Core Features**
3. Check **Send Push Notification** on post or mark **Breaking News**

---

## PWA (installable app)

See [NewsPulse → PWA](#newspulse--pwa).

Users on mobile/desktop can **Add to Home Screen**. Requires HTTPS + Site Icon.

---

## Activity monitoring & REST API

### REST endpoints (admin auth required)

Base: `/wp-json/neonews/v1/`

| Method | Route | Purpose |
|--------|-------|---------|
| GET | `/activity` | Paginated log (filters: user_id, event_type, search, dates) |
| GET | `/activity/stats` | Stats (`?days=7`) |
| GET | `/activity/types` | Event type labels |
| GET | `/activity/{id}` | Single entry |

Authenticate with WordPress admin cookie or Application Password.

Details: [MONITORING-AND-API.md](MONITORING-AND-API.md).

---

## Shortcodes

| Shortcode | Description |
|-----------|-------------|
| `[neonews_submit_form]` | Article submission form |
| `[neonews_account]` | Account dashboard |
| `[premium_content]…[/premium_content]` | Premium-only block |
| `[free_content]…[/free_content]` | Free content block |
| `[membership_status]` | User membership status |

---

## Frontend visitor experience

| Feature | Where |
|---------|-------|
| Dark / light mode | Header toggle — persists in cookie or user meta |
| Search | Header — searches posts |
| Breaking ticker | Top of site |
| Sign-in modal | Header |
| Weather | Header chip + sidebar |
| Cookie banner | Bottom of screen |
| Newsletter | Footer + homepage sidebar |
| Contact form | Contact Us page |
| Offline page | PWA when no network |
| Scroll animations | Homepage cards (reduced if perf setting on) |

---

## Developer reference

### Actions

```php
do_action( 'neonews_article_submitted', $post_id, $user_id );
do_action( 'neonews_post_marked_breaking', $post_id, $post );
do_action( 'neonews_user_became_premium', $user_id );
do_action( 'neonews_user_lost_premium', $user_id );
```

### Filters

```php
add_filter( 'neonews_breaking_news_expiry', function( $hours ) {
    return 48;
} );
```

### Helper functions

```php
neonews_get_settings();           // Platform settings array
neonews_get_post_views( $id );    // View count
neonews_format_views( $count );   // "1.2K"
neonews_get_reading_time( $id );  // Minutes
neonews_is_user_premium( $user_id );
neonews_get_performance_report(); // Performance audit array
```

### Options stored in database

| Option name | Contents |
|-------------|----------|
| `neonews_platform_settings` | Theme/platform settings |
| `neonews_core_settings` | Core plugin settings |
| `neonews_membership_settings` | Membership settings |
| `neonews_security_settings` | Security settings |
| `neonews_pwa_settings` | PWA settings |

---

## Maintenance checklist

### Weekly
- [ ] Review **Pending Submissions**
- [ ] Check **User Activity** for unusual patterns
- [ ] Verify breaking news expired (auto after 24h)

### Monthly
- [ ] Update WordPress, theme, plugins
- [ ] Run **Security Scan**
- [ ] Review Performance Score
- [ ] Backup database

### After major changes
- [ ] Clear page cache
- [ ] Test sign-in, submission, paywall
- [ ] Test mobile menu and PWA install

---

*For troubleshooting, see [TROUBLESHOOTING.md](TROUBLESHOOTING.md). For the documentation index, see [README.md](README.md).*
