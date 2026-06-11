# Content Management

Posts, categories, featured/breaking flags, videos, and the submission workflow.

---

## Create a news article

1. **Posts → Add New**
2. **Title** — headline
3. **Content** — article body (block editor)
4. **Excerpt** — optional manual excerpt (otherwise auto from content)
5. **Featured image** — required for carousel cards and push notification images
6. **Categories** — assign one or more
7. **NeoNews Options** (sidebar):
   - **Featured Post** — homepage carousel
   - **Breaking News** — ticker (expires 24h)
   - **Send Push Notification** — if OneSignal configured
8. **YouTube Video** (if applicable):
   - YouTube URL
   - Video duration (displayed on video cards)
9. **Premium Content** — if membership plugin active (premium flag + show on home)
10. **Publish**

---

## Categories

**Posts → Categories**

| Field | Purpose |
|-------|---------|
| **Name** | Display name (e.g. World News) |
| **Slug** | URL segment — must match homepage settings |
| **Parent** | Optional hierarchy |
| **Description** | SEO text |

### Slugs used by theme defaults

`politics`, `business`, `technology`, `sports`, `entertainment`, `videos`

Configure which appear on homepage: **NewsPulse → Homepage → Category slugs**.

### Category colors

**NewsPulse → Features & Colors → Custom colors**:

```
politics:#e63946
technology:#457b9d
```

One slug:color pair per line.

---

## Featured posts (carousel)

1. Edit post → **NeoNews Options** → **Featured Post**
2. Up to N posts shown (N = **NewsPulse → Homepage → Carousel post count**)
3. Toggle section: **NewsPulse → Homepage → Featured carousel**

Also controllable in **Customize → NewsPulse Design → Featured Carousel on Homepage**.

---

## Breaking news (ticker)

1. Edit post → **NeoNews Options** → **Breaking News**
2. Publish
3. Appears in header ticker with LIVE/Breaking labels
4. Auto-removed after **24 hours** (filter: `neonews_breaking_news_expiry`)
5. Can trigger OneSignal push with "BREAKING:" prefix

Configure: **NewsPulse → Header → Breaking news ticker**

---

## Video / Watch section

Posts appear in **Watch** section when:
1. **NewsPulse → Homepage → Watch / Videos** is enabled
2. Post is in category matching **Video category slug** (default: `videos`)
3. **YouTube Video** meta has URL set

---

## View counts

Enabled via **NewsPulse → Core Features → Enable View Counter**.

- Shown on post meta and engagement bar
- **Post Statistics** meta box in editor
- **Posts** list column: Views
- Bot filtering and rate limiting built in

---

## Frontend submission workflow

### Setup

1. **NewsPulse → Core Features** → **Enable News Submission**
2. Page with `[neonews_submit_form]` (e.g. Submit Story)
3. User must be logged in with Reporter, Author, Editor, or Admin role

### Submit

Reporter fills form → post created as **Pending**.

### Review

**NewsPulse → Pending Submissions** or **Posts → All Posts → Pending**

1. Open post
2. Edit if needed
3. Change status to **Published**

Activity log records **Submitted article** events.

---

## Posts list columns

| Column | Meaning |
|--------|---------|
| **Views** | Total view count |
| **Flags** | ★ featured, ⚡ breaking, 📝 frontend submission |
| **Premium** | 🔒 premium; 🏠 = shown in homepage Premium Picks |

---

## Bulk operations

Standard WordPress bulk edit works for categories and status.

For demo content cleanup use **NewsPulse → Demo Content → Remove Demo** (not bulk delete).

---

## SEO notes

- Theme outputs Schema.org **NewsArticle** on singles
- Use excerpts for meta descriptions (SEO plugins compatible)
- Featured images should be at least 1200×630 for social sharing

---

## Related docs

- [PAGES-AND-TEMPLATES.md](PAGES-AND-TEMPLATES.md) — Submit Story page
- [USERS-AUTH-MEMBERSHIP.md](USERS-AUTH-MEMBERSHIP.md) — Reporter roles
- [PUSH-NOTIFICATIONS.md](PUSH-NOTIFICATIONS.md) — Push on publish
- [COMPLETE-GUIDE.md](COMPLETE-GUIDE.md) — Full settings reference
