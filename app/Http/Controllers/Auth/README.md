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
