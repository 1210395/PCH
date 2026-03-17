# app/Http/Controllers/Auth

Authentication controllers for the Palestine Creative Hub designer portal. These controllers manage registration, login, logout, email verification, password reset, image uploads during registration, and Google OAuth for Gmail API access.

All routes are under the `/{locale}` prefix and use the `designer` guard.

---

## Controller Index

| File | Description |
|---|---|
| `AuthController.php` | Login form display and submission, multi-step registration wizard (display and submission), logout, registration success page. |
| `EmailVerificationController.php` | Display verification notice, handle signed verification link, resend verification email. |
| `PasswordResetController.php` | Forgot password form, send reset link, show reset form, handle password update. |
| `ValidationController.php` | AJAX endpoint to check whether an email address is already registered (`POST /{locale}/validate/email`). Used by the registration wizard for live feedback. |
| `ImageUploadController.php` | Handles progressive image and PDF uploads during the registration wizard. Images are stored temporarily in `storage/app/public/temp/` and linked to the new designer record after successful registration. |
| `GoogleOAuthController.php` | OAuth2 redirect and callback for authorising the Gmail API (used by `GmailOAuthService` for sending transactional email via Gmail SMTP). |

---

## Key Patterns

### Multi-Step Registration Wizard
`AuthController::register()` accepts a single POST with all wizard steps merged. It uses `DB::transaction()` to create the `Designer` record atomically, then persists uploaded images/PDFs that were saved in temp storage during the wizard.

### Rate Limiting
- Registration: 5 requests per minute
- Login: 15 requests per minute (brute-force protection)
- Validation endpoints: 10 requests per minute
- Password reset email: 3 requests per minute

### Guard
All auth operations target the `designer` guard explicitly (e.g., `Auth::guard('designer')->login($designer)`). Never uses the default `web` guard.

### Email Verification
Uses Laravel's built-in `MustVerifyEmail` contract on the `Designer` model. Verification links are signed routes; resend is throttled to 10 per 5 minutes.

### Temp Upload Cleanup
`ImageUploadController` stores uploaded files in a temp directory with a session-scoped identifier. Files are moved to permanent storage on successful registration. Stale temp files are cleaned up by a scheduled Artisan command.

---

## ImageUploadController — Temp-to-Permanent Pattern

The registration wizard uploads files progressively before the form is submitted. Each upload is saved to a session-scoped temp path:

```
storage/app/public/uploads/temp/{type}/{session_id}/{uuid}.{ext}
```

Where `{type}` is one of `profiles`, `covers`, `products`, `projects`, `services`, `certifications`.

On successful registration, `AuthController::register()` calls `ImageUploadController::moveToPermStorage()` for each temp path. That method:

1. Validates the path against the storage base directory (path traversal protection).
2. Generates a structured permanent filename: `{type}_{entityId}_{imageNumber}.{ext}` (e.g. `product_42_1.jpg`).
3. Moves the file to its permanent folder (e.g. `products/product_42_1.jpg`).
4. Returns the new relative path which is then stored in the database.

If a move fails, the original temp path is returned as a fallback to avoid losing the upload.

Duplicate detection uses a per-session metadata JSON file that maps file hashes to stored paths; cross-type reuse is blocked (a profile image hash cannot resolve a product image).

---

## GoogleOAuthController — OAuth2 Flow

`GoogleOAuthController` is an admin-only utility for obtaining a Gmail API refresh token:

1. `GET /oauth2/redirect` — Builds the Google consent-screen URL via `GmailOAuthService::getAuthorizationUrl()` and redirects the admin.
2. `GET /oauth2/callback` — Receives the authorization code from Google, exchanges it for tokens via `GmailOAuthService::exchangeCode()`, and renders `auth.oauth-success` displaying the refresh token for manual `.env` configuration.

The refresh token is never stored automatically; the admin copies it into `GOOGLE_REFRESH_TOKEN` in `.env`. After that, `GmailOAuthService` uses it to send transactional emails via Gmail SMTP.

---

## PasswordResetController

Handles the two-step forgot-password flow using Laravel's built-in `designers` password broker (configured in `config/auth.php`):

1. `GET /{locale}/forgot-password` → `showForgotForm()` — renders the email input form.
2. `POST /{locale}/forgot-password` → `sendResetLink()` — dispatches a signed reset link; never reveals whether an email exists (shows the same success message regardless).
3. `GET /{locale}/reset-password/{token}` → `showResetForm()` — renders the new-password form with the token pre-filled.
4. `POST /{locale}/reset-password` → `reset()` — validates the token, hashes the new password, and redirects to the login page on success.

Password requirements mirror registration: minimum 8 characters, mixed case, number, and symbol.
