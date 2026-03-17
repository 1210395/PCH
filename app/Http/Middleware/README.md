# Middleware

This directory contains all custom HTTP middleware for Palestine Creative Hub.

---

## AdminMiddleware

**File:** `AdminMiddleware.php`

**What it checks:**
1. The `designer` guard has an authenticated user (401 / redirect to login if not).
2. The user's `isAdmin()` method returns true (403 / abort if not).
3. The account `isActive()` returns true — deactivated admins are logged out and redirected.

**Routes it applies to:**
All routes inside the `Route::prefix('{locale}/admin')` group in `routes/admin.php`, and the standalone `/admin/image-migration` routes in `routes/web.php`.
Registered as the `admin` alias in `bootstrap/app.php` (or `Kernel.php`).

**JSON support:** Returns structured JSON error responses (`success: false`) for requests that expect JSON (e.g. AJAX/API calls).

---

## AcademicMiddleware

**File:** `AcademicMiddleware.php`

**What it checks:**
1. The `academic` guard has an authenticated user (redirects to login if not).
2. The account's `isActive()` returns true — deactivated academic accounts are logged out and redirected with an error message.

**Routes it applies to:**
Any route group or individual route that applies the `academic` middleware alias. Primarily used to protect academic-institution management routes.

---

## Authenticate

**File:** `Authenticate.php`

**What it checks:**
Extends Laravel's built-in `Authenticate` middleware. On unauthenticated access it saves `url.intended` in the session (so the user is returned there after login) and redirects to the locale-prefixed login route.

**Routes it applies to:**
Used wherever the `auth` middleware is applied (e.g. `auth:designer`, `auth:academic`). Does not redirect JSON/API requests — those receive a 401 response.

---

## EnsureEmailIsVerified

**File:** `EnsureEmailIsVerified.php`

**What it checks:**
1. The user is authenticated via the specified guard (default: `designer`).
2. The user has verified their email (`hasVerifiedEmail()`).
3. Admin users (`is_admin = true`) bypass this check unconditionally.

Unverified non-admin users are redirected to `verification.notice`.

**Routes it applies to:**
All routes using the `verified` middleware alias. Applied to the main authenticated designer route group in `routes/web.php`.

---

## SecurityHeaders

**File:** `SecurityHeaders.php`

**What it checks / sets:**
This middleware does not guard access. It appends the following security headers to every HTTP response:

| Header | Value |
|---|---|
| `X-Content-Type-Options` | `nosniff` |
| `X-Frame-Options` | `SAMEORIGIN` |
| `X-XSS-Protection` | `1; mode=block` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` *(HTTPS only)* |

**Routes it applies to:**
Applied globally to every response via the middleware stack in `bootstrap/app.php`.

---

## SetLocale

**File:** `SetLocale.php`

**What it checks / does:**
1. Reads the `{locale}` route parameter from the current URL (falls back to `config('app.locale', 'en')`).
2. Validates against the supported locales list: `['en', 'ar']`. Unsupported values fall back to `'en'`.
3. Calls `App::setLocale($locale)` to configure the application for this request.
4. Writes the resolved locale into a `locale` cookie valid for **one year** (525,600 minutes) so subsequent visits default to the user's last-used language.

**Routes it applies to:**
Applied to the top-level `Route::group(['prefix' => '{locale}'])` group in `routes/web.php`, so every localised route automatically inherits it. The root `/` redirect reads the cookie independently before any middleware runs.
