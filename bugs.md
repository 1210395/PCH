# PCH Pre-QA Bug Inventory

Findings from a 12-pass multi-agent audit on `main` at commit `fb8e6b8d2`. Production: `https://technopark.ps/PalestineCreativeHub/`.

Audit passes:
1. Security + authorization (static)
2. Validation + rate limiting (static)
3. i18n + RTL + accessibility (static)
4. Functional walkthrough on production (Playwright, polite cadence)
5. Console / responsive crawl on production (Playwright)
6. Database + schema audit (static)
7. Business logic + edge cases (static)
8. Frontend Alpine / JS bug audit (static)
9. Email + notification pipelines (static)
10. Cache + session + security headers (static)
11. Input fuzzing + special-character handling (static)
12. Console commands + jobs + services (static)
13. Direct grep checks for `dd()` / `TODO` / debug remnants
14. Direct config inspection (`session.php`, `bootstrap/app.php`, `SecurityHeaders`, `routes/api.php`, `WebhookController`)

Severity legend: 🚨 BLOCKER (must fix before QA), 🔴 HIGH (will be flagged), 🟡 MEDIUM (should fix), ⚪ LOW (polish).

Per-finding status: ✅ fixed · ⚠️ verified non-issue / no fix needed · ❌ open.

---

## Resolution log

Fix work shipped in 7 batches against `main`. Pull each commit and deploy.

| Batch | Commit | Scope |
|-------|--------|-------|
| 1 | `77d1fa1e4` | Diag routes deleted, marketplace guest redirect removed, Notification.data double-encode fix, account.delete.send-code throttle, login meta description |
| 2 | `77581e8b7` | Validation hardening: image `mimes:` everywhere, phone_country whitelist, search array guards + LIKE wildcard escape, `boolean()` for checkbox flags, password-reset anti-enumeration |
| 3 | `f3d50cfdc` | Throttle sweep on every state-mutating route + admin/academic groups |
| 4 | `6b5845b84` | Login per-account RateLimiter, EnsureDesignerActive middleware, rotate `remember_token` on password change, gate webhook `skip_verification` to local env |
| 5 | `5ba7eb457` | Strip password from registration localStorage, validate MessageRequest message, UrlHelper::safe + 4 templates |
| 6 | `089bef0a3` | Branded 403/404/419/429/500 error pages, JS modal copy localized, mobile nav aria-labels (verified hamburger exists) |
| 7a | `60c166d9b` | CleanupOrphanedUploads static-call fix, hide deactivated-designer content from direct URLs, cache invalidation on bulk-notification insert |
| 7b | `454149169` | Like/follow toggles wrapped in DB::transaction + lockForUpdate, scheduled cleanup tasks with onFailure logging |
| 8 | `f2d5e51d7` | Login double-submit guard, compose-message stuck-state fix, modal escape stop, portfolio file validation |
| 9 | `403d68bc2` | Marketplace source_id ownership rule, register form maxlength, admin password complexity, validate confirmDelete/sendDeleteCode, decouple delete-code email/cache |
| 10 | `6a3b01f09` | Default mailer to gmail, webhook replay protection, strip PII from registration logs, remove deprecated X-XSS-Protection |
| 11 | `fad4e0a63` | Content-Security-Policy in Report-Only mode, GDPR cookie consent banner |
| Hotfix | `aac66a231` | Defer image WebP encoding off the registration request — Publish dropped from ~10s to ~ms |
| Refactor | `2590c08e0` | Fold fastMovePerm into existing moveToPermStorage(..., $skipImageProcessing=true) |
| 12 | `84cd777a9` | H-4 image rollback cleanup on registration failure |
| 13 | `594ceb35e` | H-20 RTL shim in main layout + M-23 verify-email input label association |
| 14 | `7c442a46f` | M-1 reset approval to pending on Product/Project/Service edit |
| 15 | `47dc1cabc` | M-19 hard-coded placeholder localization + M-20 locale-aware dates |
| 16 | `85f57749b` | M-22 alt text on 5 wrongly-empty content images |
| 17 | `93e8ee49f` | M-24 search empty state + M-29 resend double-click race fix |
| 18 | `261ff3db3` | M-7 stop surfacing server debug_info in registration error toast |

**Remaining open items** (need user input or external coordination):
- B-10 (search throttle tightness — needs user policy decision)
- H-8 / H-9 / H-10 / H-25 (DB schema baselining + FK + likes uniqueness — needs migration strategy)
- H-18 (136 missing ar.json keys — needs Arabic translator)
- H-19 (RTL bulk codemod — touches 30 files)
- H-21 (home TTFP — needs perf profiling)
- H-23 (register form unlabeled inputs — would need per-input audit)
- H-28 (trustProxies CIDRs — needs confirmation site is behind Cloudflare)
- H-37 (real queue worker — needs cPanel cron setup)
- M-9 (counter recompute artisan command)
- M-10 (avatar fallback — would break existing `if ($designer->avatar)` checks)
- M-12 (tender description sanitiser — needs HTMLPurifier package)
- M-30 (modal focus trap — needs focus-trap library)
- M-31 (searchable dropdown keyboard nav — moderate effort)
- M-32 (email unsubscribe — needs new route + signed token + template)
- L-* items (polish, lower priority)

False positives confirmed: B-2, B-13, H-7, H-22, H-33, M-37 — see inline notes below.

---

## 🚨 Blockers — fix before QA

### B-1. ✅ Hardcoded-token diagnostic routes leak logs and run DDL
- **Where:** `routes/web.php:527-634`
- **What:** `/diag/mktables/f3c9e2a7`, `/__admin/logtail/f3c9e2a7`, `/diag/regerr/f3c9e2a7`, `/diag/log2/f3c9e2a7` gate on a static token in source. They return up to 200 KB of `laravel.log` (passwords, stack traces, PII) and one issues `CREATE TABLE` DDL on a GET. No auth, no rate limit.
- **Fix:** Delete the entire block. If still needed for ops, move behind `auth:designer + admin` and rotate the token.

### B-2. ⚠️ Mass-assignment lets users self-approve their own content
*Verified non-exploitable: no controller takes `approval_status` from user input (no `$request->all()`, no `$request->only(...)`, no validation rule includes `approval_status`). Removing from `$fillable` would silently break legitimate server-side `'approval_status' => $approvalStatus` writes in 5 controllers. Defense-in-depth still desirable but the audit's exploit-path claim was overstated.*
- **Where:** `app/Models/MarketplacePost.php:53`, `app/Models/Product.php:46`, `app/Models/Project.php:48`
- **What:** `approval_status`, `approved_by`, `approved_at` are listed in `$fillable`. A crafted POST/PUT body can set `approval_status=approved` and bypass the moderation queue entirely.
- **Fix:** Remove approval fields from `$fillable` on Product, Project, MarketplacePost (also verify Service / AcademicTraining / AcademicWorkshop / AcademicAnnouncement). Only set via the `approve()/reject()` trait methods.

### B-3. ✅ Marketplace silently redirects guests to login
- **Where:** `app/Http/Controllers/MarketplaceController.php:23-26`
- **What:** Public visitors get bounced to `/login` from `/marketplace`, killing SEO and discoverability. Controller already has guest-aware code at lines 53-58 — the redirect is dead leftover.
- **Fix:** Remove the 4-line `if (!$designer) return redirect(...)` block.

### B-4. ✅ Password stored in plaintext localStorage during registration
- **Where:** `resources/views/auth/register/alpine-data.blade.php:1939-1940, 2037-2038`
- **What:** `formData.password` and `confirmPassword` persisted to localStorage on every keystroke; survive across reloads / bounced submits. On shared/public computers password is recoverable from devtools until tab close (Safari iOS sometimes skips `unload`).
- **Fix:** Strip password fields from `cleanFormData` before save; ignore on restore.

### B-5. ✅ 404 / 429 error pages are unstyled
- **Where:** `resources/views/errors/` (404, 429 templates likely missing or bare)
- **What:** Bare "404 NOT FOUND" centered on a blank page — no header, footer, nav, logo, "Go home" link.
- **Fix:** Build branded `errors/404.blade.php`, `errors/429.blade.php`, `errors/500.blade.php` extending `layout.main`.

### B-6. ✅ `MessageRequestController::send` accepts user message with no validation/sanitization
- **Where:** `app/Http/Controllers/MessageRequestController.php:117`
- **What:** `$customMessage = $request->input('message')` stored directly and shown in notifications. No validate, no length cap, no `strip_tags`. Stored XSS + DoS via giant body.
- **Fix:** `$request->validate(['message' => 'nullable|string|max:2000'])` and `strip_tags()` before persist.

### B-7. ✅ `account.delete.send-code` sends email with NO throttle
- **Where:** `routes/web.php:254`
- **What:** Endpoint sends a 6-digit verification email; an attacker can trigger unlimited sends and burn SMTP quota / spam the user. Cache key `delete_code_{id}` is overwritten each call so the user can race themselves out.
- **Fix:** `->middleware('throttle:3,10')`. Don't overwrite an unexpired code.

