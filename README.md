# Palestine Creative Hub (PCH)

A bilingual (Arabic/English) platform for Palestinian creative professionals to showcase portfolios, products, services, and projects — and to connect with clients and each other.

Built with **Laravel 12**, deployed at `https://technopark.ps/PalestineCreativeHub/`.

---

## Purpose

Palestine Creative Hub provides a centralised digital space where:

- **Designers, manufacturers, and showrooms** can publish portfolios, sell products, offer services, and post marketplace listings.
- **Academic and TVET institutions** can promote training programmes, workshops, and announcements.
- **Clients and collaborators** can discover talent, browse the marketplace, and initiate conversations.
- **Admins** can moderate content, manage accounts, configure site settings, and review analytics.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend framework | Laravel 12 (PHP 8.2+) |
| Database | MySQL 8+ |
| Frontend CSS | Tailwind CSS (CDN) |
| Frontend JS | Alpine.js v3 (CDN) |
| Charts | Chart.js (CDN, admin panel) |
| Build tool | Vite |
| PDF generation | TCPDF |
| Image processing | Intervention Image v3 |
| Excel export | Maatwebsite Excel v3 |
| Email | PHPMailer + Laravel Mail |
| QR codes | SimpleSoftwareIO QR Code |
| File managers | Laravel Elfinder + Unisharp FileManager |
| Auth tokens | Laravel Sanctum |

---

## Feature List

### Designer Portal
- **Profiles** — Public portfolio page with bio, avatar, cover image, skills, social links, and location.
- **Projects** — Multi-image portfolio pieces with categories, likes, views, and inline commenting.
- **Products** — Catalogue items with multi-image upload, price, and category; full approval workflow.
- **Services** — Text-based service listings with categories and approval workflow.
- **Marketplace** — Community post board (service / collaboration / showcase / opportunity types) with likes and comments.
- **Messaging** — Request-gated direct messaging between designers; conversation rating after closing.
- **Ratings** — Star-based profile ratings across four criteria (professionalism, communication, quality, timeliness).
- **Subscriptions** — Follow profiles and subscribe to content categories to receive in-app notifications.
- **Follow system** — Follow/unfollow other designers; follower counts shown on profiles.

### Academic Portal
- **Academic accounts** — Separate auth guard for universities, colleges, TVET centres, and training centres.
- **Trainings & Workshops** — Create and manage training programmes with approval workflow.
- **Announcements** — Publish institutional announcements; approval required before going live.

### Marketplace & Discovery
- **Designers directory** — Filter by sector (designer / manufacturer / showroom), city, skills; sort by followers.
- **Products catalogue** — Filter by category and price range; full-text search.
- **Projects gallery** — Filter by category; sort by latest or most liked.
- **Services listing** — Browse services by category.
- **Fab Labs** — Directory of fabrication laboratories with type and city filters.
- **Tenders** — Opportunity feed integrated with a Jobs.ps webhook for live tender imports.
- **Trainings** — Combined view of academic trainings, workshops, and announcements.
- **Academic & TVETs** — Institution directory.
- **Global search** — Instant autocomplete and full-page results across all content types.

### Admin CMS
- **Dashboard** — Platform KPIs, pending approval queues, sector/city breakdowns, daily registration chart, top contributors.
- **Analytics** — Advanced filtered analytics (date presets, sector, city) with Excel export and cache refresh.
- **Content moderation** — Approve / reject products, projects, services, and marketplace posts; bulk actions.
- **Designer management** — Edit accounts, toggle active/trusted/admin flags, reset passwords, bulk actions.
- **FabLab management** — Full CRUD for fabrication laboratory entries.
- **Training & Tender management** — Admin-managed (no external submission); full CRUD.
- **Academic management** — Academic account CRUD + content approval for trainings, workshops, and announcements.
- **Rating management** — View, approve, and reject profile ratings; configure rating criteria.
- **Settings** — Hero image upload, auto-accept toggles per content type, layout text (header/footer/subheader/counters), registration policies.
- **Dropdown management** — CRUD for all site dropdown options with Arabic translation support and drag-to-reorder.
- **CMS pages** — Editable static pages (About, Terms, Privacy, etc.) with image upload.

---

## Setup Instructions

### Requirements
- PHP 8.2+
- Composer
- Node.js 18+ and npm
- MySQL 8+

### Quick Setup (using composer script)

```bash
git clone <repo-url>
cd PalestineCreativeHub
composer run setup
```

The `setup` script runs:
1. `composer install`
2. Copies `.env.example` to `.env` if not present
3. `php artisan key:generate`
4. `php artisan migrate --force`
5. `npm install`
6. `npm run build`

### Manual Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy and configure environment
cp .env.example .env
# Edit .env with your database credentials and mail settings

# 3. Generate application key
php artisan key:generate

# 4. Run database migrations
php artisan migrate

# 5. Install Node dependencies and build assets
npm install
npm run build

# 6. Set storage permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```

