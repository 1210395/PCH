# Routes

This directory contains all route definitions for Palestine Creative Hub.

---

## Files

| File | Purpose |
|---|---|
| `web.php` | All public and authenticated frontend routes |
| `admin.php` | Admin panel routes (included by `web.php`) |

---

## The `{locale}` Prefix Pattern

Nearly every frontend route is wrapped in a `Route::group(['prefix' => '{locale}'])` block. This means all URLs carry a language prefix:

- `https://example.com/en/designers`
- `https://example.com/ar/profile`

The `SetLocale` middleware resolves the `{locale}` segment, validates it against `['en', 'ar']`, sets `App::setLocale()`, and saves the preference in a one-year cookie. Admin routes (in `admin.php`) follow the same pattern with the prefix `{locale}/admin`.

The root `/` route redirects to the appropriate locale by reading the `locale` cookie first, then falling back to the `Accept-Language` header.

---

## web.php — Route Groups

### Global (no prefix)

| Route | Name | Description |
|---|---|---|
| `GET /media/{path}` | — | Serves uploaded files from `storage/app/public/` via the `/media/` prefix (avoids Apache blocking `/storage/`). Throttled at 200 req/min. Prevents path traversal. |
| `GET /oauth2/setup` | `oauth2.setup` | Starts the Google OAuth2 flow for Gmail API authorisation. |
| `GET /oauth2/callback` | `oauth2.callback` | Handles the Google OAuth2 callback. |
| `GET /sitemap.xml` | `sitemap` | XML sitemap for search engines. |
| `GET /` | — | Locale redirect: reads cookie → Accept-Language header → defaults to `en`. |
| `GET /{locale}/favicon.ico` | — | Serves favicon regardless of locale prefix. |

---

### `/{locale}` group — Public Routes

All routes below are prefixed with `/{locale}` and have the `SetLocale` middleware applied.

#### Home & Search

| Route | Name | Middleware |
|---|---|---|
| `GET /` | `home` | `throttle:100,1` |
| `GET /search` | `search` | `throttle:60,1` |
| `GET /search/instant` | `search.instant` | `throttle:120,1` |

#### CMS Static Pages

| Route | Name | Description |
|---|---|---|
| `GET /{slug}` | `page.show` | Handles `about`, `support`, `community-guidelines`, `terms`, `privacy`, `accessibility`, `sitemap` |

#### Authentication

| Route | Name | Middleware |
|---|---|---|
| `GET /login` | `login` | `throttle:60,1` |
| `POST /login` | `login.post` | `throttle:15,1` |
| `GET /register` | `register` | — |
| `POST /register` | `register.post` | `throttle:5,1` |
| `GET /register/success` | `register.success` | — |

#### Email Verification

| Route | Name | Middleware |
|---|---|---|
| `GET /email/verify` | `verification.notice` | — |
| `GET /email/verify/{id}/{hash}` | `verification.verify` | `signed`, `throttle:20,1` |
| `POST /email/verification-notification` | `verification.send` | `throttle:10,5` |

#### Password Reset

| Route | Name | Middleware |
|---|---|---|
| `GET /password/forgot` | `password.request` | — |
| `POST /password/email` | `password.email` | `throttle:3,1` |
| `GET /password/reset/{token}` | `password.reset` | — |
| `POST /password/reset` | `password.update` | `throttle:5,1` |

#### Public Browse (rate limited)

| Route | Name |
|---|---|
| `GET /designer/{id}` | `designer.portfolio` |
| `GET /designers` | `designers` |
| `GET /projects` / `GET /projects/{id}` | `projects` / `project.detail` |
| `GET /products` / `GET /products/{id}` | `products` / `product.detail` |
| `GET /fab-labs` / `GET /fab-labs/{id}` | `fab-labs` / `fab-lab.detail` |
| `GET /marketplace` / `GET /marketplace/{id}` | `marketplace.index` / `marketplace.show` |
| `GET /trainings` / `GET /trainings/{id}` | `trainings.index` / `trainings.show` |
| `GET /tenders` / `GET /tenders/{id}` | `tenders.index` / `tenders.show` |
| `GET /services` / `GET /services/{id}` | `services` / `services.show` |
| `GET /academic-tevets` / `GET /academic-tevets/{id}` | `academic-tevets` / `academic-institution.show` |

