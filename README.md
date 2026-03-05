# Palestine Creative Hub

A bilingual (Arabic/English) creative portfolio and marketplace platform built with **Laravel 12**, deployed at `https://technopark.ps/PalestineCreativeHub/`.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 (PHP 8.2+) |
| Database | MySQL (technopark_portal) |
| Frontend | Tailwind CSS (CDN) + Alpine.js v3 (CDN) |
| Fonts | Instrument Sans (bunny.net) |
| Icons | Inline SVGs (public), Font Awesome 6.4 (admin), Phosphor (legacy) |
| PDF | TCPDF |
| Images | Intervention Image v3 |
| Excel | Maatwebsite Excel v3 |
| Email | PHPMailer + Laravel Mail |
| QR Codes | SimpleSoftwareIO QR Code |
| File Managers | Laravel Elfinder + Unisharp FileManager |
| Legacy CMS | Bootstrap-based Control Panel |

---

## Authentication Systems

The platform runs **3 independent auth guards**:

1. **`designer`** - Creative professionals (designers, manufacturers, showrooms)
   - Table: `designers`
   - Registration: 7-step wizard
   - Routes: `/{locale}/...`

2. **`academic`** - Academic/TVET institutions
   - Table: `academic_accounts`
   - Types: university, college, tvet, training_center
   - Routes: `/{locale}/academic/...`

3. **`control`** - Legacy CMS administrators
   - Table: `users` (legacy)
   - Session-based permissions (u_p1 through u_p20)
   - Routes: `/Control/...`

Admin access is a **role on the designer guard** (`is_admin` flag), not a separate guard.

---

## Route Structure

| File | Prefix | Guard | Purpose |
|------|--------|-------|---------|
| `routes/web.php` | `/{locale}` | designer (partial) | Public pages + auth designer routes |
| `routes/admin.php` | `/{locale}/admin` | designer + is_admin | Admin panel |
| `routes/academic.php` | `/{locale}/academic` | academic | Academic institution portal |
| `routes/control.php` | `/Control` | control | Legacy CMS |
| `routes/api.php` | `/api` | sanctum / none | API + Jobs.ps webhook |

All public routes use `{locale}` prefix supporting `en` and `ar`.

---

## Core Features

### For Designers (Creative Professionals)
- **Portfolio** - Public profile page with bio, skills, projects, products, services
- **Products** - CRUD with multi-image upload, categories, approval workflow
- **Projects** - CRUD with multi-image upload, likes, views, comments
- **Services** - CRUD (text-only, no images)
- **Marketplace** - Community posts (service/collaboration/showcase/opportunity types)
- **Messaging** - Request-based system (must request before chatting)
- **Ratings** - Profile ratings (4 criteria: professionalism/communication/quality/timeliness)
- **Subscriptions** - Follow designers and subscribe to content categories

### For Academic Institutions
- **Trainings** - Create/manage training programs
- **Workshops** - Create/manage workshops
- **Announcements** - Publish announcements
- All content goes through approval workflow

### Public Pages
- **Homepage** - Hero carousel, top designers, featured content, stats
- **Designers Directory** - Filter by type (designer/manufacturer/showroom), search, sort
- **Products Catalog** - Filter by category, price range, search
- **Projects Gallery** - Filter by category, sort by popularity/likes
- **Services Listing** - Browse available services
- **Fab Labs** - Directory of fabrication laboratories (type/city filters)
- **Tenders** - Opportunities feed (integrated with Jobs.ps webhook)
- **Trainings** - Combined view of trainings, workshops, announcements
- **Academic & TVETs** - Institution directory
- **Search** - Global search across designers, projects, products

### Admin Panel (`/{locale}/admin/`)
- **Dashboard** - Pending approval queues, platform statistics
- **Content Moderation** - Approve/reject products, projects, services, marketplace posts
- **Designer Management** - Edit, toggle admin/trusted/active status
- **FabLab Management** - Full CRUD for fabrication labs
- **Settings** - Hero images, auto-accept toggles, site info, dropdown options
- **Academic Management** - Account CRUD, content moderation

### Legacy CMS (`/Control/`)
- Pages/Articles, Categories, Files, Menus, Tags, Authors
- Dynamic Form Builder with entries and Excel export
- E-commerce Shop (products, orders, coupons, offers, customers)
- Conference/Event Scheduler with speakers
- Polls, FAQs, Glossary
- Financial Management (revenue, expenses, budgets)
- Member/Complaint Workflow System
- Audit Logging

---

## Content Approval Workflow

Content goes through: **Pending** -> **Approved** / **Rejected**

Managed by `HasApprovalStatus` trait. Auto-accept can be toggled per content type via `AdminSetting`.

