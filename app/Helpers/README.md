# Helpers

This directory contains static utility helper classes used throughout the application.

---

## AssetHelper

**File:** `AssetHelper.php`
**Namespace:** `App\Helpers\AssetHelper`

Provides cache-busting versioned URLs for public assets and uploaded media files.

### Methods

| Method | Description |
|---|---|
| `versioned(string $path): string` | Returns `asset($path)?v={config asset_version}`. Use when you want a fixed version string controlled by `config/app.php` → `asset_version`. |
| `autoVersioned(string $path): string` | Returns `asset($path)?v={filemtime}`. Automatically busts the cache whenever the file on disk changes. Falls back to the config version if the file does not exist. |
| `storage(string $path): string` | Returns a versioned URL under the `/media/` prefix for uploaded files (e.g. avatars, product images). Uses the config version string. |

### Blade Directives

Two Blade directives are registered by `AppServiceProvider::boot()`:

```blade
@versionedAsset('css/app.css')
@autoVersionedAsset('js/app.js')
```

These are equivalent to calling `AssetHelper::versioned(...)` and `AssetHelper::autoVersioned(...)` directly but are more concise in templates.

### Direct Usage in Blade

```blade
<link href="{{ \App\Helpers\AssetHelper::versioned('css/app.css') }}" rel="stylesheet">
<img src="{{ \App\Helpers\AssetHelper::storage('profiles/profile_42.jpg') }}">
```

---

## DropdownHelper

**File:** `DropdownHelper.php`
**Namespace:** `App\Helpers\DropdownHelper`

Centralised access point for all dropdown option lists used across forms (registration, profile edit, admin panels). Every public method:

1. Checks whether the `dropdown_options` table exists (gracefully handles fresh installs / migrations not yet run).
2. Fetches from the database (with cache via `DropdownOption` model methods).
3. Falls back to hardcoded default arrays if the table is empty or unavailable.

### Data Methods

| Method | Returns | Used for |
|---|---|---|
| `sectorsForJs(): array` | Sector objects with nested `subSectors` arrays | Registration wizard JS sector/subsector picker |
| `sectorOptions(): array` | `[value, label]` pairs localised to current locale | Searchable sector select in profile edit |
| `subsectorsByType(): array` | `[sector_value => string[]]` map | Dependent subsector dropdown |
| `skills(): array` | Flat list of skill label strings | Skills multi-select |
| `cities(): array` | Flat list of city/governorate label strings (localised) | City select |
| `citiesKeyValue(): array` | `[english_key => localised_label]` pairs | City select where stored value must be locale-independent |
| `productCategories(): array` | Flat label list | Product category select |
| `projectCategories(): array` | Flat label list | Project category select |
| `projectRoles(): array` | Flat label list | Project role select |
| `serviceCategories(): array` | Flat label list | Service category select |
| `yearsOfExperience(): array` | Flat label list | Experience range select |
| `fablabTypes(): array` | `[value, label]` pairs (localised) | FabLab type select |
| `marketplaceTypes(): array` | `[value, label]` pairs (localised) | Marketplace post type select |
| `marketplaceCategories(): array` | Flat label list (locale-aware cache key) | Marketplace category filter |
| `marketplaceTags(): array` | Flat label list (locale-aware cache key) | Marketplace tag filter |
| `trainingCategories(): array` | Flat label list | Training category select |
| `tenderCategories(): array` | Flat label list | Tender category select |

### Utility Methods

| Method | Description |
|---|---|
| `sanitizeUtf8(mixed $string): string` | Converts a value to valid UTF-8, strips control characters, returns empty string for non-strings. |
| `sanitizeUtf8Array(array $data): array` | Recursively applies `sanitizeUtf8()` to every string value in an array. |
| `formatPhoneWithCountry(?string $phone, ?string $countryCode): string` | Prepends the international dial code for the given ISO 3166-1 alpha-2 country code. Handles leading zeros and already-formatted `+` prefixed numbers. |

### Cache Behaviour

The `DropdownOption` model caches results internally. `DropdownHelper` adds a guard for the known edge case where the cache holds an empty array (e.g. first-run race condition): it clears the stale key before fetching fresh data.

Locale-sensitive lists (`marketplaceCategories`, `marketplaceTags`) use locale-suffixed cache keys (e.g. `dropdown_options_marketplace_category_labels_ar`) so Arabic and English lists are cached independently.