---

### `/{locale}` group — Authenticated (`auth:designer`, `verified`)

These routes require the `designer` guard to be authenticated and email-verified.

#### Profile Management

| Route | Name |
|---|---|
| `GET /profile` | `profile` |
| `GET /profile/edit` | `profile.edit` |
| `POST /profile/update` | `profile.update` |
| `GET /account/settings` | `account.settings` |

#### Portfolio Item CRUD

| Routes | Names |
|---|---|
| `POST /products`, `PUT /products/{id}`, `DELETE /products/{id}` | `products.store`, `products.update`, `products.destroy` |
| `POST /projects`, `PUT /projects/{id}`, `DELETE /projects/{id}` | `projects.store`, `projects.update`, `projects.destroy` |
| `POST /services`, `PUT /services/{id}`, `DELETE /services/{id}` | `services.store`, `services.update`, `services.destroy` |

#### Messaging

All message routes are under `auth:designer` + `verified`, rate-limited individually.
Key routes: `messages.index`, `messages.chat`, `messages.send`, `messages.requests`, `messages.fetch`.

#### Notifications

`notifications.index`, `notifications.unreadCount`, `notifications.markAsRead`, `notifications.markAllAsRead`.

#### Subscriptions (profile + category)

`subscriptions.profile.toggle`, `subscriptions.profile.check`, `subscriptions.category.get`, `subscriptions.category.save`, `subscriptions.category.delete`.

#### Likes & Follows

`designer.follow`, `designer.unfollow`, `product.like`, `project.like`, `designer.like`, `marketplace.like`.

---

## admin.php — Route Groups

All admin routes share the middleware stack `['auth:designer', 'admin']` and the prefix `{locale}/admin`.

### Naming Convention

Admin route names follow the pattern `admin.{resource}.{action}`, e.g.:
- `admin.designers.index`
- `admin.products.approve`
- `admin.ratings.criteria.store`

### Resource Groups

| Prefix | Name prefix | Description |
|---|---|---|
| `/analytics` | `admin.analytics.` | Platform analytics dashboard and CSV export |
| `/` (dashboard) | `admin.dashboard` | Main admin dashboard and pending counts |
| `/designers` | `admin.designers.` | Designer account CRUD, activate/deactivate, bulk actions |
| `/products` | `admin.products.` | Product review, approve/reject, image management |
| `/projects` | `admin.projects.` | Project review, approve/reject, image management |
| `/services` | `admin.services.` | Service review, approve/reject |
| `/marketplace` | `admin.marketplace.` | Marketplace post review, approve/reject |
| `/fablabs` | `admin.fablabs.` | Full FabLab CRUD (admin-created content) |
| `/settings` | `admin.settings.` | Hero image, texts, footer, header, counters, auto-accept toggles |
| `/dropdowns` | `admin.dropdowns.` | Manage all dropdown option types (sectors, cities, skills, etc.) |
| `/pages` | `admin.pages.` | CMS static page editing |
| `/trainings` | `admin.trainings.` | Full training CRUD (admin-created) |
| `/tenders` | `admin.tenders.` | Full tender CRUD (admin-created) |
| `/academic-accounts` | `admin.academic-accounts.` | Academic institution account management |
| `/academic-content` | `admin.academic-content.` | Approval workflow for academic trainings, workshops, announcements |
| `/ratings` | `admin.ratings.` | Profile rating moderation and criteria management |

### Approval Workflow Pattern

Content submitted by designers (products, projects, services, marketplace posts) follows an approval workflow:

```
POST /{id}/approve  → admin.{resource}.approve
POST /{id}/reject   → admin.{resource}.reject
```

Admin-managed content (trainings, tenders, FabLabs) has no approval step — it is published directly.
