# NeoNews Platform - Installation Guide

## System Requirements

- **WordPress**: 5.9 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.7+ or MariaDB 10.3+
- **Memory**: 128MB minimum (256MB recommended)
- **HTTPS**: Required for PWA features

## Installation Steps

### 1. Download the Platform

Download the complete NeoNews Platform package containing:
- `neonews-theme/` - Main WordPress theme
- `neonews-core/` - Core functionality plugin
- `neonews-membership/` - Membership system plugin
- `neonews-pwa/` - Progressive Web App plugin
- `demo-importer/` - Demo content importer

### 2. Install the Theme

1. Log in to your WordPress admin dashboard
2. Navigate to **Appearance → Themes → Add New**
3. Click **Upload Theme**
4. Choose the `neonews-theme` folder (zip it first if needed)
5. Click **Install Now**
6. After installation, click **Activate**

**Alternative (FTP):**
1. Upload the `neonews-theme` folder to `/wp-content/themes/`
2. Go to **Appearance → Themes**
3. Find "NeoNews Theme" and click **Activate**

### 3. Install Required Plugins

Install the plugins in this order:

#### NeoNews Core (Required)
1. Go to **Plugins → Add New → Upload Plugin**
2. Upload `neonews-core` folder (as zip)
3. Click **Install Now** then **Activate**

#### NeoNews Membership (Optional but Recommended)
1. Upload and activate `neonews-membership` the same way

#### NeoNews PWA (Optional)
1. Upload and activate `neonews-pwa` the same way

#### Demo Importer (Optional)
1. Upload and activate `demo-importer` for sample content

### 4. Initial Configuration

After activation, complete these steps:

#### Set Up Menus
1. Go to **Appearance → Menus**
2. Create a "Primary Menu" and assign to **Primary Menu** location
3. Create a "Footer Menu" and assign to **Footer Menu** location
4. (Optional) Create a "Mobile Menu" for mobile-specific navigation

#### Configure Widgets
1. Go to **Appearance → Widgets**
2. Add widgets to:
   - **Sidebar** - Main blog sidebar
   - **Footer Columns 1-4** - Footer widget areas
   - **Homepage Sections 1-3** - Homepage widget areas

#### Set Up Homepage
1. Go to **Settings → Reading**
2. Choose "A static page" for homepage displays
3. Select or create your homepage
4. Set a posts page for the blog

#### Configure Site Identity
1. Go to **Appearance → Customize → Site Identity**
2. Upload a **Logo** (recommended: 300x100px)
3. Upload a **Site Icon** (required for PWA, 512x512px)

### 5. Import Full Demo (Required for turnkey site)

1. Ensure **Demo Importer** plugin is activated
2. Go to **NewsPulse → Demo Content** (or **NeoNews Demo** if Core/theme is not active)
3. Click **Import Full Demo Now**
4. Wait 1–2 minutes (downloads copyright-free photos from Picsum)

This creates **everything ready to edit**:
- Homepage + blog page (Reading settings configured)
- Header menu with categories + footer menu
- Sidebar and footer widgets pre-filled
- 15 youth-style articles with featured images
- 5 categories, 2 demo users, site title, colors, site icon

### 6. Plugin Configuration

#### NeoNews Core Settings
1. Go to **NewsPulse → Core Features**
2. Configure:
   - Enable/disable news submission
   - Enable/disable view counter
   - Enable/disable activity tracking
   - OneSignal credentials (for push notifications)

#### Membership Settings
1. Go to **NewsPulse → Membership**
2. Configure:
   - Enable paywall
   - Free article limits
   - Premium excerpt length

#### PWA Settings
1. Go to **NewsPulse → PWA**
2. Configure:
   - App name and short name
   - Theme colors
   - Display mode

### 7. Permalinks

1. Go to **Settings → Permalinks**
2. Choose "Post name" or custom structure
3. Click **Save Changes** (important for PWA routes)

## Verification Checklist

After installation, verify:

- [ ] Theme activates without errors
- [ ] All plugins activate without errors
- [ ] Admin pages load correctly
- [ ] Frontend displays properly
- [ ] Dark mode toggle works
- [ ] Mobile menu functions
- [ ] Search overlay opens/closes
- [ ] Breaking news ticker shows (if posts marked as breaking)

## Troubleshooting

### White Screen After Activation
- Check PHP error logs
- Increase memory limit in `wp-config.php`:
  ```php
  define('WP_MEMORY_LIMIT', '256M');
  ```

### Styles Not Loading
- Clear browser cache
- Check for plugin conflicts
- Verify file permissions (755 for folders, 644 for files)

### PWA Not Working
- Ensure site uses HTTPS
- Check Site Icon is set
- Clear service worker in browser DevTools

### Menu Not Showing
- Verify menu is assigned to correct location
- Check if theme location exists
- Clear any caching plugins

## Next Steps

- Read [README.md](README.md) — documentation hub
- Read [GETTING-STARTED.md](GETTING-STARTED.md) for first 30 minutes
- Read [COMPLETE-GUIDE.md](COMPLETE-GUIDE.md) for every feature
- Read [SETUP.md](SETUP.md) for detailed configuration
- Read [ELEMENTOR.md](ELEMENTOR.md) for page builder usage
- Read [SECURITY.md](SECURITY.md) for security best practices
