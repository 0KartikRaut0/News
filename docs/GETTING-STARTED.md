# Getting Started — First 30 Minutes

Follow this checklist after [INSTALLATION.md](INSTALLATION.md). For full detail on every step, see [COMPLETE-GUIDE.md](COMPLETE-GUIDE.md).

---

## Checklist

### 1. Activate components

- [ ] **neonews-theme** — Appearance → Themes → Activate
- [ ] **neonews-core** — Plugins → Activate
- [ ] **neonews-security** — recommended
- [ ] **demo-importer** — fastest setup
- [ ] **neonews-membership** — activation keys, badges, exclusive content (v2.0)

### 2. Permalinks & identity

- [ ] **Settings → Permalinks** → Post name → Save
- [ ] **Appearance → Customize → Site Identity** → Logo + Site Icon (512×512)

### 3. Demo import {#demo-import}

- [ ] **NewsPulse → Demo Content**
- [ ] Click **Import Full Demo Now**
- [ ] Wait 1–2 minutes
- [ ] Click **View Site**

**If images missing:** click **Fix Missing Images** (requires PHP GD extension).

### 4. Security baseline

- [ ] **NewsPulse → Security Center → Run Security Scan**
- [ ] Click **Fix All (Auto)** if offered
- [ ] Save **Protection Settings** (encryption + login limit recommended)

### 5. Review settings

- [ ] **NewsPulse → Platform** — Performance Score at top; click **Apply recommended fixes** if shown
- [ ] **NewsPulse → Core Features** — confirm submission, views, activity tracking enabled
- [ ] **Appearance → Customize → NewsPulse Design** — pick fonts/colors

### 6. Verify frontend

- [ ] Homepage loads with carousel and categories
- [ ] Dark mode toggle works
- [ ] Search opens and finds posts
- [ ] Sign-in modal opens
- [ ] Single post shows engagement bar
- [ ] Mobile menu works

---

## Without demo import

If you skip the demo, do these manually:

1. Create pages — see [PAGES-AND-TEMPLATES.md](PAGES-AND-TEMPLATES.md)
2. **Settings → Reading** → static Home + Posts page
3. **Posts → Categories** — politics, business, technology, sports, entertainment, videos
4. **Appearance → Menus** — Primary + Footer
5. **Appearance → Widgets** — Sidebar + footer columns
6. Publish a few posts; mark one **Featured** and one **Breaking**

---

## Default demo credentials

After demo import, reset passwords for:

| Username | Role |
|----------|------|
| `demo_reporter` | Reporter |
| `demo_editor` | Editor |

**Users → All Users** → Edit → Set New Password.

---

## Where to go next

| Goal | Document |
|------|----------|
| Add/edit articles | [CONTENT-MANAGEMENT.md](CONTENT-MANAGEMENT.md) |
| Change homepage layout | [COMPLETE-GUIDE.md#homepage-configuration](COMPLETE-GUIDE.md#homepage-configuration) |
| Reporter submissions | [USERS-AUTH-MEMBERSHIP.md](USERS-AUTH-MEMBERSHIP.md) |
| Activation keys & premium badges | [USERS-AUTH-MEMBERSHIP.md#membership--paywall](USERS-AUTH-MEMBERSHIP.md#membership--paywall) |
| Paywall / exclusive content | [USERS-AUTH-MEMBERSHIP.md#membership--paywall](USERS-AUTH-MEMBERSHIP.md#membership--paywall) |
| Push alerts | [PUSH-NOTIFICATIONS.md](PUSH-NOTIFICATIONS.md) |
| Stuck on an error | [TROUBLESHOOTING.md](TROUBLESHOOTING.md) |
