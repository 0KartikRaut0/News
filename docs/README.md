# NeoNews / NewsPulse — Documentation Hub

Use this folder whenever you are stuck. Start with the **[Complete Guide](COMPLETE-GUIDE.md)** — it covers every feature, admin screen, page template, and common task step by step.

---

## Quick links (most common tasks)

| I want to… | Go to |
|------------|--------|
| Install the site from scratch | [INSTALLATION.md](INSTALLATION.md) → [GETTING-STARTED.md](GETTING-STARTED.md) |
| Import the full demo in one click | [GETTING-STARTED.md](GETTING-STARTED.md#demo-import) |
| Recreate demo pages manually | [PAGES-AND-TEMPLATES.md](PAGES-AND-TEMPLATES.md) |
| Configure homepage sections | [COMPLETE-GUIDE.md#homepage](COMPLETE-GUIDE.md#homepage-configuration) |
| Add posts, categories, featured/breaking | [CONTENT-MANAGEMENT.md](CONTENT-MANAGEMENT.md) |
| Set up user roles & reporter submissions | [USERS-AUTH-MEMBERSHIP.md](USERS-AUTH-MEMBERSHIP.md) |
| Enable CAPTCHA on login/signup | [USERS-AUTH-MEMBERSHIP.md#captcha](USERS-AUTH-MEMBERSHIP.md#login-signup--captcha) |
| Turn on paywall / premium content | [USERS-AUTH-MEMBERSHIP.md#membership](USERS-AUTH-MEMBERSHIP.md#membership--paywall) |
| Generate premium activation keys | [USERS-AUTH-MEMBERSHIP.md#membership](USERS-AUTH-MEMBERSHIP.md#membership--paywall) → Activation keys |
| Configure exclusive news page | [PAGES-AND-TEMPLATES.md](PAGES-AND-TEMPLATES.md) |
| View user activity & send alerts | [MONITORING-AND-API.md](MONITORING-AND-API.md) |
| Run security scan & hardening | [SECURITY.md](SECURITY.md) |
| Improve speed / performance score | [PERFORMANCE.md](PERFORMANCE.md) |
| Set up push notifications | [PUSH-NOTIFICATIONS.md](PUSH-NOTIFICATIONS.md) |
| Build pages with Elementor | [ELEMENTOR.md](ELEMENTOR.md) |
| Fix something broken | [TROUBLESHOOTING.md](TROUBLESHOOTING.md) |

---

## Full documentation index

### Master reference
- **[COMPLETE-GUIDE.md](COMPLETE-GUIDE.md)** — Everything in one document (admin menus, settings, templates, shortcodes, API, frontend features)
- **[PRESENTATION-FEATURES.md](PRESENTATION-FEATURES.md)** — Full feature list in bullet points for client presentations (convert to PDF)

### Setup & structure
- [INSTALLATION.md](INSTALLATION.md) — Server requirements, upload theme/plugins, permalinks
- [GETTING-STARTED.md](GETTING-STARTED.md) — First 30 minutes: demo, menus, reading settings, checklist
- [SETUP.md](SETUP.md) — Original setup guide (categories, dark mode, featured posts, sidebar)
- [PAGES-AND-TEMPLATES.md](PAGES-AND-TEMPLATES.md) — All page templates, creating pages like the demo, menus, widgets

### Content & users
- [CONTENT-MANAGEMENT.md](CONTENT-MANAGEMENT.md) — Posts, categories, YouTube videos, submission workflow
- [USERS-AUTH-MEMBERSHIP.md](USERS-AUTH-MEMBERSHIP.md) — Roles, sign-in modal, CAPTCHA, paywall, premium users

### Platform features
- [MONITORING-AND-API.md](MONITORING-AND-API.md) — Activity log, email/Slack alerts, REST API, dashboard widgets
- [PERFORMANCE.md](PERFORMANCE.md) — Performance score panel, lightweight mode, optimization checklist
- [FRONTEND-FEATURES.md](FRONTEND-FEATURES.md) — Dark mode, search, weather, ads, cookies, engagement bar
- [PUSH-NOTIFICATIONS.md](PUSH-NOTIFICATIONS.md) — OneSignal setup and usage
- [SECURITY.md](SECURITY.md) — Security Center, encryption, login guard, hardening
- [ELEMENTOR.md](ELEMENTOR.md) — Page builder integration

### Help
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) — Common problems and fixes

---

## Admin menu map (WordPress sidebar)

All NeoNews settings live under **NewsPulse** (unless theme is inactive — then Demo appears as **NeoNews Demo**).

```
NewsPulse
├── Platform              → Ads, CAPTCHA, performance, weather, legal pages
├── Core Features         → Submission, views, activity, OneSignal, alerts
├── Pending Submissions   → Posts awaiting approval
├── User Activity         → Activity log & CSV export
├── Membership            → Paywall, activation keys, badges, exclusive content
├── Security Center       → Scan, hardening, encrypted keys
├── Demo Content          → Import / remove demo
└── PWA                   → Progressive Web App settings
```

**Also used often:**
- **Appearance → Customize → NewsPulse Design** — fonts, colors, dark mode default
- **Appearance → Menus** — Primary, Footer, Mobile
- **Appearance → Widgets** — Sidebar, footer columns, ad zones, homepage sections
- **Posts → Categories** / **Posts → Add New**
- **Pages → Add New**
- **Users → Add New**

---

## Package contents

| Component | Folder | Required? |
|-----------|--------|-----------|
| Theme | `neonews-theme/` | Yes |
| Core plugin | `neonews-core/` | Yes |
| Security plugin | `neonews-security/` | Recommended |
| Membership | `neonews-membership/` | Optional (v2.0 — activation keys, badges, exclusive) |
| PWA | `neonews-pwa/` | Optional |
| Demo importer | `demo-importer/` | Optional (fastest setup) |

---

## Shortcodes (quick reference)

| Shortcode | Purpose |
|-----------|---------|
| `[neonews_submit_form]` | Frontend article submission form |
| `[neonews_account]` | Member account dashboard (likes, reads, shares) |
| `[premium_content]…[/premium_content]` | Content visible only to premium members |
| `[free_content]…[/free_content]` | Wrapper for free-tier content |
| `[membership_status]` | Shows current user's membership status |

---

## Support workflow

1. Check **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** for your symptom.
2. Search **[COMPLETE-GUIDE.md](COMPLETE-GUIDE.md)** for the feature name or admin label.
3. Open the focused guide from the index above.
4. For developers: see **Hooks & filters** section in [COMPLETE-GUIDE.md](COMPLETE-GUIDE.md#developer-reference).