### Development Server

```bash
composer run dev
```

This starts Laravel server, queue listener, Pail log viewer, and Vite dev server concurrently.

### Running Tests

```bash
composer run test
```

---

## Directory Structure Overview

```
PalestineCreativeHub/
├── app/
│   ├── Exports/                    # Excel export classes (AnalyticsExport)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              # 20 admin panel controllers
│   │   │   ├── Api/                # Webhook controller (Jobs.ps tenders)
│   │   │   ├── Auth/               # Authentication, image upload, validation
│   │   │   └── *.php               # 19 public-facing controllers
│   │   ├── Middleware/             # SetLocale, Admin, Academic, Auth guards
│   │   └── Models/                 # Legacy static model helpers
│   ├── Models/
│   │   ├── Traits/                 # HasApprovalStatus, HasSubscriptions
│   │   └── *.php                   # 36 Eloquent models
│   ├── Services/                   # CacheService, WebhookSignatureService,
│   │                               #   NotificationSubscriptionService, GmailOAuthService
│   ├── helpers/                    # DropdownHelper, AssetHelper, functions.php
│   ├── Providers/                  # AppServiceProvider
│   └── View/Components/            # Blade components (Modal, Portfolio, Profile)
├── database/
│   ├── migrations/                 # 30+ migration files
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── admin/                  # Admin panel Blade templates
│   │   ├── auth/                   # Login, register wizard, password reset
│   │   ├── components/             # Reusable Blade component templates
│   │   ├── layout/                 # Public layout (main, auth)
│   │   ├── layouts/                # Admin/chat layout
│   │   ├── messages/               # Messaging views
│   │   └── *.blade.php             # Page templates (home, designers, products…)
│   ├── css/
│   └── js/
├── routes/
│   ├── web.php                     # Public + authenticated designer routes
│   ├── admin.php                   # Admin panel routes (/{locale}/admin)
│   ├── academic.php                # Academic portal routes
│   └── api.php                     # API + webhook routes
├── public/
├── storage/
├── .env.example
├── composer.json
├── package.json
└── vite.config.js
```

---

## Environment Variables Explained

Copy `.env.example` to `.env` and fill in the values below.

### Application

| Variable | Description |
|---|---|
| `APP_NAME` | Display name of the application |
| `APP_ENV` | `local`, `staging`, or `production` |
| `APP_KEY` | Generated by `php artisan key:generate` |
| `APP_DEBUG` | `true` only during development |
| `APP_URL` | Full base URL including subfolder if applicable |
| `APP_FOLDER` | Subfolder path (e.g. `/PalestineCreativeHub`) — used for asset URLs |
| `WEB_FOLDER` | Subfolder name without leading slash — used in route generation |
| `APP_LOCALE` | Default locale: `en` or `ar` |

### Database

| Variable | Description |
|---|---|
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | Database host (usually `127.0.0.1`) |
| `DB_PORT` | Database port (default `3306`) |
| `DB_DATABASE` | Database name |
| `DB_USERNAME` | Database user |
| `DB_PASSWORD` | Database password |

### Session & Cache

| Variable | Description |
|---|---|
| `SESSION_DRIVER` | `file` (default) or `redis` |
| `SESSION_LIFETIME` | Minutes before session expires |
| `SESSION_ENCRYPT` | `true` recommended for production |
| `SESSION_SECURE_COOKIE` | `true` for HTTPS-only deployments |
| `CACHE_STORE` | `file` (default) or `redis` |

### Mail

| Variable | Description |
|---|---|
| `MAIL_MAILER` | `smtp` |
| `MAIL_HOST` | SMTP hostname |
| `MAIL_PORT` | SMTP port (`587` for TLS) |
| `MAIL_USERNAME` | SMTP username |
| `MAIL_PASSWORD` | SMTP password |
| `MAIL_ENCRYPTION` | `tls` or `ssl` |
| `MAIL_FROM_ADDRESS` | Sender email address |
| `FROM_EMAIL` | General from address |
| `NO_REPLY_EMAIL` | No-reply address for system emails |
| `CONTACT_US_EMAIL` | Destination for contact form submissions |
| `NOTIFICATION_EMAIL` | Destination for admin notifications |

### Payment & SMS

| Variable | Description |
|---|---|
| `BOP_MERCHANT_ID` | Bank of Palestine merchant ID |
| `BOP_ACQUIRER_ID` | Bank of Palestine acquirer ID |
| `BOP_PASSWORD` | Bank of Palestine API password |
| `BOP_SUBMIT_URL` | Bank of Palestine payment redirect URL |
| `PALPAY_USERNAME` | PalPay gateway username |
| `PALPAY_PASSWORD` | PalPay gateway password |
| `SMS_API_KEY` | SMS service API key |

### Security & Integrations