### B-8. ✅ Image-upload validators allow SVG (XSS-capable)
- **Where:** `AuthController.php:262,264,281,291,300`; `AdminTrainingController.php:303-308`; admin product/project/service/fab-lab/announcement/marketplace controllers
- **What:** `'profile_image' => ['nullable','image','max:5120']` — `image` rule alone accepts `bmp`, `tiff`, `svg`. SVGs can carry inline `<script>`.
- **Fix:** Add `'mimes:jpg,jpeg,png,webp'` to every image rule. Bulk codemod.

### B-9. ✅ Login throttle is per-IP only at 60/min, no per-account lockout
- **Where:** `routes/web.php:168-170` + `app/Http/Controllers/Auth/AuthController.php:72-161`
- **What:** 60 attempts/min/IP allows ~3,600/hr; no email-keyed lockout, so a single email can be probed from many IPs.
- **Fix:** Define `RateLimiter::for('login', fn ($r) => Limit::perMinute(5)->by($r->input('email').'|'.$r->ip()))` in `AppServiceProvider` and use `throttle:login` on the route.

### B-10. ✅ Search rate-limits real users at normal pace
*Verified by smoke test 2026-04-28 against `https://technopark.ps/PalestineCreativeHub/en/search`: response carries `X-RateLimit-Limit: 120` (route uses `throttle:120,1`) and `/search/instant` uses `throttle:200,1`. Both well above the "two requests in 30s = 429" claim. Earlier throttle sweep batches loosened the limits.*
- **Where:** Search route in `routes/web.php`

### B-11. ✅ Hard-coded English in JS modal helpers
- **Where:** `resources/views/marketplace-post-detail.blade.php:699,746,749`
- **What:** `showAlert` / `showConfirm` template literals hard-code `OK`, `Cancel`, `Send Request` — Arabic users see English buttons.
- **Fix:** Replace with `{{ __('OK') }}` etc., interpolated into the JS.

### B-12. ✅ Login meta description still says "TecnoPark"
- **Where:** `resources/views/auth/login.blade.php:5`
- **What:** SEO meta description references the wrong product name.
- **Fix:** Change `__('Log in to your TecnoPark account')` to `'Log in to your Palestine Creative Hub account'`; add `ar.json` translation.

### B-13. ⚠️ Mobile (375px) — no hamburger / nav toggle visible
*False positive: hamburger toggle exists at `_navbar.blade.php:640` calling `toggleMobileMenu()`. Crawler missed it because it only matched on class names `.hamburger`/`.menu-toggle`/`.navbar-toggler`. Added `aria-label` / `aria-controls` / `aria-expanded` for accessibility.*
- **Where:** `resources/views/layout/main.blade.php` nav region
- **What:** Crawler at 375×812 found no `.menu-toggle`/`.hamburger`/`.navbar-toggler` element on any of 5 sampled pages.
- **Fix:** Verify with DevTools at 375px; add a Tailwind hamburger if missing.

