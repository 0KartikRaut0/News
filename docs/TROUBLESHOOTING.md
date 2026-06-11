# Troubleshooting

Common problems and fixes. Search [COMPLETE-GUIDE.md](COMPLETE-GUIDE.md) for specific setting names.

---

## Installation & activation

### White screen after activating theme/plugin

1. Enable WordPress debug in `wp-config.php`:
   ```php
   define( 'WP_DEBUG', true );
   define( 'WP_DEBUG_LOG', true );
   ```
2. Check `wp-content/debug.log`
3. Increase memory:
   ```php
   define( 'WP_MEMORY_LIMIT', '256M' );
   ```
4. Deactivate plugins one by one to find conflict

### Styles look broken

1. Hard refresh (Ctrl+Shift+R)
2. Clear caching plugin cache
3. Verify theme activated (not child theme missing files)
4. Check file permissions: folders 755, files 644

---

## Homepage & layout

### Homepage shows blog posts instead of news layout

1. **Settings → Reading** → **A static page**
2. Homepage = **Home** page
3. Posts page = **Latest News** (or your blog page)
4. Save

### Carousel empty

1. Mark posts as **Featured Post** (NeoNews Options)
2. **NewsPulse → Homepage → Featured carousel** enabled
3. Increase **Carousel post count** if needed

### Breaking ticker empty

1. Mark posts as **Breaking News**
2. **NewsPulse → Header → Breaking news ticker** enabled
3. Breaking posts expire after 24 hours — publish fresh ones

### Category blocks empty

1. Category slugs in **NewsPulse → Homepage → Category slugs** must match real categories
2. Categories need published posts
3. **Show home categories** enabled

---

## Menus & navigation

### Menu not showing

1. **Appearance → Menus** → assign to **Primary Menu**
2. Menu must contain items
3. If no menu: check **fallback slugs** in **NewsPulse → Header**

### Footer links missing

Configure **NewsPulse → Footer → Company column** and **Sections column** slugs.

---

## Auth & CAPTCHA

### Sign-in modal not working

1. Check browser console for JS errors
2. Disable other plugins temporarily
3. Verify **Show header auth** enabled

### CAPTCHA always fails

1. Verify site key + secret key match provider dashboard
2. Domain must match site URL in provider settings
3. For reCAPTCHA v3: adjust threshold
4. If encryption on: re-save secret key in Platform settings

### CAPTCHA slows every page

Enable **Lazy CAPTCHA** under Performance settings.

---

## Submission & roles

### Submit form says not allowed

1. User must be logged in
2. Role must be Reporter, Author, Editor, or Admin
3. **NewsPulse → Core Features → Enable News Submission** on

### Submissions not appearing

Check **Posts → Pending** or **NewsPulse → Pending Submissions**.

---

## Membership & paywall

### Paywall / blur lock not showing

1. **neonews-membership** plugin active (v2.0+)
2. **NewsPulse → Membership → General → Enable Paywall** on
3. Post marked **Premium / exclusive post**
4. User is not premium and blur lock enabled under **Content & Lock**

### Activation key not working

1. Key copied exactly (format `NN-XXXX-XXXX`)
2. Key status is **active** in Membership → Activation Keys
3. Key not expired (if validity days were set)
4. User logged in on **Profile** page
5. After 5 failed attempts, wait 15 minutes (rate limit)

Check **Security Center → Activity Log** for failed attempts.

### User should be premium but isn't

**Users → Edit User → Premium Status** — check box and save.

Or verify key was redeemed (key status = **used** in admin).

### Badges not showing

**NewsPulse → Membership → Badges & Timer** — enable avatar/tag toggles.

### Expiry timer not showing

User must have an expiry date set and days remaining ≤ **Expiry reminder (days)** setting.

---

## Activity & alerts

### Activity log empty

1. **Enable Activity Tracking** in Core Features
2. Perform test action (login, read post)
3. Check date filters on activity page

### Test alert not received

1. Verify alert email address
2. Check spam folder
3. For Slack: use Incoming Webhook URL, select Slack type
4. Server must be able to send mail (`wp_mail`) or use SMTP plugin

---

## Security Center

### Scan fails or hangs

1. Increase PHP `max_execution_time`
2. Check `debug.log` for errors
3. Ensure `neonews-security` active

### Fix button does nothing

Some findings require manual fix (documented in finding text). Auto-fix only applies to supported items.

---

## Performance

### Low performance score

**NewsPulse → Platform → Apply recommended fixes** + enable **System fonts**.

See [PERFORMANCE.md](PERFORMANCE.md).

### Google Fonts still loading with system fonts on

1. Save Platform settings again
2. Clear cache
3. Check Customizer isn't overriding with external fonts

---

## PWA

### Install prompt not showing

1. Site must use **HTTPS**
2. **Site Icon** set (512×512)
3. **NewsPulse → PWA → Enable PWA** on
4. Clear browser site data / unregister old service worker in DevTools

### Offline page not appearing

Hard refresh after enabling PWA. Service worker may take one visit to register.

---

## Push notifications

See [PUSH-NOTIFICATIONS.md](PUSH-NOTIFICATIONS.md) troubleshooting section.

Quick checks:
- HTTPS required
- OneSignal App ID + REST key in Core Features
- Post has **Send Push** or **Breaking News** checked

---

## Demo importer

### Import fails

1. PHP **GD extension** required for local images
2. Increase memory and timeout
3. Check `debug.log`

### Images missing after import

**NewsPulse → Demo Content → Fix Missing Images**

### Remove demo broke site

Re-import demo or manually recreate Home page and Reading settings per [PAGES-AND-TEMPLATES.md](PAGES-AND-TEMPLATES.md).

---

## Ads & cookies

### Ads not showing

1. **Enable all ads** + zone enabled
2. Widget added to ad zone in **Appearance → Widgets**
3. If cookie gating on: user must accept all cookies
4. Ad blocker in browser may hide ads

### Analytics not tracking

1. **Google Analytics** enabled + valid Measurement ID
2. User must accept all cookies
3. Verify ID format: `G-XXXXXXXXXX`

---

## Weather

### Weather shows wrong city

Set **Default city** or precise **Latitude/Longitude** in Platform settings.

### Weather never loads

1. **Enable weather** on
2. Check browser console for fetch errors
3. Open-Meteo may rate-limit — wait and retry

---

## Elementor

See [ELEMENTOR.md](ELEMENTOR.md) troubleshooting.

Theme header/footer/breaking ticker always come from theme — not Elementor.

---

## Still stuck?

1. Search [COMPLETE-GUIDE.md](COMPLETE-GUIDE.md) for exact admin label
2. Check [docs/README.md](README.md) index
3. Enable `WP_DEBUG_LOG` and reproduce the issue
4. Note plugin conflict by deactivating all except neoNews plugins
