# app/Http/Controllers

Root-level controllers for the Palestine Creative Hub public-facing application. Each controller handles one feature domain and is accessible to visitors and authenticated designers.

All routes using these controllers are prefixed with `/{locale}` (where `locale` is `en` or `ar`).

---

## Controller Index

| File | Guard | Purpose |
|---|---|---|
| `Controller.php` | — | Base Laravel controller (extended by all others) |
| `HomeController.php` | public | Homepage, discovery feed, global search |
| `DesignerController.php` | public/auth | Designer directory listing and public portfolio page |
| `DesignerProfileController.php` | auth:designer | Own profile view/edit, account settings, password, delete account |
| `DesignerFollowController.php` | auth:designer | Follow/unfollow designers; user search/suggestions for sharing |
| `ProductController.php` | public/auth | Product listing, detail view, create/edit/delete, like toggle |
| `ProjectController.php` | public/auth | Project listing, detail view, create/edit/delete, like toggle |
| `ServiceController.php` | public/auth | Service listing, detail view, create/edit/delete |
| `MarketplaceController.php` | public/auth | Marketplace feed, post detail, like toggle |
| `MarketplacePostController.php` | auth:designer | Create/edit/delete marketplace posts, share to users |
| `MarketplaceCommentController.php` | public/auth | Read comments (public); create/edit/delete (auth) |
| `FabLabController.php` | public | Fab Lab directory listing and detail view |
| `TenderController.php` | public | Tender listing and detail view |
| `TrainingController.php` | public | Training listing and detail view (backed by `AcademicTraining`) |
| `AcademicTevetsController.php` | public | Academic institution directory and detail view |
| `MessagesController.php` | auth:designer | Messaging inbox, compose, chat, fetch/send messages |
| `MessageRequestController.php` | auth:designer | Message request flow (send, accept, decline, pending count) |
| `ConversationRatingController.php` | auth:designer | Rate a conversation after it closes |
| `ProfileRatingController.php` | public/auth | View ratings (public); submit/update/delete own rating (auth) |
| `NotificationController.php` | auth:designer | Notification inbox, unread count, mark read/all-read |
| `EmailController.php` | auth:designer | Compose and send a direct email to another designer |
| `SubscriptionController.php` | auth:designer | Toggle profile subscriptions and manage category subscriptions |
| `PageController.php` | public | Render CMS static pages (About, Terms, Privacy…) |
| `SitemapController.php` | public | Generate `/sitemap.xml` for search engines |

### Sub-directories

| Directory | Description |
|---|---|
| `Admin/` | 20 controllers for the admin panel — see `Admin/README.md` |
| `Api/` | Webhook controller for external integrations — see `Api/README.md` |
| `Auth/` | Authentication, email verification, password reset, image upload — see `Auth/README.md` |
| `Academic/` | Academic portal controllers (separate auth guard) |

---

## Key Patterns

### Locale Parameter
Every controller method receives `$locale` as the first route parameter (injected via `{locale}` prefix). Most methods pass it directly to `route()` helpers when generating redirect URLs.

### Approval Workflow
Content controllers (Product, Project, Service, MarketplacePost) rely on the `HasApprovalStatus` trait on their models. Public listing views call the `approved()` scope; owners see their own items in all states via the `visibleTo($designerId)` scope.

### Rate Limiting
All routes apply Laravel throttle middleware. Write operations (store/update/like) are throttled more aggressively than read operations.

### Auth Guard
The platform uses the `designer` guard (table: `designers`) rather than the default Laravel `web` guard. Auth checks use `auth('designer')->user()` and `auth:designer` middleware.

### Cache Invalidation
When models are saved or deleted, the `booted()` hooks call `CacheService::clearDashboardCache()` and `CacheService::clearDesignerCache()` to keep analytics fresh.
