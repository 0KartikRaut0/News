# Frontend Features

Everything visitors see and interact with on the public site.

---

## Header

| Feature | Setting location | Behavior |
|---------|------------------|----------|
| **Date & time bar** | Platform / Header | Live clock |
| **Breaking news ticker** | Header | Scrolling headlines from Breaking posts |
| **Logo** | Customize → Site Identity | Custom logo or **Logo mark** character |
| **Primary navigation** | Appearance → Menus | Desktop nav |
| **Search** | Header | Searches posts; ESC to close |
| **Dark / light toggle** | Always on | Persists via cookie (guests) or user meta |
| **Weather chip** | Platform → Weather | Current conditions via Open-Meteo |
| **Sign In / Account** | Header | Modal auth or account dropdown with plan badge on avatar |
| **Social & WhatsApp** | Footer tab | Header bar icons; WhatsApp on Contact, Advertise, Careers |

---

## Dark mode

### Default mode

**Appearance → Customize → NewsPulse Design → Default Theme Mode**

### How persistence works

| User type | Storage |
|-----------|---------|
| Logged in | User meta |
| Guest | Browser cookie |

Toggle in header — instant switch, no page reload required (AJAX).

### Customize dark colors

**Customize → NewsPulse Design → Colors (Dark Mode)**

Or edit CSS variables in `style.css` under `[data-theme="dark"]`.

---

## Search

- Icon/field in header
- Full-screen or overlay search UI
- Searches **posts** only by default
- Placeholders: **NewsPulse → Header → Search placeholders**

To include pages, add filter (see [SETUP.md](SETUP.md)).

---

## Homepage sections

Controlled in **NewsPulse → Homepage**. See [COMPLETE-GUIDE.md#homepage-configuration](COMPLETE-GUIDE.md#homepage-configuration).

Visual order:
1. Optional full-width banner (image or YouTube)
2. Hero headline
3. Featured carousel
4. Category grids
5. Mid-page ad slot
6. Watch / Videos
7. Reader tipline
8. Latest posts + sidebar

---

## Single post page

| Feature | Setting |
|---------|---------|
| **Engagement bar** | Features & Colors → Engagement bar |
| **Likes** | Features & Colors → Post likes |
| **Shares** | Features & Colors → Post shares (X, Facebook, LinkedIn, copy) |
| **View count** | Features & Colors → View counts |
| **Reading time** | Features & Colors → Reading speed (WPM) |
| **Comments** | WordPress comments (if enabled) |
| **Premium paywall** | Membership plugin — blur lock + exclusive page |
| **Plan badges** | Basic/Premium on avatar; tags on Profile & My Activity |
| **Premium Picks** | Homepage section for flagged exclusive posts |

---

## Membership UI (logged-in users)

| Location | What visitors see |
|----------|-------------------|
| Header avatar | Basic or Premium pill; red ring if expiring |
| My Profile | Plan tag, enroll panel, activation key form |
| My Activity | Plan tag, enroll panel, expiry countdown |
| Exclusive page | Locked card grid for basic users |
| Single premium post | Blurred preview + lock for basic users |

Configure under **NewsPulse → Membership**. See [USERS-AUTH-MEMBERSHIP.md](USERS-AUTH-MEMBERSHIP.md).

---

## Sign-in modal

- Opens from **Sign In** button
- Tabs: Sign In / Sign Up
- CAPTCHA when configured
- Forgot password link

See [USERS-AUTH-MEMBERSHIP.md](USERS-AUTH-MEMBERSHIP.md).

---

## Cookie consent

Banner at bottom: **"We use cookies"**

| Button | Effect |
|--------|--------|
| **Essential only** | Core functionality only |
| **Accept all cookies** | Ads, statistics, GA4, custom scripts |

Settings: **NewsPulse → Platform → Cookie consent banner**

Gating options:
- **Gate ad zones**
- **Gate statistics** (views/shares tracking)
- **Google Analytics** + Measurement ID
- **Custom scripts** (loaded after consent)

---

## Advertisements

8 widget-based zones + mobile sticky. Master toggle: **Enable all ads**.

Ads respect cookie consent when **Gate ad zones** is on.

Add ad code: **Appearance → Widgets** → ad zone → Custom HTML block.

---

## Weather

- **Header chip** — compact temperature/conditions
- **Sidebar widget** — expanded card

Data from Open-Meteo (no API key). Configure city or coordinates in **NewsPulse → Platform → Weather**.

Disable sidebar/header independently in **NewsPulse → Sidebar** and Platform settings.

---

## Newsletter forms

Two locations:
1. **Homepage sidebar** — **NewsPulse → Homepage → Sidebar newsletter**
2. **Footer** — **NewsPulse → Footer → Footer newsletter**

Optional **Form action URL** for Mailchimp/external service.

---

## Contact form

**Contact Us** page template includes built-in form.

Submits via `admin_post_neonews_contact_form` — delivers to admin email.

---

## Mobile experience

- Responsive layout (mobile-first CSS)
- **Mobile Menu** location (optional separate menu)
- **Mobile sticky ad** zone at bottom
- Touch-friendly carousel swipe
- PWA install prompt (if PWA enabled)

---

## PWA / Add to Home Screen

When **NewsPulse → PWA** enabled + HTTPS + Site Icon:
- Web app manifest
- Service worker caches assets
- Offline fallback page
- Installable on phone/desktop

---

## Accessibility & motion

- **Reduce scroll/motion animations** — **NewsPulse → Platform → Performance**
- Respects `prefers-reduced-motion` in browser when enabled
- Keyboard: carousel arrow keys, ESC closes search

---

## Toast notifications

Short success/error messages for AJAX actions (likes, copy link, etc.) — built into theme JS.

---

## Related docs

- [PERFORMANCE.md](PERFORMANCE.md) — speed
- [USERS-AUTH-MEMBERSHIP.md](USERS-AUTH-MEMBERSHIP.md) — auth
- [COMPLETE-GUIDE.md](COMPLETE-GUIDE.md) — all settings