### B-14. ✅ `<a href="{{ $fabLab->website }}">` allows `javascript:` URLs
- **Where:** `app/Http/Controllers/Admin/AdminFabLabController.php:124,250`; `resources/views/fab-lab-detail.blade.php:224`, `admin/academic-accounts/show.blade.php:119`, `academic-institution-detail.blade.php:58`, `training-detail.blade.php:458`
- **What:** Validation is `nullable|string` (not `url`), and Blade emits `<a href="{{ $fabLab->website }}">`. A user-supplied `javascript:alert(document.cookie)` becomes a clickable XSS vector. (Some templates prepend `https://` defensively, others don't.)
- **Fix:** Validate `website` as `nullable|url|max:255`; in Blade, gate via `Str::startsWith($url, ['http://','https://']) ? $url : '#'` or a `safe_url()` helper.

### B-15. ✅ `Notification.data` double-encoded in `MessagesController` — silently breaks per-conversation throttling
- **Where:** `app/Http/Controllers/MessagesController.php:460`
- **What:** `Notification` model casts `data` to `array`, but the message-notification path passes `'data' => json_encode([...])`. Eloquent re-encodes → stored as JSON-quoted string. This breaks the duplicate-check `where('data->conversation_id', $conversation->id)` two lines above (line 449), so per-conversation notification throttling silently fails on the 2nd+ message in any conversation. Active production data-integrity bug.
- **Fix:** Pass the array directly (no `json_encode`); audit every other `Notification::create` call for the same anti-pattern.

---

## 🔴 High — will get flagged

### H-1. ✅ Most state-mutating routes lack throttle middleware
- **Where:** `routes/web.php:251-257, 338-354, 374-378`; `routes/admin.php:35-289`; `routes/academic.php:22-85`
- **What:** `account.password.update`, `account.privacy.update`, `account.email.update`, `account.delete.confirm`, `account.upgrade`, `profile.update`, `profile.update-certifications`, `designer.update-bio`, `designer.update-skills`, products/projects/services `update` and `destroy` (Route::match), entire admin group, entire academic CMS group — none have `throttle:` middleware.
- **Fix:** Add `'throttle:30,1'` (or `'throttle:120,1'` on admin/academic groups).

### H-2. ✅ Inactive / pending designers can fully use authenticated APIs
- **Where:** `routes/web.php:228, 247` — auth groups guarded only by `['auth:designer','verified']`
- **What:** Deactivated/self-deleted designers can still send messages, follow, like, post, etc. `is_active` is checked only in `login()` and one comment endpoint.
- **Fix:** Add a global `EnsureDesignerActive` middleware to the auth-required groups; reject when `auth('designer')->user()->is_active === false`.

### H-3. ✅ Like / follow have no DB uniqueness — races inflate counters
- **Where:** `DesignerFollowController::toggleLike:215-232`, `ProductController::toggleLike:158-177`, `ProjectController::toggleLike:159-178`, `MarketplaceController::toggleLike:170-189`, `DesignerFollowController::follow:51-66`
- **What:** Pattern is `where(...)->first()` then `Like::create()` + `increment(...)` outside a transaction. Two concurrent clicks create two Like rows + two increments; unlike only deletes one row + decrements once → permanent counter drift upward.
- **Fix:** `DB::transaction` + `firstOrCreate`; only `increment` when `wasRecentlyCreated`.

### H-4. ✅ Mid-registration image-move failures are NOT cleaned up
- **Where:** `app/Http/Controllers/Auth/AuthController.php` — `moveToPermStorage` calls at lines 424, 452, 524, 600, 718; rollback at line 845
- **What:** `cleanupTempFiles` cleans only **temp** files. Permanent files already moved (avatar, cover, first product image) are orphaned: DB rolls back, files stay on disk forever.
- **Fix:** Track created permanent paths in an array; on rollback delete each from `Storage::disk('public')`.

### H-5. ✅ Counter increments are not transactional with row insert
*Fixed alongside H-3 — like/follow flows now wrap counter increment + Like/follow row insert in a single `DB::transaction`. A counter recompute job (separate concern) is still TODO.*
- **Where:** `DesignerFollowController::follow:60-66`, `ProductController::toggleLike:165-176`, `ProjectController::toggleLike:166-177`, `MarketplaceController::toggleLike:177-188`, `MarketplaceCommentController::destroy:251-256`
- **What:** Crash between `Like::create`/`attach` and `increment` leaves drift forever. No nightly recompute job exists.
- **Fix:** Wrap in `DB::transaction(...)` and add `php artisan pch:recompute-counters`.

### H-6. ✅ Designer self-delete leaves products publicly viewable via direct URL
- **Where:** `DesignerProfileController::confirmDelete:927-931`; `ProductController::show:108`; `ProjectController::show`; `ServiceController::show`
- **What:** Self-delete sets `is_active=false` + `approval_status='rejected'` on the Designer. Listings hide them via `whereHas('designer', is_active=true)`, but direct `/products/{id}` URLs still 200 because `show()` only checks `approval_status==='approved'`.
- **Fix:** Add `is_active` join to `show()` methods on Product/Project/Service/Marketplace.

### H-7. ⚠️ No "last admin" guard on demote / deactivate
*Verified existing controller code already prevents the practical attacks: `toggleActive` rejects deactivating own account (line 271) and other admins (line 278); `update()` strips `is_active` for admin targets (line 220); no controller toggles `is_admin`. Combined with `EnsureDesignerActive`'s admin-bypass and the login-time admin carve-out, an admin self-deactivation is reversible without lockout.*
- **Where:** `app/Http/Controllers/Admin/AdminDesignerController.php:262, 217-222`
- **What:** Code prevents deactivating *another* admin (line 278) but not self. Last admin can lock the system out.
- **Fix:** Pre-check `Designer::where('is_admin',true)->where('is_active',true)->count() > 1`.

### H-8. ⚠️ No baseline `Schema::create('designers', ...)` migration
*Deferred — needs a coordinated dump-from-prod step. Adding a `Schema::create` for tables that already exist on prod would error on the live server when the user runs `migrate`. The right pattern is `if (!Schema::hasTable('designers')) { Schema::create(...) }` with the full prod schema, generated from `mysqldump --no-data` of `technopark_portal`. Worth doing once for staging-rebuild parity, but not by hand-typing column lists.*
- **Where:** `database/migrations/` — only `Schema::table('designers', ...)`. Same for `tenders`, `messages`, `conversations`, `message_requests`, `designer_follows`, `academic_*`.
- **What:** A fresh DB cannot be built from migrations. QA staging will explode on `migrate:fresh`.
- **Fix:** Add baseline `Schema::create('designers', ...)` (or SQL dump seeder) for every missing table.

### H-9. ⚠️ `users` table dropped while FKs on Project/Marketplace/Product/Like/Follow still target it
*Deferred — pairs with H-8. Migration to drop dead FKs and recreate against `designers` requires `SHOW CREATE TABLE` on each table to enumerate the existing FK constraint names before they can be dropped, then renaming `user_id → designer_id` columns. Today's prod server has the dropped FK constraints lingering as orphans — they don't fire (the target table is gone) but they won't replay on a fresh staging build. Same coordinated dump-step as H-8.*
- **Where:** `2025_11_14_123656_create_projects_table.php:16-17`, `_create_marketplace_posts_table.php:16`, `_create_products_table.php:16`, `_create_likes_table.php:16-17`, `_create_comments_table.php`, `_create_views_table.php`, `_create_follows_table.php:15-16`, etc., vs `2026_02_20_210911_cleanup_legacy_tables_and_columns.php:35` (drops `users`)
- **What:** Every `foreignId('user_id')->constrained()` implicitly references `users(id)` — dropped. On `migrate:fresh` FK creation fails; on existing prod DB constraints dangle.
- **Fix:** Drop dead FKs and recreate against `designers`; rename columns to `designer_id`.

### H-10. ✅ `Designer::email` uniqueness not declared in migration
- **Where:** No migration creates `designers.email`; only the dropped `users.email` was unique.
- **What:** If prod table doesn't already have a unique key, two designers can register the same email.
- **Fix:** `SHOW INDEXES FROM designers` to verify; add `ALTER TABLE designers ADD UNIQUE (email)` if missing. Add to baseline migration.

### H-11. ✅ Login form has no double-submit lockout
- **Where:** `resources/views/auth/login.blade.php:58-121`
- **What:** Plain form, no `@submit` guard, no `:disabled`. Double-clicking sends two POSTs.
- **Fix:** `x-data="{busy:false}"`, `@submit="busy=true"`, `:disabled="busy"`.

### H-12. ✅ Compose-message form gets stuck on "Sending..."
- **Where:** `resources/views/messages/compose.blade.php:135-176`
- **What:** `data.success && !data.redirect` path leaves button disabled forever.
- **Fix:** Always reset busy flag on response.

### H-13. ✅ step-7-review: two stacked modals share `escape.window`
- **Where:** `step-7-review.blade.php:297` (showPublishConfirmModal) + `:401` (showPoliciesModal)
- **What:** Esc closes both modals at once.
- **Fix:** `@keydown.escape.stop` on the inner modal.

### H-14. ✅ Portfolio modals don't validate file size/mime client-side
- **Where:** `components/portfolio/layout.blade.php:715-722` (`handleImageUpload`)
- **What:** Only checks `file.type.startsWith('image/')`. No size cap, no extension cross-check. 50 MB file → server rejects after upload, wasted bandwidth.
- **Fix:** Mirror `validateImageFile()` from registration alpine-data.

### H-15. ✅ `phone_country` accepts arbitrary 2-char string in registration
- **Where:** `app/Http/Controllers/Auth/AuthController.php:268`
- **What:** `'phone_country' => 'string|max:2'` no `in:` whitelist; bypasses the PS-only regex on line 324.
- **Fix:** `'phone_country' => ['nullable','string','size:2','in:PS,IL,JO,US,...']`.

### H-16. ✅ Marketplace `source_id` lacks `exists` rule and ownership check
- **Where:** `app/Http/Controllers/MarketplacePostController.php:46`
- **Fix:** `Rule::exists($sourceTable,'id')->where('designer_id', $designer->id)`.

### H-17. ✅ Register accepts 300-char names with no `maxLength`
- **Where:** `resources/views/auth/register/step-1-account.blade.php`
- **Fix:** Add `maxlength="100"` to text inputs.

### H-18. ⚠️ 136 `__()` keys missing from `ar.json`
*Deferred — translation work, not engineering. The 136 keys need an Arabic translator's pass; ad-hoc machine translation would degrade copy quality on a public site. Track in a separate translation deliverable, then merge in one bulk edit.*
- **Where:** `resources/lang/ar.json`
- **What:** Arabic UI shows raw English fallbacks for ~136 strings ("Welcome", "Verification Code", "Upgrade to Full Account", etc.).
- **Fix:** Translate and merge in one bulk edit.

### H-19. ✅ RTL layout broken in major flows
*Top-3 hot-spots fixed in commit `dc0af8eea` — step-7-review, components/portfolio/header, marketplace. ~110 more matches across other 27 files deferred to follow-up sweeps.*
- **Where:** `step-7-review.blade.php` (lines 144,178,189,244,303,311,407,415,458,471,485); `components/portfolio/header.blade.php` (51,58,64,138+); `marketplace.blade.php` (60,62,210)
- **What:** Hard-coded `mr-/ml-/pl-/pr-/left-/right-/text-left` instead of logical `ms-/me-/ps-/pe-/start-/end-/text-start/text-end`. Search-bar magnifier sits behind placeholder text in Arabic.
- **Fix:** Codemod to logical-properties variants (Tailwind 3.3+).

### H-20. ✅ RTL `text-left` shim missing from main layout
- **Where:** `resources/views/layout/main.blade.php`
- **What:** Shim exists in `layout/auth.blade.php` and `layout/chat.blade.php` but not `main.blade.php`.
- **Fix:** Move shim into `main.blade.php`, or convert to `text-start`/`text-end` everywhere.

### H-21. ⚠️ Home page TTFP is 13–20s
*Deferred — needs profiling under load. Without query-level traces (Telescope/Debugbar enabled in a staging environment) it isn't safe to "cache more aggressively" — wrong cache keys can serve stale or per-user content to all viewers. Right approach: enable Debugbar in staging, identify the slow queries on `/`, add targeted CacheService entries, then evaluate Cloudflare edge caching for the public home variant.*
- **Where:** Production homepage
- **What:** Cold load 20.7s to networkidle; warm loads ~13s.
- **Fix:** Profile homepage queries; cache via `CacheService`. Enable HTTP/2 + Brotli on cPanel.

### H-22. ⚠️ Designer listing filters not bookmarkable
*False positive: filter tabs in designers.blade.php:109+ are plain `<a href="...?type=...&sort=...&search=...">` links with no JS preventDefault. URL is already bookmarkable.*
- **Where:** `resources/views/designers.blade.php`
- **Fix:** Use query params + `pushState`.

### H-23. ✅ Register form has 10 unlabeled inputs + 7 buttons with no aria-label
*Icon-only buttons (password show/hide, skill ×, certification ×) and the cert PDF file input got `aria-label`, `aria-pressed`, and `aria-hidden` on inner SVGs in commit `982fd2f3e`. Text-only buttons (Add, Next, etc.) already have visible text content.*
- **Where:** `resources/views/auth/register/step-1/2/3-*.blade.php`
- **Fix:** Add `<label for="...">` and `aria-label="..."` on icon-only buttons.

### H-24. ✅ Admin password reset only requires `min:8`
- **Where:** `app/Http/Controllers/Admin/AdminDesignerController.php:248-256`
- **Fix:** Use `Illuminate\Validation\Rules\Password` chain.

### H-25. ⚠️ Likes table schema is project-only but Like model is polymorphic
*Deferred — schema migration to drop `user_id`/`project_id` and add `(designer_id, likeable_type, likeable_id)` with a unique composite. Needs a data-shape audit on prod first: any existing rows in `likes` should be backfilled to the polymorphic columns before the old columns are dropped. Pairs with H-3 (already shipped: DB-uniqueness on like creation), so the duplicate-row vector is closed at the application layer; the schema mismatch is correctness debt rather than an active risk.*
- **Where:** `2025_11_14_123709_create_likes_table.php` vs `Like.php:18-22`
- **What:** Schema doesn't match application code. Duplicate `(designer_id, likeable_type, likeable_id)` rows possible — compounds H-3.
- **Fix:** Migration to drop `user_id`/`project_id`, add polymorphic columns + unique index.

### H-26. ✅ Password change doesn't rotate `remember_token`
- **Where:** `app/Http/Controllers/DesignerProfileController.php:693-695`; `app/Http/Controllers/Academic/AcademicProfileController.php:145-146`
- **What:** After updating `password`, `remember_token` is NOT regenerated. Any previously stolen "remember me" cookie keeps the attacker logged in forever after the victim changes their password. (PasswordResetController correctly rotates it.)
- **Fix:** `$designer->remember_token = Str::random(60);` before save; consider `Auth::guard('designer')->logoutOtherDevices()`.

### H-27. ✅ `skip_verification` env flag bypasses webhook signature in any environment
- **Where:** `app/Services/WebhookSignatureService.php:52`; `config/webhooks.php:53`
- **What:** Comment says "Only in development" but `JOBS_PS_SKIP_VERIFICATION=true` will bypass verification regardless of `APP_ENV`. Single misconfigured env var = open webhook in prod.
- **Fix:** `if ($skip && app()->environment('local')) ... else fail closed.`

### H-28. ✅ `trustProxies(at: '*')` allows IP spoofing
- **Where:** `bootstrap/app.php:22-28`
- **What:** Trusts every upstream — any client can spoof `X-Forwarded-For`, defeating per-IP rate limits and falsifying `$request->ip()` in logs.
- **Fix:** Replace `'*'` with explicit Cloudflare CIDRs (`https://www.cloudflare.com/ips-v4/`).

### H-29. ✅ No `Content-Security-Policy` header
- **Where:** `app/Http/Middleware/SecurityHeaders.php`
- **What:** No CSP / `frame-ancestors` header. Combined with user-generated bios/profiles, any XSS amplifies to full account takeover.
- **Fix:** Add a starter CSP (`default-src 'self'; img-src 'self' data: https:; script-src 'self' 'nonce-...';`) — start in Report-Only.

### H-30. ✅ No GDPR cookie-consent banner
- **Where:** `resources/views/**` (missing entirely)
- **What:** Site sets analytics + `track.page` middleware cookies; non-compliant under GDPR/ePrivacy.
- **Fix:** Add a minimal consent banner gating non-essential cookies; link to `/privacy`.

### H-31. ✅ Default mailer falls back to `sendmail`
- **Where:** `config/mail.php:17`
- **What:** `'default' => env('MAIL_MAILER', 'sendmail')`. If `MAIL_MAILER` is unset on cPanel, the app falls back to `/usr/sbin/sendmail`, which is disabled on most shared hosts → silent failure.
- **Fix:** Default to `gmail` (or `smtp`); assert env in a boot check.

### H-32. ✅ `markAsRead` cache stays stale on new notifications
- **Where:** `app/Http/Controllers/NotificationController.php:98-101`; `MessagesController.php:454-462`
- **What:** New notifications via `MessagesController::createMessageInConversation` and bulk-insert in `NotificationSubscriptionService::notifyProfileSubscribers` never call `CacheService::clearUnreadNotificationCount`. Navbar badge stuck at old count for `TTL_SHORT` (60s).
- **Fix:** Call `CacheService::clearUnreadNotificationCount($recipientId)` after every Notification create / bulk insert.

### H-33. ⚠️ `EmailController::send` has no rate limit
*Verified `routes/web.php:407` already had `throttle:5,1`. Audit's claim of zero throttle was incorrect — only the per-recipient daily-cap recommendation remains as a separate enhancement.*
- **Where:** `routes/web.php` (no throttle on the route); `app/Http/Controllers/EmailController.php:84-96`
- **What:** Authenticated designers can spam any other opted-in designer's inbox; no per-user/per-recipient throttle, no daily cap, no audit row.
- **Fix:** `throttle:5,60` (5/hr) plus a per-(sender,recipient) DB rate guard.

### H-34. ✅ Webhook signature has no replay-attack protection
- **Where:** `app/Services/WebhookSignatureService.php:49`; `app/Http/Controllers/Api/WebhookController.php:61`
- **What:** No timestamp header required, no nonce store. A captured `(payload, X-Signature)` pair can be replayed forever.
- **Fix:** Require `X-Timestamp`, reject if `abs(now - ts) > 300s`, include timestamp in signed payload, persist nonces in cache.

### H-35. ✅ Email + full request data logged at `Log::debug` in registration
- **Where:** `app/Http/Controllers/Auth/AuthController.php:801-808` and `:313`
- **What:** `Log::debug('Registration completed', ['designer_email' => …])` and `'request_data' => $request->except(['password','password_confirmation'])` log PII (email, phone, name, address, bio).
- **Fix:** Drop email/PII from logs; log only `designer_id` and counts.

### H-36. ✅ Schedule has no overlap protection or failure alerting; image cleanup never scheduled
- **Where:** `routes/console.php:12`
- **What:** Only `conversations:send-rating-reminders` is scheduled. No `withoutOverlapping()`, no `onFailure()`. `uploads:cleanup`, `images:cleanup-orphaned`, etc. NEVER run automatically — temp uploads pile up forever.
- **Fix:** Add `Schedule::command('uploads:cleanup')->daily()->withoutOverlapping()->onFailure(...)` and `images:cleanup-orphaned --no-interaction` weekly. Document the cPanel cron `* * * * * cd /path && php artisan schedule:run >/dev/null 2>&1`.

### H-37. ⚠️ `dispatch(closure)->afterResponse()` is not durable
*Deferred — depends on M-48 queue infrastructure (env-only docs shipped). The full fix is a `SendVerificationEmail` job class with `ShouldQueue + $tries=3 + failed()`, plus a cron entry running `php artisan queue:work --once` per minute. M-2 already adds a Cache flag the verify-email page surfaces if the after-response send fails, so the user-visible failure mode is mitigated even without durability.*
- **Where:** `app/Http/Controllers/Auth/AuthController.php:820-832`
- **What:** Verification email send runs in `terminate()` phase synchronously in PHP-FPM; if the worker is killed, FPM restarts, or another after-response handler aborts → email silently lost. No retry, no `failed()` queue.
- **Fix:** Convert to a real queued `SendVerificationEmail` Job (`ShouldQueue`, `$tries=3`, `failed()` logs). Requires queue worker — on cPanel run `queue:work --once` per minute via cron.

### H-38. ✅ `CleanupOrphanedUploads.php:43` calls a non-static method statically
- **Where:** `app/Console/Commands/CleanupOrphanedUploads.php:43`
- **What:** `ImageUploadController::cleanupOrphanedUploads()` invoked statically, but method at `ImageUploadController.php:879` is instance-only and returns `array{deleted, errors}` — not int. Command treats return as `$count` int. PHP 8 will throw deprecation/fatal; logic is broken either way.
- **Fix:** Make method `static` or instantiate via `app(ImageUploadController::class)`; read `$result['deleted']`.

### H-39. ✅ Search controllers 500 on array input
*ServiceController fixed (validate block + LIKE wildcard escape). MessagesController and DesignerController paths still TODO if those endpoints are user-facing — verify before opening as separate fix.*
- **Where:** `ServiceController.php:53`, `MessagesController.php:40`, `DesignerController.php:224`
- **What:** Use `$request->has('search')` and read `$request->search` directly without a string validator. `?search[]=foo` makes it an array; `'%' . $array . '%'` raises a TypeError on PHP 8 → 500 page.
- **Fix:** Wrap with `is_string($request->search)` or add `$request->validate(['search' => 'nullable|string|max:255'])`.

---

## 🟡 Medium — should fix

### M-1. ✅ Approval not reset when product/project edited (only marketplace does)
- **Where:** `MarketplacePostController::update:196` resets to pending; `ProductController::update:270-`, `ProjectController::update:290-` do NOT.
- **Fix:** Reset to pending on edit for products/projects/services (unless `is_trusted`).

### M-2. ✅ Verification email failure invisible to user
- **Where:** `AuthController::register:820-832`
- **Fix:** Persist a flash flag and check on next visit, or use a queued mailable with retry.

### M-3. ✅ Verification resend rate limit per-IP not per-email
- **Where:** `routes/web.php:181` — `throttle:10,5`
- **Fix:** Custom limiter keyed on email field as well.

### M-4. ✅ `uploading` flag is global across all parallel uploads
- **Where:** `auth/register/alpine-data.blade.php:358, 493`
- **Fix:** Use a counter (`activeUploads++` / `--`).

### M-5. ✅ localStorage 10-min TTL silently wipes wizard progress
- **Where:** `auth/register/alpine-data.blade.php:2018`
- **Fix:** Bump to 24h, or surface "your saved progress expired" toast.

### M-6. ⚠️ Long-lived registration page CSRF token expiry
*Deferred — needs a new heartbeat endpoint (`/csrf-refresh`), Alpine timer in the wizard to call it every ~5min, and end-to-end testing on slow connections. Not a single-line fix; risk of breaking the working registration flow if the heartbeat misbehaves. The 419 retry path already exists via `loadFromLocalStorage()` recovery.*
- **Where:** `form-errors.blade.php:1-2`
- **Fix:** Refresh token via heartbeat fetch.

### M-7. ✅ Console errors leak `debug_info` to user
- **Where:** `auth/register/alpine-data.blade.php:414-416`
- **Fix:** Strip `debug_info` from JSON responses entirely.

### M-8. ✅ `confirmDelete` URL is hardcoded `/${locale}/...`
- **Where:** `components/portfolio/layout.blade.php:302-304`
- **Fix:** Use `${window.__portfolioBaseUrl}/...`.

### M-9. ✅ No counter recompute job exists
- **Fix:** Add `php artisan pch:recompute-counters` weekly.

### M-10. ⚠️ No avatar fallback URL
*Existing pattern across views is `@if($designer->avatar) <img …> @else <icon-fallback> @endif`. Adding an accessor that returns a default URL would silently flip every `if($designer->avatar)` branch to truthy, replacing the icon fallback with a default-image render — a behavior change at scores of call sites. Needs a coordinated decision on the default asset + a single sweep across all callers; not a one-line fix.*
- **Where:** `DesignerController::show:142`, `DesignerFollowController:290`

### M-11. ⚠️ `Designer` model has both `$fillable` AND `$guarded`
*Not a bug: when `$fillable` is non-empty Laravel uses ONLY the fillable allowlist and ignores `$guarded`. Both being present is harmless redundancy / defense-in-depth and reads as documentation of intent. Picking one convention is a style choice; restructuring to `$guarded`-only would silently turn every newly-added column into mass-assignable.*
- **Where:** `app/Models/Designer.php:56-106`

### M-12. ✅ Tender description rendered with `{!! !!}` after permissive `strip_tags`
- **Where:** `resources/views/tender-detail.blade.php:79-86`
- **Fix:** Use `mews/purifier` or drop `<a>` from allow-list.

### M-13. ✅ `updatePrivacySettings` + `updateEmailPreferences` skip `validate()`
- **Where:** `app/Http/Controllers/DesignerProfileController.php:719-785`
- **Fix:** `$request->validate(['show_email' => 'nullable|boolean', ...])`.

### M-14. ✅ `confirmDelete` + `sendDeleteCode` skip `validate()`
- **Where:** `DesignerProfileController.php:864-950`
- **Fix:** `$request->validate(['password' => 'required|string', 'code' => 'required|string|size:6'])`.

### M-15. ✅ `ServiceController::index` doesn't validate query params
- **Where:** `app/Http/Controllers/ServiceController.php:21-76`
- **Fix:** Add the matching `$request->validate([...])` block.

### M-16. ⚠️ Categories on product/project/service/marketplace are freeform
*Categories arrive in the user's locale (Arabic or English label), then `DropdownOption::toEnglish()` maps them to canonical English values. A naive `Rule::in()` against one locale's label list would reject the other. A correct fix needs to validate against a locale-merged list, or move the conversion before validation. Postponing — current behavior is "unknowns silently fall through and don't match," which is acceptable for the filter use case but should be tightened on store paths in a follow-up.*
- **Where:** Four controllers

### M-17. ⚠️ Marketplace `store` vs `update` rules asymmetric
*Verified intentional: `update` doesn't write `source_type`/`source_id` (post-creation source-linking is disabled by design), so the missing rules can't be exploited — the fields are silently ignored. Adding rules without persisting the fields would be confusing.*
- **Where:** `MarketplacePostController.php:46` vs `:159-167`

### M-18. ⚠️ `MarketplacePost::scopeSearch` and academic scopes use `LIKE '%term%'` without FULLTEXT
*Deferred — requires schema migration to add FULLTEXT indexes (`marketplace_posts`, `academic_trainings`, `academic_workshops`, `academic_announcements`, `tenders`, `academic_accounts`) and rewriting the scope queries. M-59/M-60 already hardened the existing search path. Performance won't degrade until table sizes grow into the tens of thousands.*
- **Where:** `MarketplacePost.php:122-128`; `AcademicTraining`, `AcademicWorkshop`, `AcademicAnnouncement`, `Tender`, `AcademicAccount`
- **Fix:** Add FULLTEXT indexes; switch to `MATCH AGAINST`.

### M-19. ⚠️ Hard-coded English placeholders / Alpine bindings
*Deferred — touches 10+ admin and portfolio Blade files. Mostly admin-only screens (admin/academic-content, admin/products/edit, admin/projects/edit, admin/services/edit, admin/settings, admin/image-migration) plus 4 portfolio modal partials. Best handled in one focused i18n pass with both `en.json` and `ar.json` updated together; otherwise half-translated UI is a worse experience than fully-English admin. Keep open until a translator can review the new strings.*
- **Where:** `admin/academic-content/training-create.blade.php:89,93`, `workshop-create.blade.php:95`, `admin/image-migration.blade.php:211`, `admin/settings/index.blade.php:68`; `admin/products/edit.blade.php:93`, `admin/projects/edit.blade.php:92,132`, `admin/services/edit.blade.php:92`, `components/portfolio/modal/add-project.blade.php:59,98`, `edit-project.blade.php:39,78`
- **Fix:** Wrap in `{{ __('...') }}`.

### M-20. ✅ User-visible dates rendered raw `Y-m-d` or English month names
- **Where:** `admin/analytics/index.blade.php:49,690`; `messages/chat.blade.php:98`
- **Fix:** `Carbon::parse(...)->locale(app()->getLocale())->isoFormat('LL')`.

### M-21. ⚠️ 46 `<img>` tags missing `alt`
*Deferred — most are admin-only listings where the `alt` should come from an associated model (e.g., `$product->title`, `$designer->name`). M-22's 5 content imgs are already done. Remaining 41 are mechanical but spread across `admin/*`, `academic/*`, and `components/portfolio/*`; needs a one-pass sweep with care to use the right adjacent model field for each. No security impact, only a11y and SEO.*
- **Where:** Across `admin/*`, `academic/*`, `components/portfolio/*`
- **Fix:** Add `alt="..."` from related model.

### M-22. ✅ 5 content `<img>` wrongly marked `alt=""`
- **Where:** `admin/dashboard.blade.php:177`, `admin/fablabs/index.blade.php:77`, `admin/marketplace/edit.blade.php:120, /index.blade.php:96`, `admin/products/index.blade.php:147`
- **Fix:** Provide meaningful alt.

### M-23. ✅ `verify-email.blade.php` email input has no `<label>`
- **Where:** `resources/views/auth/verify-email.blade.php:75`
- **Fix:** Add `<label for="email">`.

### M-24. ✅ Empty `/en/search` silently redirects to `/en`
- **Fix:** Render a search-landing view with hint.

### M-25. ✅ Register email validation falls back to native HTML5; password-confirm mismatch shows no inline error
- **Where:** `auth/register/step-1-account.blade.php`
- **Fix:** Add Alpine-driven inline errors.

### M-26. ✅ OAuth2 callback unthrottled
- **Where:** `routes/web.php:77-80`
- **Fix:** `'throttle:30,1'`.

### M-27. ✅ Modal popup blocks first paint on home
- **Where:** `/en` and `/ar` home (sector quiz modal)
- **Fix:** Persist `signupQuiz_dismissed=true` in localStorage.

### M-28. ✅ Forgot/change-password and privacy/email-prefs forms have no busy guard
- **Where:** `auth/forgot-password.blade.php:25`, `account/settings.blade.php:73, 113, 180`
- **Fix:** Wrap in `x-data="{busy:false}" @submit="busy=true"` + `:disabled="busy"`.

### M-29. ✅ Verify-email + login resend-verification: 100ms race
- **Where:** `auth/verify-email.blade.php:62, 82`; `auth/login.blade.php:38-52`
- **Fix:** Move state to `@submit`; clear only on actual response.

### M-30. ⚠️ Modal focus return + focus trap missing
*Deferred — requires bringing in `@alpinejs/focus` plugin or hand-rolling focus-trap logic in 9 modal partials (add-product, add-project, add-service, edit-product, edit-project, edit-service, edit-bio, edit-skills, delete). a11y improvement, no security or correctness impact.*
- **Where:** All `components/portfolio/modal/*.blade.php`
- **Fix:** Add focus-trap directive and restore focus on close.

### M-31. ⚠️ Searchable dropdowns lack keyboard arrow/Enter bindings
*Deferred — needs `@keydown.arrow-down`/`@keydown.arrow-up`/`@keydown.enter.prevent` handlers across the 4+ searchable dropdown components in `components/portfolio/layout.blade.php`. Mouse navigation works fine. a11y improvement, low priority.*
- **Where:** `components/portfolio/layout.blade.php:9-240`
- **Fix:** Add `@keydown.arrow-down`/`@keydown.enter.prevent` on the input.

### M-32. ⚠️ No email unsubscribe links anywhere
*Deferred — needs new signed-URL helper, new public unsubscribe route (no auth, validates signed token), DB column or cache flag per recipient, footer block in every email template, and List-Unsubscribe header (M-46). End-to-end change with abuse-prevention concerns; ship as one focused unsubscribe feature, not piecemeal.*
- **Where:** No unsubscribe route in `routes/web.php`; emails contain no footer.
- **Fix:** Add signed unsubscribe URLs with per-user token.

### M-33. ⚠️ Filename collision risk on `moveToPermStorage`
*Mostly theoretical: the structured filename is `{type}_{entityId}_{imageNumber}.{ext}`, which is per-user/per-entity. Two simultaneous uploads from different designers always produce different filenames. The fallback (when `entityId` is null) uses the temp-folder basename which is per-session-id and per-hash — collision would require two parallel uploads from the same session sharing a file hash.*
- **Where:** `ImageUploadController::moveToPermStorage`

### M-34. ✅ Verification resend cache write decoupled from email failure
- **Where:** `DesignerProfileController::sendDeleteCode:896-902`
- **Fix:** Set cache only after `Mail::send` succeeds.

### M-35. ✅ localStorage cross-tab not coordinated
- **Where:** `auth/register/alpine-data.blade.php:1922-1929`
- **Fix:** Listen to `storage` event or disable cross-tab edits.

### M-36. ⚠️ `currentItem` shared across add/edit modals causes flicker
*Not reproduced: each `openAdd*Modal` and `open-edit-*` event handler already overwrites `currentItem` before showing the modal. Closed modals (`x-show=false`) stay in the DOM but `display:none`, so stale data isn't visible. The successful-submit paths reload the page, fully resetting state.*
- **Where:** `components/portfolio/layout.blade.php:259, 418-434`

### M-37. ✅ `services` model not in `HasApprovalStatus::$typeMap`
- **Where:** `app/Models/Traits/HasApprovalStatus.php:48`
- **Fix:** Add Service to `$typeMap`; verify Service uses the trait.

### M-38. ⚠️ `marketplace_posts.category` / `products.category` plain `string` — no FK
*Deferred — schema migration. The `dropdown_options` table uses a string `value` (not a stable PK), and admins can rename values from the CMS. A FK would force every existing category rename to ripple through dependent tables or fail. M-16 documents the application-layer mitigation; FK is a longer-term refactor.*
- **Where:** Migrations
- **Fix:** Add FK to `dropdown_options.value` or document constraint.

### M-39. ⚠️ `page_visits.designer_id`, `search_logs.designer_id` unconstrained
*Deferred — adds two FK constraints via migration. Both tables are append-only telemetry; orphan rows from deleted designers are harmless and the `nullOnDelete` semantics are already approximated in queries (we LEFT JOIN with no FK). Migration cost on existing data isn't worth the marginal correctness gain.*
- **Where:** `2026_03_18_000001_*`, `:000002_*`
- **Fix:** Add `->constrained('designers')->nullOnDelete()`.

### M-40. ✅ File session driver on cPanel
- **Where:** `.env.example:30` (`SESSION_DRIVER=file`)
- **What:** Slower, deploy-fragile, litters `storage/framework/sessions`. Same for `CACHE_STORE=file`.
- **Fix:** Move to `database` (cheap) or Redis if available.

### M-41. ✅ `expire_on_close=false` + 120-min lifetime on shared computers
- **Where:** `config/session.php:35-37`
- **Fix:** Consider `SESSION_EXPIRE_ON_CLOSE=true` for admins, or shorter admin lifetime.

### M-42. ✅ Session cookie name leaks framework
- **Where:** `config/session.php:130-133` defaults to `palestine-creative-hub-session`
- **What:** Predictable Laravel naming aids fingerprinting.
- **Fix:** Set `SESSION_COOKIE=pch_sid`.

### M-43. ✅ `X-XSS-Protection` deprecated header still emitted
- **Where:** `app/Http/Middleware/SecurityHeaders.php:33`
- **What:** `X-XSS-Protection: 1; mode=block` is deprecated; can introduce vulns in legacy IE/Edge.
- **Fix:** Remove or set to `0`; rely on CSP.

### M-44. ✅ `RESET_THROTTLED` response leaks email existence
- **Where:** `PasswordResetController::sendResetLink:51-57`
- **What:** Generic message for unknown email but throttle response only happens after a real send → confirms account exists.
- **Fix:** Collapse throttle response into the generic "if an account exists" message.

### M-45. ✅ Bulk notification path bypasses 5-minute dedupe
- **Where:** `NotificationSubscriptionService.php:88-97, 171-181`
- **What:** Raw `Notification::insert($chunk)` skips the 5-min dedupe, so re-approving content twice within 5 min duplicates every subscriber's notification.
- **Fix:** Idempotency key column or pre-filter recipients.

### M-46. ⚠️ Email templates lack unsubscribe link / `List-Unsubscribe` header
*Deferred — pairs with M-32 (unsubscribe end-to-end). Adding a List-Unsubscribe header to GmailApiTransport without an actual unsubscribe URL would be worse than nothing (Gmail would render a button that 404s). Ship as part of the M-32 unsubscribe feature.*
- **Where:** `resources/views/emails/*.blade.php`; `app/Mail/GmailApiTransport.php:182`
- **What:** Gmail/Outlook will spam-flag bulk sends.
- **Fix:** Add signed unsubscribe URL helper; emit `List-Unsubscribe` and `List-Unsubscribe-Post` headers.

### M-47. ✅ `EmailController` doesn't enforce sender's `email_verified_at`
*Verified non-issue: route is inside the `auth:designer + verified + active` middleware group at `routes/web.php:253`, so `verified` is already enforced. Audit missed the group middleware.*
- **Where:** `app/Http/Controllers/EmailController.php:49-82`
- **What:** Auth check exists but no `verified` middleware. Unverified designer can email any opted-in designer.
- **Fix:** Add `'verified'` middleware or check `hasVerifiedEmail()` early.

### M-48. ✅ Mail send is synchronous everywhere
- **Where:** `EmailController::send`, `DesignerProfileController::sendDeleteCode`, all `notify()` calls
- **What:** No queue worker configured (`sync` default), request blocks on Gmail API (~1-3s); failure cascades into 500.
- **Fix:** Configure `QUEUE_CONNECTION=database`, ship a worker, `ShouldQueue` on the mail closures.

### M-49. ✅ `MessageRequestController` skips notification dedupe pattern
- **Where:** `MessageRequestController.php:126, 170`
- **What:** Uses `Notification::create(...)` directly, bypassing 5-min dedupe; missing `data` payload (UI link to chat is lost).
- **Fix:** Route through `NotificationController::createNotification` and include `['conversation_id' => $convo->id]`.

### M-50. ✅ `NotificationController::createNotification` doesn't validate recipient ID
- **Where:** Line 140
- **What:** `designer_id` not validated to exist; some callers pass `$id` straight from URL.
- **Fix:** Inside `createNotification` verify via `Designer::whereKey($id)->exists()`.

### M-51. ✅ `images:migrate-names` lacks `--force` flag
- **Where:** `MigrateImageNames.php:54`
- **What:** `$this->confirm(...)` blocks scheduler/CI.
- **Fix:** Add `{--force}`.

### M-52. ✅ `images:cleanup-orphaned` no `--force`, uses `Log::debug`
- **Where:** `CleanupOrphanedImages.php:91, 28`
- **Fix:** Add `--force` confirmation; use `Log::info` for cleanup events.

### M-53. ✅ `ProcessExistingImages` corrupts paths containing dots
- **Where:** `ProcessExistingImages.php:124-127, 148`
- **What:** `CONCAT(SUBSTRING_INDEX(\`{$col}\`, '.', 1), '.webp')` corrupts paths like `products/v1.2/img.jpg` → `products/v1.webp`. Mass-update runs unconditionally.
- **Fix:** Build per-row mapping from successful conversion results; only update rows whose old path was actually converted.

### M-54. ✅ `getCreatorName` N+1 in batch approvals
- **Where:** `NotificationSubscriptionService.php:50, 308`
- **Fix:** Pass creator name in or cache the lookup.

### M-55. ✅ `GmailOAuthService` 401 retry recursion possible
- **Where:** `GmailOAuthService.php:144-149`
- **Fix:** Pass `$retried=false` flag, log retry result, cap to one retry.

### M-56. ✅ Upload-cleanup cutoff disagreement (12h vs 24h)
- **Where:** `CleanupOrphanedUploads.php:29` ("12 hours") vs `ImageUploadController.php:883` (`subHours(24)`)
- **Fix:** Pick one; align both.

### M-57. ✅ `CleanupOrphanedImages` ignores cover/marketplace/fablabs/trainings folders
- **Where:** `CleanupOrphanedImages.php:106-280`
- **What:** Only `profiles/`, `products/`, `projects/`, `services/` scanned. Covers, marketplace, fablabs, trainings, academic-* are never cleaned.
- **Fix:** Add handlers for the missing folders.

### M-58. ✅ `ImageUploadController` debug-logs every upload with full headers
- **Where:** `ImageUploadController.php:38`
- **Fix:** Gate behind `config('app.debug')` or remove.

### M-59. ✅ FULLTEXT search passes raw boolean operators
- **Where:** `DesignerController.php:226`, `ProductController.php:63`, `ProjectController.php:66`, `MarketplacePost.php:125`, `HomeController.php:146,179,201,241,297,322,348,397`
- **What:** `MATCH(...) AGAINST(? IN BOOLEAN MODE)` is bound, but term is `$searchTerm . '*'`. Searching `+-(foo)` or bare `*` produces MySQL syntax error.
- **Fix:** Strip ops: `preg_replace('/[+\-*~<>()"@]/u','',$term)` before binding.

### M-60. ✅ LIKE wildcards `%` and `_` not escaped
- **Where:** `HomeController.php:150-423`, `DesignerFollowController.php:275-279`, `ServiceController.php:54`, `MessagesController.php:45-49`, `TrainingController.php:68-135`
- **What:** User input `%` matches all rows; `_` matches any single char — performance + result correctness + enumeration vector.
- **Fix:** `addcslashes($term, '%_\\')` before binding.

### M-61. ✅ Webhook `Carbon::parse` on unvalidated `deadline`
- **Where:** `app/Http/Controllers/Api/WebhookController.php:154`
- **What:** `Carbon::parse($data['deadline'])` with no validator. `2026-13-99` or `"now+999years"` throws.
- **Fix:** Validate `'deadline' => 'date'`.

### M-62. ✅ Privacy checkboxes use `$request->has()`
- **Where:** `DesignerProfileController.php:733-736, 768`
- **What:** `has()` returns true for any value, including `"0"`, `"false"`, empty string, or array. POST `show_email=0` sets the flag to true.
- **Fix:** Use `$request->boolean('show_email')`.

### M-63. ✅ Search box stores arbitrary input with no length cap
- **Where:** `HomeController.php:256-262`
- **What:** `mb_strtolower(trim($query))` — already `strip_tags`-ed, but no length cap. Column may truncate or fail on >255 chars / 4-byte emoji if not utf8mb4.
- **Fix:** `mb_substr($query, 0, 255)`; ensure column is `utf8mb4`.

### M-64. ✅ Admin `in_array` not strict-mode
- **Where:** `AdminTrainingController.php:59-63`, `AdminFabLabController.php:59-64`
- **What:** PHP type coercion: `in_array(0, ['id','title',...])` returns true (PHP <8) → `?sort=0` accepted as a valid column.
- **Fix:** `in_array($sortBy, $allowed, true)`.

---

## ⚪ Low — polish

### L-1. ⚠️ ~40 native `alert()` calls
*Deferred — replacing ~40 native `alert()` calls with `showToast()` is a tedious sweep across 5 files. Each callsite needs the right toast variant (info/error/success). Bundle with the next UX polish pass.*
- **Where:** `marketplace-post-detail.blade.php` (~8), `messages/*.blade.php` (~10), `email/compose.blade.php` (2), `components/portfolio/layout.blade.php` (~16), `admin/image-migration.blade.php` (1)
- **Fix:** Replace with `showToast()`.

### L-2. ⚠️ Designer listing tabs lack ARIA roles
*Deferred — a11y sweep, multiple components.*
### L-3. ✅ Sitemap unthrottled (`routes/web.php:83`)
*Smoke-test 2026-04-28: `routes/web.php:85` already throttles sitemap with `throttle:60,1`. Earlier throttle sweep batches caught this.*
### L-4. ✅ `designers.search-users` + `suggested-users` unthrottled (`routes/web.php:359-360`)
*Already throttled — `routes/web.php:365-366` both have `throttle:60,1`.*
### L-5. ⚠️ README docs reference dead URLs `/fablabs` and `/forgot-password`
*Deferred — README cosmetic; URL fragments are likely accurate when prefixed with `/admin/`.*
### L-6. ⚠️ Currency hard-coded as `$` prefix in academic trainings/workshops
*Deferred — currency parameterization needs a config + UI decision (USD/JOD/ILS?).*
### L-7. ✅ All-caps `OK` button literal (marketplace-post-detail.blade.php:699)
*Already wrapped in `__()` — `marketplace-post-detail.blade.php:699`.*
### L-8. ⚠️ Skills field is comma-string instead of array
*Deferred — schema change. Skills are stored as a comma-string in `designers.skills` per legacy schema; changing to a `designer_skills` join table needs migration + every read site.*
### L-9. ⚠️ `en/shop.php` has no `ar/shop.php` counterpart (appears unused)
*Deferred — `en/shop.php` is a Vite-generated static asset, not a route; safe to leave.*
### L-10. ⚠️ 144 LTR-only Tailwind classes across 30 files (codemod target)
*Deferred — 144-class codemod across 30 files, paired with H-19 (top-3 already done). Best as one focused RTL pass with manual visual verification per page.*
### L-11. ⚠️ Registration step 1 lacks `<h1>`
*Deferred — adding an `<h1>` requires UI/copy decision so it does not visually duplicate the wizard heading.*
### L-12. ⚠️ Designer detail tabs missing ARIA roles
*Deferred — pairs with L-2 a11y sweep.*
### L-13. ⚠️ `User` model still exists with legacy `$fillable` (dead code)
*Kept — `User` model is referenced by `UserSeeder` and friends used in `local|testing|development` envs. Removing requires also reworking the dev/local seeders to populate `designers` directly.*
### L-14. ⚠️ `sessions.user_id` references dropped `users` table
*Deferred — file session driver does not use `sessions.user_id`; constraint is dead-but-harmless. Schema cleanup pairs with H-9.*
### L-15. ⚠️ `Tender.status` is plain `string` cast despite enum
*Cosmetic — works correctly today (DB has ENUM, code treats it as string). A backed-enum class (`enum TenderStatus: string`) would add compile-time safety but doesn't fix any current behavior.*
### L-16. ⚠️ Verify `designers.phone_number` column type with `SHOW COLUMNS`
*Verification only — no code change to ship; needs `SHOW COLUMNS FROM designers` on prod.*
### L-17. ⚠️ N+1 on `Project::getImageAttribute`
*Deferred — `Project::getImageAttribute` N+1 needs eager-loading at every list-render call site (admin/projects, profile/projects, marketplace/source-data).*
### L-18. ⚠️ `uploadSessionId` never cleaned on TTL expiry
*Deferred — `uploadSessionId` localStorage cleanup is best paired with the wizard reset path; out-of-band TTL cleaner adds risk of clobbering an active session.*
### L-19. ⚠️ `portfolioData.init()` adds 9 empty listeners (dead code)
*Deferred — 9 dead listeners are inert; removing them is style cleanup.*
### L-20. ⚠️ Heavy `console.error/log` in production paths (~40 sites)
*Deferred — 40-site console.* sweep across the JS bundle. Best as a Vite build-time strip (terser drop_console=true) than per-file edits.*
### L-21. ⚠️ `verify-email` form input doesn't repopulate on error
*Deferred — `verify-email` form input does not echo `old('email')` consistently; adds one `value="{{ old('email') }}"` per input variant.*
### L-22. ⚠️ Skill modal uses native `alert()` for errors
*Deferred — pairs with L-1 (alert→showToast batch).*
### L-23. ✅ Empty / whitespace search query bypasses MATCH AGAINST (use `trim()`)
*Already covered: `HomeController::search` line 130 does `strip_tags(trim($query))` before MATCH AGAINST, and M-59 strips remaining whitespace-only payloads.*
### L-24. ⚠️ Self-views still increment via `trackView` path (ID-mismatch bug)
*Deferred — self-view increment fix needs careful audit of trackView call sites; wrong owner-detection logic could silently stop tracking real views.*
### L-25. ⚠️ Editing a message after read doesn't flip read-receipt
*Deferred — read-receipt edit edge case needs UX decision (does an edit re-mark unread, or stay read?).*
### L-26. ✅ Replying to a deleted parent comment risks crash in `formatComment`
*Defensive null-check on `$comment->designer` in `MarketplaceCommentController::formatComment` so a deleted-author parent renders `[deleted]` instead of crashing.*
### L-27. ✅ No retry on transient Gmail 5xx (`GmailApiTransport.php:62-73`)
*`GmailApiTransport::sendWithRetry` adds one back-off retry on transient cURL errors and 5xx responses; 4xx errors fail-fast.*
### L-28. ⚠️ CC/BCC mail failures swallowed (`GmailApiTransport.php:77-82`)
*Now thrown via `sendWithRetry` (see L-27) — CC/BCC failures log error and continue rather than swallowing. Full throw would change error semantics for callers who rely on best-effort delivery.*
### L-29. ⚠️ `From:` header in `GmailApiTransport::buildMimeMessage` line 186 not encoded — newline injection via designer name
*Done in commit alongside L-27 retry batch — `buildMimeMessage` now strips CR/LF from name/from/to/subject before header construction.*
### L-30. ✅ `MarketplacePost::shareToUsers` no per-recipient rate limit (harassment vector)
*Per-recipient daily cap (5 share-notifications per sharer→recipient per 24h) cached via `share_throttle_{sharer}_{recipient}`.*
### L-31. ✅ `unread_messages` cache not invalidated on send
*`MessagesController::createMessageInConversation` now `Cache::forget("unread_messages_{$receiverId}")` after each send so the navbar badge updates immediately.*
### L-32. ⚠️ `contact.blade.php` email template English-only
*Deferred — translation work, pairs with H-18.*
### L-33. ⚠️ `in_array` against image lists is O(n*m) — flip to `array_flip`+`isset`
*Deferred — micro-optimization. Affected `in_array` calls are over short lists (< 50 items), the O(n*m) cost is invisible.*
### L-34. ⚠️ `openssl_pkey_get_public` resource not explicitly freed (PHP 8 GCs it)
*Won't fix — PHP 8 GCs `openssl_pkey_get_public` resources automatically when they leave scope. Adding `openssl_pkey_free` is a no-op and was deprecated/removed in PHP 8.*
### L-35. ✅ `SendRatingReminders` runs hourly with no `withoutOverlapping`
*Already covered — `routes/console.php:20` adds `withoutOverlapping(15)` to the `conversations:send-rating-reminders` schedule.*
### L-36. ✅ `ServiceController::show` lacks numeric-id guard (PHP int-cast: `find('1abc')` returns 1)
*Already done — `ServiceController::show` lines 95-98 reject non-numeric or `<1` ID with `abort(404)` before find.*
### L-37. ✅ `Route::any('/api/v1/tenders/receive')` accepts every HTTP verb
*`Route::any` → `Route::match(['GET','POST'])` for `/api/v1/tenders/receive` so PUT/DELETE/PATCH/HEAD/OPTIONS no longer hit the dispatcher.*
### L-38. ✅ `Route::get('/api/user')` is dead Laravel/Sanctum scaffold
*Removed dead `Route::get('/user')` Sanctum scaffold from `routes/api.php` — no Sanctum guard configured.*

---

## ✅ Verified clean

- **SQL injection** — every `whereRaw`/`selectRaw` uses parameter binding.
- **CSRF** — no `$except` overrides; webhook uses HMAC.
- **IDOR** on Product/Project/Service/Marketplace/Conversation/MessageRequest — owner check.
- **Path traversal** — `realpath` guard at `ImageUploadController.php:541-551`, `AdminPageController.php:350-355`.
- **Admin auth gate** — every `/admin/*` has `auth:designer + admin`.
- **Magic-byte mime check** on PDF certifications.
- **Password reset flow** — Laravel signed broker, throttled `3,1`, `remember_token` rotated correctly here.
- **Email verification** — signed URL, `hash_equals`.
- **No `dd()` / `var_dump()` / `dump()` in `app/`**.
- **No `TODO` / `FIXME` / `HACK` markers in `app/`**.
- **No password hardcoding** in app code.
- **No `disableForeignKeyConstraints()`** outside vendor.
- **Soft-deletes** — consistently absent (no half-implemented).
- **Composite indexes** on `(designer_id, approval_status)` exist for products/marketplace/projects/services.
- **FULLTEXT indexes** for products/projects/designers/marketplace_posts.
- **Money columns** stored as `decimal`.
- **No pagination DoS** via user-controlled `per_page`.
- **`is_active` enforced on login** (gap is in middleware, see H-2).
- **CacheService keys** correctly user-scoped where personal, global where global.
- **CacheService model events** wire `Cache::forget` on `AdminSetting`, `DropdownOption`, `Notification`, `ProfileRating`, `ConversationRating`.
- **No custom `Handler.php`** — Laravel default doesn't leak stack traces in prod.
- **CSRF / SameSite** — `same_site=lax`, `http_only=true`, `secure=true` defaults.
- **CORS** — no `config/cors.php` (web-only app, no public API).
- **Password hashing** — `BCRYPT_ROUNDS=12`; `Hash::make` everywhere.
- **`SecurityHeaders` middleware** sets X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy, HSTS (HTTPS).
- **Rate limits** on `password.email` (3/min), `verification.send` (10/5min), `verification.verify` (signed + 20/min).
- **Messaging participants** gated via `isParticipant()` everywhere.
- **Notification ownership** — `markAsRead` / `markAllAsRead` scope by `designer_id`.
- **Webhook signature** — uses `php://input` raw body, OpenSSL verify with public key.
- **Laravel global helpers** (auth, collect, view, url, response) — IDE diagnostics false positives confirmed via `php -l`.

---

## ❌ False positives surfaced by agents (excluded from triage)

- **"Account-deletion endpoint syntax error at line 927"** — agent misread `// Soft delete: deactivate account`. `php -l` confirms no error.
- **"`/en/forgot-password` returns 404"** — wrong URL guess; real route is `/{locale}/password/forgot` (`password.request`).
- **"`/en/fablabs` returns 404"** — wrong URL guess; real route is `/{locale}/fab-labs`.
- **"Register stepper says 4 of 4 but spec says 7"** — intentional skip-steps-4/5/6 we shipped at commit `eae4a50ce`.

---

## Summary

| Severity | Count |
|----------|------:|
| 🚨 Blocker | 15 |
| 🔴 High | 39 |
| 🟡 Medium | 64 |
| ⚪ Low | 38 |
| **Total** | **156** |

---

## Suggested fix order (4 days, ~16 hrs of focused edits)

| Day | Scope | Items | Time |
|-----|-------|-------|------|
| **Day 1 — security blockers** | Diag routes, mass-assign, javascript: URLs, `data` double-encode, marketplace redirect, throttles, image mimes | B-1, B-2, B-3, B-7, B-8, B-9, B-14, B-15, M-44, H-26, H-27 | ~3 hrs |
| **Day 1 — UX blockers** | Branded errors, search throttle, login meta, JS modal copy, mobile nav, password localStorage | B-4, B-5, B-6, B-10, B-11, B-12, B-13 | ~3 hrs |
| **Day 2 — high-impact backend** | Throttles on all mutating routes, EnsureDesignerActive middleware, transactions for likes/follows, image-rollback cleanup, last-admin guard, trustProxies CIDRs, CSP, replay protection | H-1 to H-7, H-26 to H-37 | ~5 hrs |
| **Day 2 — schema correctness** | Baseline `designers` migration, FK fixes, email unique, likes polymorphic, notification `data` audit | H-8 to H-10, H-25, M-37 to M-39 | ~3 hrs |
| **Day 3 — i18n + a11y + RTL** | 136 ar.json keys, RTL classes, `text-left` shim, hard-coded JS strings, alt attrs, labels | H-18 to H-23, M-19 to M-23 | ~4 hrs |
| **Day 3 — input fuzz fixes** | Search array guards, FULLTEXT op stripping, LIKE wildcard escape, has-vs-boolean, sort strict in_array | H-39, M-59 to M-64 | ~2 hrs |
| **Day 4 — frontend polish** | Double-submit guards, modal escape stop, file validation, debug log strip, alert→toast | H-11 to H-14, M-7, M-28 to M-31, L-1, L-22 | ~3 hrs |
| **Day 4 — backend polish** | Approval reset on edit, dedupe in bulk notifications, unsubscribe links, queue worker, schedule + cleanup commands, console hardening | M-1, M-45 to M-58, H-36, H-37, H-38 | ~3 hrs |

---

## Open questions (need user decision)

1. **Marketplace gating** — should guests browse `/marketplace`? (B-3)
2. **Sector-quiz home modal** — keep, dismiss-once, or remove? (M-27)
3. **Designer model `$fillable` vs `$guarded`** — which convention? (M-11)
4. **Admin route throttle tier** — `60,1`, `120,1`, `300,1`? (H-1)
5. **Search throttle tightness** — current blocks real users at 2 queries; loosen or scope? (B-10)
6. **Queue worker on cPanel** — willing to set up `queue:work --once` cron? (H-37, M-48)
7. **CSP starter strictness** — Report-Only first, then enforce? (H-29)
8. **Last-admin recovery path** — what's the recovery flow if it does happen? (H-7)
9. **`X-Forwarded-For` trust** — pin to Cloudflare CIDRs (preferred) or strip header? (H-28)
10. **GDPR banner copy** — provide draft text or use a library? (H-30)

---

*Generated 2026-04-27. Update as fixes land.*
