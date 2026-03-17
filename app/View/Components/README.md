# View Components

This directory contains all anonymous Blade components for Palestine Creative Hub.
Components are organised into four namespaces: `Modal`, `Portfolio`, `Profile`, and `Profile/Tabs`.

---

## Portfolio vs Profile — the split explained

| Namespace | Purpose | Viewer |
|---|---|---|
| `Portfolio\*` | **Read-only** public view of a designer's portfolio | Anyone (no auth required) |
| `Profile\*` | **Editable** view of the authenticated designer's own profile | Owner only (auth + verified) |

Both namespaces render the same underlying designer data but with fundamentally different intent:

- **Portfolio** components focus on presentation — they hide edit controls and show follow/subscribe actions to visitors.
- **Profile** components focus on editing — they wire up Alpine.js reactive forms, image upload sessions, and CRUD modals for portfolio items.

When a designer views their own portfolio, the `ViewPage` component sets `$isOwner = true`, which surfaces the same edit shortcuts that are always visible in the Profile namespace.

---

## How Blade Components Work in This Project

Each component class lives in `app/View/Components/` and its corresponding Blade template lives in `resources/views/components/` (mirroring the class namespace with kebab-case filenames).

Components are referenced in Blade using the `<x-*>` tag syntax:

```blade
{{-- Portfolio view page --}}
<x-portfolio.view-page :designer="$designer" :projects-data="$projects" ... />

{{-- Profile edit page --}}
<x-profile.edit-page :designer="$designer" :projects-data="$projects" ... />

{{-- Modal base --}}
<x-modal.base-modal />
```

Props are passed as kebab-case HTML attributes and automatically mapped to camelCase constructor parameters by Laravel's component resolver.

---

## Component Reference

### Modal

| Component | Class | Description |
|---|---|---|
| `modal.base-modal` | `Modal/BaseModal.php` | Abstract base class that defines the contract (title, description, fields, save/delete URLs, icon path) all concrete modal components must implement. |

---

### Portfolio (public view)

| Component | Class | Description |
|---|---|---|
| `portfolio.view-page` | `Portfolio/ViewPage.php` | Root component for the public portfolio page. Resolves asset URLs and determines `$isOwner`. |
| `portfolio.layout` | `Portfolio/Layout.php` | Outer HTML shell (navigation, page wrapper) for the portfolio view. Also provides `generateUUID()` for upload tracking. |
| `portfolio.header` | `Portfolio/Header.php` | Hero section showing cover image, avatar, name, title, sector, and follow/edit actions. |
| `portfolio.bio-section` | `Portfolio/BioSection.php` | Designer biography text block with optional inline edit link for the owner. |
| `portfolio.contact-section` | `Portfolio/ContactSection.php` | Contact details: email, phone, website, and social links. |
| `portfolio.skills-display` | `Portfolio/SkillsDisplay.php` | Skills tag badges with optional edit link for the owner. |
| `portfolio.tabs` | `Portfolio/Tabs.php` | Tabbed area containing the Projects, Products, and Services panels. |
| `portfolio.modals` | `Portfolio/Modals.php` | Groups all Alpine.js modals (contact, follow, subscribe) into one include. |

---

### Profile (authenticated edit)

| Component | Class | Description |
|---|---|---|
| `profile.edit-page` | `Profile/EditPage.php` | Root component for the profile edit page. Resolves asset URLs for form pre-population. |
| `profile.layout` | `Profile/Layout.php` | Outer HTML shell for the profile edit page. Also provides `generateUUID()` for upload sessions. |
| `profile.tabs` | `Profile/Tabs.php` | Tabbed navigation hosting the Profile Info tab and all portfolio item tabs. |
| `profile.modals` | `Profile/Modals.php` | Groups all CRUD modals (add/edit for projects, products, services) into one include. |
| `profile.skills-section` | `Profile/SkillsSection.php` | Interactive skills picker that loads available options from `DropdownHelper::skills()`. |

---

### Profile Tabs (nested tab panels)

| Component | Class | Description |
|---|---|---|
| `profile.tabs.profile` | `Profile/Tabs/Profile.php` | The "Profile Info" tab panel: name, bio, avatar, cover, contact fields, sector. Pre-populates Alpine.js form state. |
| `profile.tabs.portfolio` | `Profile/Tabs/Portfolio.php` | Generic portfolio item tab panel reused for Projects, Products, and Services. Derives Alpine.js function names dynamically from the `$type` prop. |
