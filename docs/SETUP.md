# NeoNews Platform - Setup Guide

> **Full documentation:** [README.md](README.md) | [COMPLETE-GUIDE.md](COMPLETE-GUIDE.md) | [PAGES-AND-TEMPLATES.md](PAGES-AND-TEMPLATES.md)

## Creating Your Homepage

### Option 1: Using Widgets (No Page Builder)

1. **Create Homepage**
   - Go to **Pages → Add New**
   - Title: "Home"
   - Select **Full Width** template
   - Publish

2. **Set as Front Page**
   - Go to **Settings → Reading**
   - Select "A static page"
   - Choose "Home" as Homepage
   - Choose a page for "Posts page"

3. **Configure Homepage Widgets**
   - Go to **Appearance → Widgets**
   - Add widgets to "Homepage Section 1", "Homepage Section 2", "Homepage Section 3"

### Option 2: Using Elementor (Recommended)

See [ELEMENTOR.md](ELEMENTOR.md) for detailed instructions.

## Setting Up Categories

### Creating Categories

1. Go to **Posts → Categories**
2. Add your main categories:
   - **Name**: Display name (e.g., "World News")
   - **Slug**: URL-friendly version (e.g., "world-news")
   - **Description**: Brief description for SEO
3. Click **Add New Category**

### Recommended Category Structure

```
├── News
│   ├── Local
│   ├── National
│   └── International
├── Politics
├── Business
│   ├── Markets
│   └── Economy
├── Technology
├── Sports
├── Entertainment
└── Opinion
```

### Category Display Settings

Categories automatically appear in:
- Post cards
- Featured carousel
- Sidebar category widget
- Footer (auto-populated)

## Configuring Dark Mode

### Default Mode

1. Go to **Appearance → Customize → NeoNews Options**
2. Set "Default Theme Mode" to Light or Dark
3. Publish changes

### How Dark Mode Works

- **Logged-in users**: Preference saved in user meta
- **Guests**: Preference saved in browser cookie
- Toggle button in header switches instantly
- Persists across page loads

### Customizing Dark Mode Colors

Edit CSS variables in `style.css`:

```css
[data-theme="dark"] {
    --nn-bg-primary: #1a1a2e;
    --nn-bg-secondary: #16213e;
    --nn-text-primary: #edf2f4;
    /* Add more custom colors */
}
```

## Setting Up Featured Posts

### Marking Posts as Featured

1. Edit any post
2. Find **NeoNews Options** meta box (right sidebar)
3. Check **Featured Post**
4. Update/Publish

### Featured Carousel Behavior

- Displays up to 5 featured posts
- Shows on homepage only
- Auto-rotates every 5 seconds
- Supports touch/swipe on mobile
- Keyboard navigation (arrow keys)

## Setting Up Breaking News

### Marking Posts as Breaking

1. Edit any post
2. Find **NeoNews Options** meta box
3. Check **Breaking News**
4. Update/Publish

### Breaking News Behavior

- Appears in ticker bar at top of site
- Auto-expires after 24 hours
- Can trigger push notification (if configured)
- Multiple breaking news items scroll together

## Configuring User Roles

### Available Roles

| Role | Capabilities |
|------|-------------|
| **Reporter** | Submit posts (pending review), upload media |
| **Editor** | All editor capabilities + approve submissions |
| **Administrator** | Full access |

### Creating Reporter Accounts

1. Go to **Users → Add New**
2. Fill in username and email
3. Set Role to **Reporter**
4. Click **Add New User**

## News Submission System

### Setting Up Submission Page

1. Create new page: "Submit Article"
2. Add shortcode: `[neonews_submit_form]`
3. Publish page
4. Add to navigation menu

### Submission Requirements

- User must be logged in
- User must have Reporter, Author, Editor, or Admin role
- Required fields: Title, Content, Category
- Optional: Featured image, Excerpt

### Managing Submissions

1. Go to **NeoNews → Pending Submissions**
2. Review submitted articles
3. Edit if needed
4. Change status to "Published" when approved

## Setting Up Push Notifications

### OneSignal Integration

1. Create free account at [OneSignal.com](https://onesignal.com)
2. Create new Web Push app
3. Get App ID and REST API Key
4. Go to **NeoNews → Settings**
5. Enter credentials
6. Save

### Sending Notifications

- **On Publish**: Check "Send Push Notification" in post editor
- **Breaking News**: Automatically sent when post marked as breaking

## Sidebar Configuration

### Default Sidebar

If no widgets added, sidebar shows:
- Trending Posts (based on view count)
- Categories list

### Custom Sidebar

1. Go to **Appearance → Widgets**
2. Add widgets to "Sidebar":
   - Search
   - Recent Posts
   - Categories
   - Tag Cloud
   - Custom HTML
   - etc.

## Footer Configuration

### Footer Widgets

1. Go to **Appearance → Widgets**
2. Configure four footer columns:
   - **Footer Column 1**: About text
   - **Footer Column 2**: Quick links
   - **Footer Column 3**: Categories
   - **Footer Column 4**: Newsletter/Social

### Footer Menu

1. Create menu at **Appearance → Menus**
2. Assign to "Footer Menu" location

## Search Configuration

### Search Behavior

- Opens full-screen overlay
- Searches posts only (not pages)
- Press ESC to close
- Click outside to close

### Customizing Search

Search is limited to posts by default. To include other post types, add filter:

```php
add_filter('pre_get_posts', function($query) {
    if ($query->is_search && !is_admin()) {
        $query->set('post_type', array('post', 'page'));
    }
    return $query;
});
```

## Performance Optimization

### Recommended Settings

1. **Caching Plugin**: WP Super Cache, W3 Total Cache, or LiteSpeed Cache
2. **Image Optimization**: ShortPixel or Imagify
3. **CDN**: Cloudflare (free tier works well)

### Built-in Optimizations

- Lazy loading images
- Deferred JavaScript
- Minimal CSS/JS (no heavy frameworks)
- Optimized database queries

## Maintenance

### Regular Tasks

- Review pending submissions weekly
- Clear expired breaking news (auto-handled)
- Update WordPress, theme, and plugins
- Backup database regularly

### Monitoring

- Check view statistics in post editor
- Review user activity at **NewsPulse → User Activity** (filter by user, event type, date; export CSV)
- On any user profile (**Users → Edit**), see the last 10 NewsPulse events for that account
- **Dashboard** widgets: *NewsPulse Activity Overview* and *Recent User Activity*
- **Alerts**: enable under **NewsPulse → Core Features → Activity Alerts** (email + Slack/generic webhook; use **Send test alert** to verify)
- **REST API**: `GET /wp-json/neonews/v1/activity` (admin auth required)
- **CAPTCHA**: configure under **NewsPulse → Platform → Login & signup CAPTCHA** (Turnstile, reCAPTCHA, or hCaptcha)
- **Performance**: **NewsPulse → Platform** — score panel at top; lightweight mode on by default; **Apply recommended fixes** one-click; enable **System fonts** for fastest loads
- Monitor push notification delivery in OneSignal dashboard
