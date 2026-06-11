# Performance Guide

Speed optimizations built into NewsPulse and how to use the Performance Score panel.

---

## Performance Score panel

**Path:** **NewsPulse → Platform** (top of **Ads & Integrations** tab)

### What it shows

| Element | Meaning |
|---------|---------|
| **Score 0–100** | Percentage of optimizations active |
| **Grade (A+ to F)** | Letter grade |
| **KB transfer saved** | Estimated bytes not loaded per page view |
| **Fewer requests** | Estimated HTTP requests avoided |
| **Faster interactivity (est.)** | Rough TTI improvement in ms |
| **Active optimizations** | What's already on |
| **Recommendations** | What to enable next |

### One-click fix

**Apply N recommended fixes** — enables all disabled performance toggles (system fonts, bloat removal, lazy CAPTCHA, etc.) without checking each box manually.

After applying, score refreshes on next page load.

---

## Lightweight mode (master switch)

**NewsPulse → Platform → Performance → Lightweight mode**

When ON (default), enables:
- Deferred JavaScript
- Lazy-loaded images in content
- Non-blocking CSS preload for theme stylesheet
- Reduced third-party load patterns

Turn OFF only for debugging.

---

## Individual toggles

| Setting | Savings | What it does |
|---------|---------|--------------|
| **System fonts** | ~120 KB, 2 requests | Skips Google Fonts entirely — fastest first paint |
| **Disable emoji scripts** | ~18 KB | Removes WP emoji JS/CSS |
| **Disable wp-embed.js** | ~6 KB | Removes oEmbed helper on frontend |
| **Disable dashicons** | ~28 KB | Removes admin icon font for guests |
| **Remove block editor CSS** | ~45 KB | Dequeues block library on frontend |
| **Disable Heartbeat (frontend)** | ~12 KB | Stops WP polling AJAX on public pages |
| **Conditional plugin assets** | ~14 KB | Core/membership CSS/JS on posts, home, archives, profile, account, exclusive |
| **Lazy CAPTCHA** | ~150 KB | CAPTCHA provider loads on sign-in modal only |
| **Reduce scroll/motion animations** | CPU time | Less scroll animation work |

---

## Recommendations outside toggles

The panel may suggest (manual steps):

| Recommendation | Action |
|----------------|--------|
| **Weather widget** | Disable under **Platform → Weather** if not needed |
| **Google Analytics** | Review if required; loads after cookie consent |
| **Page cache plugin** | Install LiteSpeed Cache, WP Rocket, or similar |
| **Membership conditional assets** | Enable under Platform → Performance when membership plugin is active |
| **Membership upgrade popup** | Increase delay under Membership → Popup & Home if intrusive |

---

## Membership & performance {#membership--performance}

When **neonews-membership** v2 is active:

| Feature | Impact | Mitigation |
|---------|--------|------------|
| Membership CSS | ~8 KB on member-related pages | Keep **Conditional plugin assets** ON |
| Popup JS | ~2 KB, deferred | Tune delay in Membership → Popup & Home |
| Blur lock | CSS filter only | No extra requests |
| Premium Picks | One WP_Query on home | Limit post count in Membership settings |
| Ad-free premium | Skips ad zones | Saves bandwidth for premium users |

Membership CSS/JS does **not** load on generic pages (About, Contact) when conditional assets is enabled.

---

## Server-level optimizations

Not built into theme but strongly recommended:

1. **Page caching** — LiteSpeed Cache, WP Super Cache, WP Rocket
2. **Image optimization** — WebP, ShortPixel, Imagify
3. **CDN** — Cloudflare free tier
4. **PHP 8.1+** — faster than PHP 7.4
5. **Object cache** — Redis/Memcached on high-traffic sites

---

## Verify improvements

1. Note score and KB estimate before changes
2. Apply fixes or enable system fonts
3. Clear page cache
4. Test in incognito:
   - [PageSpeed Insights](https://pagespeed.web.dev/)
   - Browser DevTools → Network tab
5. Reload Platform tab — score should increase

---

## What the theme already avoids

- No jQuery on frontend
- Vanilla JavaScript only
- Conditional loading of core plugin assets
- IntersectionObserver for weather (loads when visible)
- Idle initialization for heavy JS features
- Lazy images via `loading="lazy"` and content filter

---

## Troubleshooting slow loads

| Symptom | Fix |
|---------|-----|
| Low performance score | Click **Apply recommended fixes** + enable **System fonts** |
| Google Fonts slow | Enable **System fonts** or reduce font weights in Customizer |
| CAPTCHA on every page | Enable **Lazy CAPTCHA** |
| Too many plugins | Deactivate unused plugins |
| Large images | Compress and use WebP |
| No page cache | Install caching plugin |
| Weather AJAX | Disable weather if not needed |

See also [TROUBLESHOOTING.md](TROUBLESHOOTING.md).

---

## Developer function

```php
$report = neonews_get_performance_report();
// Keys: score, grade, grade_label, active, recommendations, totals, auto_fix_count
```

Apply programmatically:
```php
neonews_apply_performance_recommendations(); // returns count of settings enabled
```
