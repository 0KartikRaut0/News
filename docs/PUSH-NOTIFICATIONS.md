# NeoNews Platform - Push Notifications Guide

## Overview

NeoNews integrates with OneSignal for web push notifications. This allows you to:
- Send notifications when new articles are published
- Automatically notify users about breaking news
- Build subscriber engagement

## OneSignal Setup

### Step 1: Create OneSignal Account

1. Go to [OneSignal.com](https://onesignal.com)
2. Click **Sign Up** (free tier available)
3. Verify your email

### Step 2: Create New App

1. Click **New App/Website**
2. Enter your app name (e.g., "My News Site")
3. Select **Web** as platform
4. Click **Next**

### Step 3: Configure Web Push

1. **Site Name**: Your website name
2. **Site URL**: Your website URL (must be HTTPS)
3. **Auto Resubscribe**: Enable (recommended)
4. **Default Icon URL**: Leave blank (uses site icon)
5. Click **Save**

### Step 4: Get Credentials

1. Go to **Settings → Keys & IDs**
2. Copy **OneSignal App ID**
3. Copy **REST API Key**

### Step 5: Configure in WordPress

1. Go to **NewsPulse → Core Features**
2. Scroll to **OneSignal Push Notifications**
3. Enter **OneSignal App ID**
4. Enter **OneSignal REST API Key**
5. Click **Save Changes**

## How Notifications Work

### Subscription Flow

1. User visits your site
2. OneSignal shows subscription prompt (after delay)
3. User clicks "Allow"
4. User is now subscribed

### Notification Triggers

#### Manual (Per-Post)

1. Edit any post
2. Find **NeoNews Options** in sidebar
3. Check **Send Push Notification**
4. Publish/Update post
5. Notification sent to all subscribers

#### Automatic (Breaking News)

1. Mark post as **Breaking News**
2. Publish post
3. Notification automatically sent with "BREAKING:" prefix

## Notification Content

### What Gets Sent

| Field | Content |
|-------|---------|
| **Title** | Post title (or "BREAKING: {title}") |
| **Body** | First 20 words of post content |
| **URL** | Post permalink |
| **Image** | Featured image (if set) |

### Customizing Notification

Notifications use post data. To customize:
- Write compelling titles
- First paragraph should hook readers
- Always set featured images (appears in notification)

## Best Practices

### Frequency

- **Don't spam**: 1-3 notifications per day maximum
- **Time it right**: Send during active hours
- **Quality over quantity**: Only notify for important content

### Content Guidelines

1. **Clear titles**: Tell users what they'll get
2. **Create urgency**: Use action-oriented language
3. **Be accurate**: Don't mislead users

### Segmentation (Pro Tip)

In OneSignal dashboard, you can:
- Create segments based on user behavior
- Target specific audiences
- A/B test notification copy

## Troubleshooting

### Notifications Not Showing

1. **Check HTTPS**: Site must use HTTPS
2. **Verify credentials**: App ID and API key correct
3. **Test subscription**: Subscribe from incognito window
4. **Check OneSignal dashboard**: View delivery reports

### Low Subscription Rates

1. **Delay prompt**: Don't ask immediately
2. **Explain value**: Tell users why to subscribe
3. **Custom prompt**: Use OneSignal's slide prompt

### Notifications Not Sending

1. **Check post meta**: Is "Send Push" checked?
2. **Verify API connection**: Check error logs
3. **Review OneSignal logs**: Dashboard → Delivery

## Advanced Configuration

### Custom Notification Button Text

In OneSignal dashboard:
1. Go to **Settings → Web Configuration**
2. Scroll to **Prompt Settings**
3. Customize button text and messages

### Notification Bell Icon

By default, NeoNews enables the notification bell. Customize:
1. Go to **Settings → Web Configuration**
2. Find **Subscription Bell**
3. Adjust position, size, colors

### Segments

Create targeted notifications:
1. In OneSignal, go to **Audience → Segments**
2. Create segment (e.g., "Engaged Users")
3. Target specific posts to segments

## API Rate Limits

Free tier limits:
- Unlimited subscribers
- Unlimited notifications
- Full API access

Paid tiers offer:
- Priority delivery
- Advanced analytics
- More segments

## Security Notes

- **Never expose** REST API Key on frontend
- Keys are stored in WordPress options (database)
- API calls made server-side only
- No sensitive data in notifications

## Disabling Notifications

### Temporarily

1. Go to **NewsPulse → Core Features**
2. Clear OneSignal credentials
3. Save

### Permanently

1. Remove credentials as above
2. In OneSignal dashboard, disable app
3. Users won't receive notifications

## User Control

### Unsubscribing

Users can unsubscribe:
1. Click notification bell
2. Click "Unsubscribe"

Or in browser settings:
1. Site settings
2. Notifications → Block

### Respecting Preferences

- Honor user choices
- Provide clear unsubscribe option
- Don't re-prompt blocked users