| Variable | Description |
|---|---|
| `CAPTCHA_SECRET` | Google reCAPTCHA v2 secret key |
| `CAPTCHA_SITEKEY` | Google reCAPTCHA v2 site key |
| `JOBS_PS_PUBLIC_KEY_PATH` | Filesystem path to Jobs.ps RSA public key for webhook signature verification |
| `JOBS_PS_SKIP_VERIFICATION` | Set `true` to bypass webhook signature checks (development only) |
| `FILE_MANAGER` | `true` to enable file manager routes |

---

## Key Routes Overview

All public and authenticated routes use the `/{locale}` prefix where `{locale}` is `en` or `ar`.

### Public Routes

| Method | URL | Description |
|---|---|---|
| GET | `/` | Redirect to locale-prefixed home |
| GET | `/{locale}` | Homepage / discover feed |
| GET | `/{locale}/search` | Full-page search results |
| GET | `/{locale}/search/instant` | AJAX autocomplete search |
| GET | `/{locale}/designers` | Designers directory |
| GET | `/{locale}/designer/{id}` | Designer public portfolio |
| GET | `/{locale}/products` | Products catalogue |
| GET | `/{locale}/products/{id}` | Product detail |
| GET | `/{locale}/projects` | Projects gallery |
| GET | `/{locale}/projects/{id}` | Project detail |
| GET | `/{locale}/services` | Services listing |
| GET | `/{locale}/services/{id}` | Service detail |
| GET | `/{locale}/marketplace` | Marketplace feed |
| GET | `/{locale}/marketplace/{id}` | Marketplace post detail |
| GET | `/{locale}/fab-labs` | Fab Labs directory |
| GET | `/{locale}/fab-labs/{id}` | Fab Lab detail |
| GET | `/{locale}/trainings` | Trainings listing |
| GET | `/{locale}/trainings/{id}` | Training detail |
| GET | `/{locale}/tenders` | Tenders listing |
| GET | `/{locale}/tenders/{id}` | Tender detail |
| GET | `/{locale}/academic-tevets` | Academic institutions directory |
| GET | `/{locale}/{slug}` | CMS static page (about, terms, privacy…) |
| GET | `/sitemap.xml` | XML sitemap |
| GET | `/media/{path}` | Serve files from storage (no symlink needed) |

### Authentication Routes

| Method | URL | Description |
|---|---|---|
| GET | `/{locale}/register` | Registration wizard |
| POST | `/{locale}/register` | Submit registration |
| GET | `/{locale}/login` | Login form |
| POST | `/{locale}/login` | Submit login |
| POST | `/{locale}/logout` | Logout (auth required) |
| GET | `/{locale}/password/forgot` | Forgot password form |
| POST | `/{locale}/password/email` | Send reset link |
| GET | `/{locale}/password/reset/{token}` | Reset password form |
| POST | `/{locale}/password/reset` | Submit new password |
| GET | `/{locale}/email/verify` | Email verification notice |
| GET | `/{locale}/email/verify/{id}/{hash}` | Confirm email |

### Authenticated Routes (designer guard + verified)

| Method | URL | Description |
|---|---|---|
| GET | `/{locale}/profile` | Own profile view |
| GET | `/{locale}/profile/edit` | Edit profile |
| GET | `/{locale}/account/settings` | Account settings |
| POST | `/{locale}/products` | Create product |
| PUT | `/{locale}/products/{id}` | Update product |
| DELETE | `/{locale}/products/{id}` | Delete product |
| POST | `/{locale}/projects` | Create project |
| POST | `/{locale}/services` | Create service |
| POST | `/{locale}/marketplace-posts` | Create marketplace post |
| GET | `/{locale}/messages` | Messages inbox |
| POST | `/{locale}/messages/send-request/{id}` | Send message request |
| GET | `/{locale}/notifications` | Notifications list |
| POST | `/{locale}/designer/{id}/follow` | Follow designer |
| POST | `/{locale}/designer/{id}/rate` | Rate designer profile |

### Admin Routes (`/{locale}/admin`, auth:designer + is_admin)

| URL prefix | Description |
|---|---|
| `/admin/` | Dashboard |
| `/admin/analytics/` | Advanced analytics + export |
| `/admin/designers/` | Designer account management |
| `/admin/products/` | Product moderation |
| `/admin/projects/` | Project moderation |
| `/admin/services/` | Service moderation |
| `/admin/marketplace/` | Marketplace post moderation |
| `/admin/fablabs/` | Fab Lab CRUD |
| `/admin/trainings/` | Training CRUD |
| `/admin/tenders/` | Tender CRUD |
| `/admin/academic-accounts/` | Academic account management |
| `/admin/academic-content/` | Academic content moderation |
| `/admin/ratings/` | Profile rating moderation + criteria |
| `/admin/settings/` | Site settings |
| `/admin/dropdowns/` | Dropdown option management |
| `/admin/pages/` | CMS static page editor |

### API Routes

| Method | URL | Description |
|---|---|---|
| POST | `/api/webhooks/tenders` | Jobs.ps tender webhook (HMAC-verified) |