On approval, `NotificationSubscriptionService` notifies:
- Profile subscribers (users following the creator)
- Category subscribers (users watching that content type/category)

---

## Key Directory Structure

```
app/
├── Console/Commands/          # Artisan commands (cleanup, migration)
├── helpers/
│   ├── AssetHelper.php        # Versioned asset URLs
│   ├── DropdownHelper.php     # DB-driven dropdown options with fallbacks
│   └── functions.php          # ~2400 lines legacy utilities
├── Http/
│   ├── Controllers/
│   │   ├── Academic/          # 7 controllers - academic portal
│   │   ├── Admin/             # 16 controllers - admin panel
│   │   ├── Api/               # Webhook controller
│   │   ├── Auth/              # Auth, image upload, validation
│   │   ├── Control/           # 23 legacy CMS controllers
│   │   │   └── Shop/          # 6 e-commerce controllers
│   │   └── *.php              # 19 root-level controllers
│   ├── Middleware/             # SetLocale, Admin, Academic, Auth, CheckPlugin
│   └── Models/                # 39 legacy models (static methods)
│       └── Control/Shop/      # E-commerce models
├── Models/                    # 36 modern Eloquent models
│   └── Traits/                # HasApprovalStatus, HasSubscriptions
├── Services/                  # Cache, Notification, Webhook services
├── View/Components/           # Blade components + some controllers
│   ├── Modal/                 # CRUD modal components
│   ├── Portfolio/             # Portfolio view components
│   └── Profile/               # Profile edit components
└── Providers/
    └── AppServiceProvider.php # HTTPS force, asset versioning directives

resources/views/
├── layout/                    # Public layouts (main, auth)
├── layouts/                   # Admin/chat layouts
├── partials/                  # Navbar, footer, head, javascript
├── auth/                      # Login, register (7-step), forgot-password
├── components/                # Blade component templates
│   ├── home/                  # Homepage sections
│   ├── modal/                 # CRUD modals
│   ├── portfolio/             # Portfolio view components
│   └── profile/               # Profile edit components
├── admin/                     # Admin panel views
├── academic/                  # Academic portal views
├── messages/                  # Messaging system views
├── Control/                   # Legacy CMS views (Bootstrap)
└── *.blade.php                # Root-level page templates (~200+ total)

routes/
├── web.php                    # Main routes (includes admin, control, academic)
├── admin.php                  # Admin panel routes
├── academic.php               # Academic routes
├── control.php                # Legacy CMS routes
└── api.php                    # API routes
```

---

## Database

- **Connection**: MySQL on 127.0.0.1:3306
- **Database**: technopark_portal
- **Charset**: utf8mb4 (full Unicode/Arabic support)
- **19 migration files** defining modern tables
- **Legacy tables** managed by the CMS (not in migrations)

### Key Tables (Modern)
- `designers`, `academic_accounts`, `users` (legacy CMS)
- `products`, `product_images`, `projects`, `project_images`, `services`
- `marketplace_posts`, `marketplace_comments`
- `fab_labs`, `tenders`, `trainings`
- `conversations`, `messages`, `message_requests`
- `notifications`, `academic_notifications`
- `profile_ratings`, `conversation_ratings`
- `profile_subscriptions`, `category_subscriptions`
- `skills`, `categories`, `dropdown_options`
- `site_settings`, `admin_settings`
- `likes`, `project_likes`, `project_comments`, `project_views`

---

## Deployment Notes

- **Subfolder deployment** at `/PalestineCreativeHub/` (not domain root)
- **PHP-FPM** with `.user.ini` for runtime settings
- **No symlinks** - custom storage route serves files directly
- **Rate limiting** on virtually every route
- **Asset versioning** via `@versionedAsset` / `@autoVersionedAsset` directives

---

## Environment Variables Summary

```env
APP_NAME="Palestine Creative Hub"
APP_ENV=local
APP_URL=https://technopark.ps/PalestineCreativeHub/
APP_FOLDER=/PalestineCreativeHub
WEB_FOLDER=PalestineCreativeHub
APP_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=technopark_portal

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

MAIL_HOST=mail.intertech.ps
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

---

## 75 Controllers Across 7 Directories

| Directory | Count | Auth Guard | Purpose |
|-----------|-------|------------|---------|
| Root-level | 19 | designer | Core platform features |
| Api/ | 1 | HMAC signature | External webhooks |
| Auth/ | 3 | designer | Authentication & uploads |
| Academic/ | 7 | academic | Institution management |
| Admin/ | 16 | designer (is_admin) | Platform administration |
| Control/ | 23 | control | Legacy CMS management |
| Control/Shop/ | 6 | auth | E-commerce management |
