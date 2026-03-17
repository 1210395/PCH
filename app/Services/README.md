# app/Services

Service classes for the Palestine Creative Hub. Each service encapsulates a distinct concern that would otherwise clutter controllers or models.

---

## Service Index

| File | Description |
|---|---|
| `CacheService.php` | Centralised cache management for dashboard statistics, homepage data, featured content, designer-specific stats, and marketplace categories. All methods are static. |
| `NotificationSubscriptionService.php` | Sends in-app notifications to profile subscribers and category subscribers when content is approved. Batches `Notification` and `AcademicNotification` inserts in chunks of 100. All methods are static. |
| `WebhookSignatureService.php` | Verifies RSA-SHA256 signatures on incoming Jobs.ps webhook requests using OpenSSL. Loaded as a constructor-injected dependency in `WebhookController`. |
| `GmailOAuthService.php` | Obtains and refreshes a Gmail API OAuth2 access token (using a stored refresh token) for sending transactional email via the Gmail API. Caches the access token to avoid unnecessary refresh requests. |

---

## CacheService

### Cache Keys and TTLs

| Key | TTL | Content |
|---|---|---|
| `admin_dashboard_stats` | 5 min | Aggregated counts, pending queues, designer stats, growth, sector/city breakdowns |
| `homepage_stats` | 15 min | Total counts per content type, sector breakdown, vendor counts |
| `homepage_featured` | 15 min | Top designers, top manufacturers, featured projects and products |
| `designer_{id}_unread_notifications` | 1 min | Unread notification count for a specific designer |
| `designer_{id}_content_stats` | 5 min | Per-status content counts for a specific designer |
| `marketplace_categories` | 1 hour | Distinct approved marketplace post categories |
| `marketplace_tags` | 1 hour | Marketplace tags from dropdown options |
| `similar_designers_{sector}` | 15 min | Top designers in the same sector (for sidebar suggestions) |
| `admin_top_contributors` | 5 min | Designers with most approved content (dashboard widget) |
| `admin:analytics:{version}:{hash}` | 5 min | Versioned analytics result set for a given filter combination |

### Clearing Caches
- `clearDashboardCache()` — clears `admin_dashboard_stats`, `homepage_stats`, `homepage_featured`.
- `clearDesignerCache($id)` — clears per-designer notification and content stat caches.
- `clearMarketplaceCache()` — clears categories and tags.
- `clearAllCaches()` — calls all of the above.

Models call `clearDashboardCache()` automatically in their `booted()` hooks on save/delete.

---

## NotificationSubscriptionService

### Flow
1. `notifyOnContentApproved($content)` — called from `HasApprovalStatus::approve()`. Extracts content type, creator, category, tags, and level from the model instance.
2. `notifyProfileSubscribers()` — finds all `ProfileSubscription` records for the creator and bulk-inserts notifications into `Notification` (for designers) and `AcademicNotification` (for academic accounts).
3. `notifyCategorySubscribers()` — delegates to `CategorySubscription::getMatchingSubscriptions()` to find subscribers whose filter preferences match the content, then bulk-inserts notifications in the same way.

Bulk inserts are chunked at 100 rows to stay within MySQL's packet size limits.

---

## WebhookSignatureService

Uses `openssl_verify()` with `OPENSSL_ALGO_SHA256` to validate that a webhook payload was signed by Jobs.ps. The public key is loaded from the filesystem path configured in `JOBS_PS_PUBLIC_KEY_PATH`. Set `JOBS_PS_SKIP_VERIFICATION=true` in `.env` to bypass verification during development (logs a warning when used).

---

## GmailOAuthService

Stores a long-lived OAuth2 refresh token in config (from `services.google.refresh_token`). On each access token request it checks the `gmail_oauth_access_token` cache entry first; if expired or missing it calls Google's token endpoint, stores the new token with the expiry minus 60 seconds buffer, and returns it.
