# Mac WordPress Setup (Local + GitHub)

Use this guide with **Local** (localwp.com) and your GitHub clone.

## Folder layout in this repo

```
news/                          ← your clone (e.g. /Users/kartik/github/news)
├── wp-content/
│   ├── themes/
│   │   └── neonews-theme/
│   └── plugins/
│       ├── neonews-core/
│       ├── neonews-membership/
│       ├── neonews-pwa/
│       ├── neonews-security/
│       ├── neonews-seo/
│       ├── neonews-seo-lite/
│       ├── neonews-smart/
│       └── demo-importer/
├── scripts/
│   └── link-to-wordpress.sh
└── docs/
```

WordPress reads files from **its own** `wp-content/themes` and `wp-content/plugins`.  
The link script connects Local’s folders to this repo so `git pull` updates the site instantly.

---

## One-time setup on Mac

### Step 1 — Confirm repo path

```bash
cd /Users/kartik/github/news
git pull
ls wp-content/themes/neonews-theme
ls wp-content/plugins/neonews-core
```

You should see theme and plugin files listed.

### Step 2 — Find your Local site name

```bash
ls "$HOME/Local Sites"
```

Example site: `swarajya-shikshan`

Your WordPress `wp-content` path:

```
/Users/kartik/Local Sites/swarajya-shikshan/app/public/wp-content
```

### Step 3 — Run the link script (one time)

Replace `swarajya-shikshan` if your site name is different.

```bash
cd /Users/kartik/github/news
chmod +x scripts/link-to-wordpress.sh
./scripts/link-to-wordpress.sh "$HOME/Local Sites/swarajya-shikshan/app/public/wp-content"
```

**Expected output:**

```
linked theme: neonews-theme
linked plugin: demo-importer
linked plugin: neonews-core
...
Done.
```

### Step 4 — Verify symlinks

```bash
ls -la "$HOME/Local Sites/swarajya-shikshan/app/public/wp-content/themes/neonews-theme"
```

**Good** (symlink):

```
neonews-theme -> /Users/kartik/github/news/wp-content/themes/neonews-theme
```

**Bad** (copied folder — no arrow):

```
drwxr-xr-x ... neonews-theme
```

If bad, remove the copy and run Step 3 again:

```bash
rm -rf "$HOME/Local Sites/swarajya-shikshan/app/public/wp-content/themes/neonews-theme"
rm -rf "$HOME/Local Sites/swarajya-shikshan/app/public/wp-content/plugins/neonews-"*
rm -rf "$HOME/Local Sites/swarajya-shikshan/app/public/wp-content/plugins/demo-importer"
./scripts/link-to-wordpress.sh "$HOME/Local Sites/swarajya-shikshan/app/public/wp-content"
```

### Step 5 — Activate in WordPress

1. Open Local → start site → **Open site** / **WP Admin**
2. **Appearance → Themes** → activate **NeoNews Theme**
3. **Plugins** → activate **NeoNews Core** (required)
4. Activate other plugins as needed (Membership, Security, PWA, etc.)

---

## Daily workflow

| On Windows (edit + push) | On Mac (get updates) |
|--------------------------|----------------------|
| Edit code in repo | `cd /Users/kartik/github/news` |
| `git add .` | `git pull` |
| `git commit -m "message"` | Hard refresh browser (`Cmd + Shift + R`) |
| `git push` | No copy step if symlinks are set |

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Changes not visible | Confirm symlink (Step 4), then `git pull`, hard refresh |
| PWA shows old CSS | Chrome DevTools → Application → Service Workers → Unregister |
| `no such file` on git pull | `cd /Users/kartik/github/news` (check exact path) |
| Theme missing in admin | Re-run link script; check Local site name |

---

## Paths cheat sheet (your setup)

| What | Path |
|------|------|
| Git repo | `/Users/kartik/github/news` |
| Theme in repo | `/Users/kartik/github/news/wp-content/themes/neonews-theme` |
| Local wp-content | `/Users/kartik/Local Sites/swarajya-shikshan/app/public/wp-content` |
| GitHub | `https://github.com/0KartikRaut0/News.git` |
