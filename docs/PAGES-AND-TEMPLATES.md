# Pages & Templates

How to create every page the demo includes, choose templates, and wire menus.

---

## All page templates

When editing a page, open **Page Attributes → Template**:

| Template name | File | Best for |
|---------------|------|----------|
| Default | `page.php` | Standard pages with sidebar |
| **Full Width** | `template-fullwidth.php` | Elementor, wide layouts |
| **About Us (Fancy)** | `template-about.php` | About page (auto-created via Platform settings) |
| **Contact Us** | `template-contact.php` | Contact form + WhatsApp |
| **Exclusive News** | `template-exclusive.php` | Premium/exclusive stories grid |
| **Careers** | `template-careers.php` | Jobs + WhatsApp |
| **Advertise** | `template-advertise.php` | Ad sales + WhatsApp |
| **My Account** | `template-account.php` | Member dashboard |
| **My Profile** | `template-profile.php` | Avatar & profile settings |
| **Legal Page** | `template-legal.php` | Privacy policy |
| **Terms of Service** | `template-terms.php` | Terms |
| **Cookies Policy** | `template-cookies.php` | Cookie policy |
| **Cache & Storage** | `template-cache.php` | Cache/storage policy |

**Homepage** does not use a special template — WordPress uses `front-page.php` when a static page is set as front page.

---

## Recreate demo pages step by step

### Home

1. **Pages → Add New** → Title: `Home`
2. Leave content empty (theme builds layout)
3. Publish
4. **Settings → Reading** → Homepage: **Home**

### Latest News (blog index)

1. **Pages → Add New** → Title: `Latest News`
2. Leave empty
3. Publish
4. **Settings → Reading** → Posts page: **Latest News**

### Submit Story

1. **Pages → Add New** → Title: `Submit Story`
2. Content: `[neonews_submit_form]`
3. Publish
4. Add to Primary Menu

Requires **NewsPulse → Core Features → Enable News Submission**.

### About Us (automatic)

**NewsPulse → Platform → About Us page** → **Create / Update About Us Page**

Uses **About Us (Fancy)** template with stats hero and default story content.

### Exclusive News (automatic)

Created by **neonews-membership** at slug `exclusive` (configurable under **Membership → General**).

Template: **Exclusive News** — grid of premium posts with lock previews for basic users.

### Go Premium

1. **Pages → Add New** → Title: `Go Premium`
2. Add marketing copy about membership benefits
3. Link to sign-up or pricing
4. Requires **neonews-membership** plugin

### About, Contact, Careers, Advertise

1. Create each page with matching **Template** from table above
2. Add your content in the editor
3. **Contact Us** — form submits via built-in handler; check admin email settings

### My Account & My Profile

1. **My Account** — Template: **My Account**, content: `[neonews_account]`
2. **My Profile** — Template: **My Profile**, empty content
3. Link from header account menu (automatic when logged in)

### Legal pages (automatic)

**NewsPulse → Platform** → scroll to **Legal pages** → **Create / Update Legal Pages**

Creates/updates:
- Privacy Policy (`privacy-policy`)
- Terms of Service (`terms-of-service`)
- Cookies Policy (`cookies-policy`)
- Cache & Storage (`cache-storage`)

---

## Menus

**Appearance → Menus**

### Create Primary Menu

1. Create menu named "Main Menu"
2. Add: Home, category links (Custom Links or Categories), Submit Story, About, Contact
3. Check **Primary Menu** under **Display location**
4. Save

### Create Footer Menu

1. Create "Footer Menu"
2. Add: About, Careers, Advertise, Contact, Privacy, Terms
3. Assign to **Footer Menu**

### Mobile Menu (optional)

Separate simplified menu → assign to **Mobile Menu**.

### No menu assigned?

Theme uses fallback slugs from **NewsPulse → Header**:
- **Fallback menu categories** — e.g. `politics,business,technology,sports,entertainment`
- **Fallback menu pages** — e.g. `about,contact`

---

## Widget areas

**Appearance → Widgets**

| Area | Typical content |
|------|-----------------|
| **Sidebar** | Search, Recent Posts, Categories |
| **Footer Column 1** | About blurb |
| **Footer Column 2** | Quick links |
| **Footer Column 3** | Categories |
| **Footer Column 4** | Newsletter / social |
| **Homepage Section 1–3** | Optional extra blocks |
| **Ad zones** | Ad network HTML (see COMPLETE-GUIDE ads section) |

Built-in homepage sidebar blocks (Weather, Fresh Reads, Topics) are controlled in **NewsPulse → Sidebar**, not Widgets.

---

## Footer & company links

**NewsPulse → Footer**:
- **Company column** — comma-separated page slugs: `about,careers,advertise,contact`
- **Sections column** — category slugs for footer category list

---

## Elementor pages

1. Create page → Template: **Full Width**
2. **Edit with Elementor**
3. Theme header, footer, and breaking ticker remain active

See [ELEMENTOR.md](ELEMENTOR.md).

---

## Remove demo content

**NewsPulse → Demo Content → Remove Demo**

Deletes all pages, posts, users, menus, and widgets tagged as demo. Custom content you added separately is kept.
