# Monitoring, Activity Log & REST API

User activity tracking, alerts, dashboard widgets, and programmatic access.

---

## Enable tracking

**NewsPulse → Core Features → Enable Activity Tracking**

Also requires core plugin active. Events stored in table `wp_neonews_user_activity`.

---

## View activity log

**NewsPulse → User Activity**

### Filters

| Filter | Purpose |
|--------|---------|
| User | Specific account |
| Event type | Login, read, like, etc. |
| Date from / to | Date range |
| Search | Text search in details |

Buttons: **Filter**, **Reset**, **Export CSV**

### Stats cards

- Events in last 7 days
- Active users
- Top event types

### Table columns

Time, User, Event, Details, Reference (post ID/link)

---

## Event types

| Type | When logged |
|------|-------------|
| Login | User signs in |
| Logout | User signs out |
| Read article | Article read tracked |
| Submitted article | Frontend submission |
| Profile updated | Profile fields changed |
| Profile photo | Avatar updated |
| Liked article | Like toggled |
| Shared article | Share button used |
| Posted comment | Comment submitted |
| Admin action | Selected admin events |

---

## Per-user activity

**Users → Edit User** — scroll to **NewsPulse Activity** section showing last 10 events for that user.

---

## Dashboard widgets

WordPress **Dashboard** home:

| Widget | Content |
|--------|---------|
| **NewsPulse Activity Overview** | 7-day summary chart/stats |
| **Recent User Activity** | Latest events list |

---

## Email & webhook alerts

**NewsPulse → Core Features → Activity Alerts**

| Setting | Purpose |
|---------|---------|
| **Email Suspicious Activity Alerts** | Master email switch |
| **Alert Email** | Recipient |
| **Alert on Administrator Login** | Email when admin logs in |
| **Enable Webhook / Slack Alerts** | HTTP notifications |
| **Webhook Type** | Generic JSON or Slack Incoming Webhook |
| **Webhook URL** | Destination |

### Test alerts

Click **Send test alert** on Core Features page (separate from main Save button).

Verifies email delivery and webhook without waiting for real anomaly.

### Anomaly detection

System detects unusual patterns (e.g. spike in logins, failed patterns) and triggers alerts when enabled.

---

## REST API

**Base URL:** `https://yoursite.com/wp-json/neonews/v1/`

**Authentication:** WordPress user with `manage_options` — use admin cookie session or [Application Passwords](https://wordpress.org/documentation/article/application-passwords/).

### Endpoints

#### GET `/activity`

Paginated activity log.

Query parameters:

| Param | Type | Description |
|-------|------|-------------|
| `page` | int | Page number (default 1) |
| `per_page` | int | Items per page (max 100) |
| `user_id` | int | Filter by user |
| `event_type` | string | Filter by event |
| `search` | string | Search details |
| `date_from` | string | YYYY-MM-DD |
| `date_to` | string | YYYY-MM-DD |

Example:
```
GET /wp-json/neonews/v1/activity?event_type=login&per_page=20
```

#### GET `/activity/stats`

Aggregate statistics.

| Param | Default | Description |
|-------|---------|-------------|
| `days` | 7 | Lookback period |

#### GET `/activity/types`

Returns map of event type slugs to human labels.

#### GET `/activity/{id}`

Single log entry by ID.

### Example (curl with Application Password)

```bash
curl -u "admin:xxxx xxxx xxxx xxxx" \
  "https://yoursite.com/wp-json/neonews/v1/activity/stats?days=30"
```

---

## CSV export

**NewsPulse → User Activity → Export CSV**

Exports filtered results. Uses same filters as the log view.

---

## Privacy considerations

- Activity log stores user IDs, IP-related metadata, and post references
- Inform users in Privacy Policy (legal page template included)
- Disable tracking if not needed: **Core Features → Enable Activity Tracking** off
- Export/delete user data per GDPR may require custom process beyond built-in tools

---

## Related docs

- [USERS-AUTH-MEMBERSHIP.md](USERS-AUTH-MEMBERSHIP.md) — what actions get logged
- [SECURITY.md](SECURITY.md) — security audit log (separate from user activity)
- [COMPLETE-GUIDE.md](COMPLETE-GUIDE.md) — full admin reference
