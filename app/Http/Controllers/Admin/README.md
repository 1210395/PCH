# app/Http/Controllers/Admin

Admin panel controllers for the Palestine Creative Hub. All routes in this directory are protected by the `auth:designer` guard combined with the `admin` middleware, which checks the `is_admin` flag on the authenticated designer.

Route prefix: `/{locale}/admin`

---

## Base Class

`AdminBaseController` (extends Laravel `Controller`) provides shared helpers used by every admin controller:

| Method | Description |
|---|---|
| `validateAndSanitize()` | Validates the request then strips HTML tags from all string fields to prevent XSS. |
| `jsonResponse()` | Wraps `response()->json()` with a status code. |
| `successResponse()` | Returns `{ success: true, message, data }` JSON. |
| `errorResponse()` | Returns `{ success: false, message, errors }` JSON with a status code. |
| `validateId()` | Checks that a route parameter is a positive integer. |
| `getAdmin()` / `getAdminId()` | Returns the currently authenticated admin designer or their ID. |
| `approveContent()` | Generic approve flow: finds model by ID, calls `$model->approve($adminId)`. |
| `rejectContent()` | Generic reject flow: validates optional reason, calls `$model->reject($adminId, $reason)`. |
| `toggleContentFeatured()` | Flips the `featured` boolean on any content item. |

---

## Controller Index

| File | Route prefix | Description |
|---|---|---|
| `AdminDashboardController.php` | `/admin/` | Platform KPIs, pending queues, activity charts, top contributors |
| `AdminAnalyticsController.php` | `/admin/analytics/` | Advanced date/sector/city filtered analytics with cache and Excel export |
| `AdminDesignerController.php` | `/admin/designers/` | Designer account list, edit, toggle active/trusted/admin, reset password, bulk actions |
| `AdminProductController.php` | `/admin/products/` | Product list, edit, image delete, approve/reject, bulk actions |
| `AdminProjectController.php` | `/admin/projects/` | Project list, edit, image delete, approve/reject, bulk actions |
| `AdminServiceController.php` | `/admin/services/` | Service list, edit, approve/reject, bulk actions |
| `AdminMarketplaceController.php` | `/admin/marketplace/` | Marketplace post list, edit, approve/reject, bulk actions |
| `AdminFabLabController.php` | `/admin/fablabs/` | Full CRUD for fabrication laboratory entries |
| `AdminTrainingController.php` | `/admin/trainings/` | Full CRUD for admin-managed training entries (no approval workflow) |
| `AdminTenderController.php` | `/admin/tenders/` | Full CRUD for tenders, toggle visibility, bulk actions |
| `AdminAcademicAccountController.php` | `/admin/academic-accounts/` | Academic institution account CRUD, toggle active, reset password |
| `AdminAcademicContentController.php` | `/admin/academic-content/` | Approve/reject academic trainings, workshops, announcements; bulk actions |
| `AdminProfileRatingController.php` | `/admin/ratings/` | Rating list, stats, analytics, approve/reject, toggle auto-accept |
| `AdminRatingCriteriaController.php` | `/admin/ratings/criteria/` | CRUD and reordering of rating criteria |
| `AdminSettingsController.php` | `/admin/settings/` | Hero image, hero texts, auto-accept toggles, registration policies |
| `AdminLayoutSettingsController.php` | `/admin/settings/` | Header, footer, and subheader text settings |
| `AdminCounterSettingsController.php` | `/admin/settings/counters` | Homepage counter labels and values |
| `AdminDropdownController.php` | `/admin/dropdowns/` | Dropdown option CRUD, reorder, alphabetical sort, toggle active |
| `AdminPageController.php` | `/admin/pages/` | CMS static page edit, image upload/remove, reset to defaults |
| `ImageMigrationController.php` | `/admin/image-migration` | One-off utility to migrate image paths between storage layouts |

---

## Key Patterns

### Approval Workflow
Content controllers (products, projects, services, marketplace) call the inherited `approveContent()` and `rejectContent()` helpers from `AdminBaseController`, which delegate to the `HasApprovalStatus` trait on the model. Approval records the admin ID and timestamp in `approved_by` / `approved_at`. Rejection records the reason and fires a notification to the content owner.

### Cache Invalidation
Content mutations call `CacheService::clearDashboardCache()` so the dashboard stats reflect changes immediately.

### Bulk Actions
Every list controller exposes a `bulkAction(Request $request)` endpoint that accepts an array of IDs and an action string (`approve`, `reject`, `delete`, `activate`, `deactivate`, etc.). Actions are dispatched in a loop.

### JSON vs. Blade Responses
Controllers check `$request->expectsJson()` to return either a JSON response (for AJAX/Alpine.js calls) or a Blade redirect with a flash message.

### Analytics Caching Strategy (`AdminAnalyticsController`)
Analytics results are cached under a versioned key (`admin:analytics:{version}:{hash_of_filters}`). Incrementing the version via the `/admin/analytics/refresh` endpoint invalidates all cached analytics slices at once without needing individual cache busting.
