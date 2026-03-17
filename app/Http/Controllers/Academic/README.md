# app/Http/Controllers/Academic

Controllers for the academic institution portal within Palestine Creative Hub.
Academic institutions (universities, colleges, TVETs, and accredited training bodies) have
their own separate authentication guard and dashboard for managing training programmes,
workshops, and public announcements.

All routes are under the `/{locale}/academic` prefix and require the `academic` guard.

---

## Controller Index

| File | Description |
|---|---|
| `AcademicBaseController.php` | Abstract base controller inherited by all academic controllers. Provides `successResponse()`, `errorResponse()`, `getAccount()`, and `getAccountId()` helpers. |
| `AcademicAuthController.php` | Login form, login submission, and logout for academic accounts. Uses the `academic` guard. |
| `AcademicDashboardController.php` | Renders the institution dashboard with counts (total/pending/approved/rejected/active/expired) and recent/upcoming items for all three content types. |
| `AcademicProfileController.php` | Editing basic profile info, uploading/deleting the institution logo, and changing the account password. |
| `AcademicTrainingController.php` | Full CRUD for training programmes (index, create, store, show, edit, update, destroy). |
| `AcademicWorkshopController.php` | Full CRUD for workshops (index, create, store, show, edit, update, destroy). |
| `AcademicAnnouncementController.php` | Full CRUD for public announcements (index, create, store, show, edit, update, destroy). |

---

## AcademicBaseController Pattern

All academic controllers extend `AcademicBaseController` (which in turn extends the root `Controller`).
This provides three categories of helper:

### JSON Response Helpers

```php
// Returns { success: true, message: "...", data: [...] }
$this->successResponse('Done', ['id' => $id]);

// Returns { success: false, message: "...", errors: [...] }
$this->errorResponse('Not found', 404);
```

Every public action supports both HTML (redirect) and JSON (AJAX) response modes by
checking `$request->expectsJson()`.

### Auth Accessor Helpers

```php
$account   = $this->getAccount();   // auth('academic')->user()
$accountId = $this->getAccountId(); // auth('academic')->id()
```

These are called at the top of every action method to scope all DB queries to the
authenticated institution — preventing cross-account data access without relying on
middleware alone.

---

## The `academic` Auth Guard

Configured in `config/auth.php`:

```php
'guards' => [
    'academic' => [
        'driver'   => 'session',
        'provider' => 'academic_accounts',
    ],
],
'providers' => [
    'academic_accounts' => [
        'driver' => 'eloquent',
        'model'  => App\Models\AcademicAccount::class,
    ],
],
```

The `AcademicAccount` model is completely separate from `Designer`. Academic accounts do
**not** share sessions, tokens, or guards with designer accounts.

Routes protected by this guard use the `auth:academic` middleware:

```php
Route::middleware(['auth:academic'])->group(function () {
    // academic portal routes
});
```

---

## Content Types: Trainings, Workshops, Announcements

All three content types share the same approval lifecycle:

| Status | Meaning |
|--------|---------|
| `pending` | Submitted by the institution; awaiting admin review. Not publicly visible. |
| `approved` | Reviewed and approved by admin. Publicly visible on the frontend. |
| `rejected` | Rejected by admin with an optional `rejection_reason`. Not publicly visible. |

### Auto-Approve Setting

Each content type can be individually configured for auto-approval via the `AdminSetting` model:

```php
$autoAcceptEnabled = \App\Models\AdminSetting::isAutoAcceptEnabled('trainings');
$validated['approval_status'] = $autoAcceptEnabled ? 'approved' : 'pending';
```

When auto-approved, `NotificationSubscriptionService::notifyOnContentApproved()` is called
immediately to notify subscribers.

### Re-Review on Edit

If an institution edits a **rejected** item, the approval status is automatically reset to
`pending` and the `rejection_reason` is cleared, queuing it for admin re-review:

```php
if ($item->isRejected()) {
    $validated['approval_status'] = 'pending';
    $validated['rejection_reason'] = null;
}
```

### Expiry Filtering

- **Trainings**: filtered by `end_date` — null means ongoing.
- **Workshops**: filtered by `workshop_date` — past date means expired.
- **Announcements**: filtered by `expiry_date` — null means no expiry.

The `publicVisible()` scope (used on frontend pages) combines `approval_status = approved`
with an expiry check to exclude stale content from public listings.

---

## Content Scoping

All queries in CRUD controllers are scoped to the authenticated account:

```php
AcademicTraining::where('academic_account_id', $this->getAccountId())->findOrFail($id);
```

This ensures institutions cannot view, edit, or delete content belonging to other accounts,
even if they guess a valid ID.
