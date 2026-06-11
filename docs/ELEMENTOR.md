# NeoNews Platform - Elementor Guide

## Elementor Compatibility

NeoNews Theme is fully compatible with Elementor page builder. You can use Elementor to:
- Design custom homepage layouts
- Create landing pages
- Build custom archive templates (with Elementor Pro)
- Design header and footer (with Elementor Pro)

## Getting Started with Elementor

### Installing Elementor

1. Go to **Plugins → Add New**
2. Search for "Elementor"
3. Install and activate **Elementor Website Builder**
4. (Optional) Purchase and install Elementor Pro for advanced features

### Creating Your First Page

1. Go to **Pages → Add New**
2. Enter page title
3. Click **Edit with Elementor**

## Using Full Width Template

For pages that should span the full width:

1. In WordPress editor (before opening Elementor)
2. Find **Page Attributes** in right sidebar
3. Select **Full Width** template
4. Click **Edit with Elementor**

## Homepage Design with Elementor

### Basic Homepage Structure

```
┌─────────────────────────────────────────┐
│            Breaking News Ticker          │ ← Theme Header (auto)
├─────────────────────────────────────────┤
│               Header/Logo                │
├─────────────────────────────────────────┤
│                                         │
│          Featured Carousel               │ ← Can use theme's or build custom
│                                         │
├─────────────────────────────────────────┤
│                                         │
│    Latest News Grid (3 columns)          │
│                                         │
├──────────────────────┬──────────────────┤
│                      │                  │
│   Category Section   │    Sidebar       │
│                      │                  │
├──────────────────────┴──────────────────┤
│              Footer Widgets              │ ← Theme Footer (auto)
└─────────────────────────────────────────┘
```

### Building News Grid Section

1. Add new section with 3 columns
2. Drag **Posts** widget (free) or **Posts** widget (Pro)
3. Configure:
   - **Layout**: Grid
   - **Columns**: 3
   - **Posts per page**: 6
   - **Image Position**: Top
   - **Show**: Title, Excerpt, Meta

### Creating Category Section

1. Add section with 2 columns (70/30 split)
2. Left column: Posts widget filtered by category
3. Right column: Sidebar widgets

## Editable Areas

### Areas Controlled by Theme (Not Elementor)

- Breaking news ticker
- Main header (logo, navigation, search, dark mode toggle)
- Main footer
- Mobile navigation

### Areas You Can Edit with Elementor

- Page content
- Custom homepage layouts
- Landing pages
- Custom templates (with Pro)

## Recommended Widgets for News Sites

### Free Elementor Widgets

| Widget | Use Case |
|--------|----------|
| **Heading** | Section titles |
| **Text Editor** | Article content |
| **Image** | Featured images |
| **Image Box** | News cards |
| **Icon Box** | Category highlights |
| **Posts** | News listings |
| **Tabs** | Category tabs |
| **Accordion** | FAQ sections |

### Elementor Pro Widgets

| Widget | Use Case |
|--------|----------|
| **Posts** (Pro) | Advanced news grids |
| **Portfolio** | Visual news layouts |
| **Loop Grid** | Custom post layouts |
| **Table of Contents** | Long articles |
| **Share Buttons** | Social sharing |

## Theme Builder (Elementor Pro)

### Creating Custom Single Post Template

1. Go to **Templates → Theme Builder**
2. Click **Add New**
3. Select **Single Post**
4. Design your template using:
   - Post Title
   - Post Content
   - Featured Image
   - Post Meta
   - Author Box
   - Related Posts
5. Set display conditions

### Creating Custom Archive Template

1. Go to **Templates → Theme Builder**
2. Click **Add New**
3. Select **Archive**
4. Design using Archive widgets
5. Set conditions (Category, Tag, etc.)

## Styling to Match Theme

### Using Theme Colors

NeoNews uses CSS variables. Access in Elementor:

- Primary: `#e63946`
- Secondary: `#1d3557`
- Accent: `#457b9d`
- Text: `#212529`

### Typography

Match theme typography:
- **Headings**: Georgia, serif
- **Body**: System fonts (-apple-system, etc.)

### Spacing

Use consistent spacing:
- Section padding: 60px top/bottom
- Column gap: 30px
- Widget spacing: 20px

## Common Layouts

### News Grid Layout

```
Section Settings:
- Layout: Boxed
- Content Width: 1200px
- Padding: 60px top/bottom

Column Settings:
- Widget spacing: 20px

Posts Widget:
- Skin: Classic
- Columns: 3
- Image Ratio: 16:10
- Show: Title, Excerpt, Date, Category
```

### Featured Post with Sidebar

```
Section with 2 columns:
- Column 1: 70%
- Column 2: 30%

Left Column:
- Posts widget (1 post, large)

Right Column:
- Heading: "Trending"
- Posts widget (5 posts, list style)
```

### Category Tabs

```
Tabs Widget:
- Tab 1: Politics (Posts filtered)
- Tab 2: Technology (Posts filtered)
- Tab 3: Sports (Posts filtered)
```

## Best Practices

### Performance

1. **Optimize Images**: Use WebP format
2. **Limit Sections**: Don't overuse sections
3. **Lazy Load**: Enable for images
4. **Cache**: Use caching plugin

### Responsive Design

1. **Test All Devices**: Desktop, Tablet, Mobile
2. **Stack Columns**: On mobile, columns should stack
3. **Adjust Font Sizes**: Smaller on mobile
4. **Hide Elements**: Use responsive visibility

### SEO

1. **Use Proper Headings**: H1 → H2 → H3 hierarchy
2. **Alt Text**: Always add to images
3. **Internal Links**: Link to related content
4. **Schema**: Theme adds NewsArticle schema

## Troubleshooting

### Elementor Editor Blank

1. Clear browser cache
2. Deactivate other plugins temporarily
3. Increase PHP memory limit
4. Check for JavaScript errors

### Styles Not Applying

1. Clear Elementor cache (Elementor → Tools)
2. Regenerate CSS (Elementor → Tools)
3. Check CSS conflicts

### Theme Elements Not Showing

If breaking news or navigation not showing:
- These are controlled by theme, not Elementor
- Check theme settings/customizer
- Verify you haven't used Elementor Theme Builder override

### Layout Issues on Mobile

1. Use responsive mode in editor
2. Adjust column widths for mobile
3. Consider hiding complex elements
4. Test on actual devices

## Resources

- [Elementor Academy](https://elementor.com/academy/)
- [Elementor Help Center](https://elementor.com/help/)
- [Elementor Community](https://www.facebook.com/groups/Elementors/)
