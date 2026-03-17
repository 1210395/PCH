# app/Models

Eloquent models for the Palestine Creative Hub. All models reside in the `App\Models` namespace and map to the MySQL database.

---

## Model Index

### User / Account Models

| Model | Table | Description |
|---|---|---|
| `Designer` | `designers` | Primary user model. Implements `MustVerifyEmail` and `Authenticatable`. Represents creative professionals, manufacturers, and showrooms. Uses the `HasSubscriptions` trait. Auto-sets `is_active` on creation based on admin auto-accept setting. |
| `AcademicAccount` | `academic_accounts` | Authenticatable model for academic and TVET institutions. Separate auth guard from `Designer`. |
| `ControlUser` | `users` | Legacy CMS administrator model. Uses session-based permissions (`u_p1` through `u_p20`). |

### Portfolio / Content Models

| Model | Table | Description |
|---|---|---|
| `Product` | `products` | Product catalogue entries. Uses `HasApprovalStatus`. Supports multi-image via `ProductImage`. |
| `ProductImage` | `product_images` | One or more images belonging to a `Product`. |
| `Project` | `projects` | Portfolio project entries. Uses `HasApprovalStatus`. Supports multi-image via `ProjectImage`, likes, views, and comments. |
| `ProjectImage` | `project_images` | One or more images belonging to a `Project`. |
| `ProjectLike` | `project_likes` | Pivot for project likes (designer_id + project_id). |
| `ProjectComment` | `project_comments` | Comments on a project. |
| `ProjectView` | `project_views` | View tracking for project analytics. |
| `Service` | `services` | Service listings (text-only). Uses `HasApprovalStatus`. |

### Marketplace

| Model | Table | Description |
|---|---|---|
| `MarketplacePost` | `marketplace_posts` | Community posts (service / collaboration / showcase / opportunity). Uses `HasApprovalStatus`. |
| `MarketplaceComment` | `marketplace_comments` | Comments on a marketplace post. |
| `Like` | `likes` | Generic like pivot used for products and marketplace posts. |

### Messaging

| Model | Table | Description |
|---|---|---|
| `Conversation` | `conversations` | A conversation thread between two designers. |
| `Message` | `messages` | Individual messages within a conversation. |
| `MessageRequest` | `message_requests` | A request to start a conversation; must be accepted before messaging begins. |
| `ConversationRating` | `conversation_ratings` | A rating left by one designer after a conversation closes. |

### Notifications & Subscriptions

| Model | Table | Description |
|---|---|---|
| `Notification` | `notifications` | In-app notifications for designers. |
| `AcademicNotification` | `academic_notifications` | In-app notifications for academic accounts. |
| `ProfileSubscription` | `profile_subscriptions` | Polymorphic subscription to another user's profile (designer or academic). |
| `CategorySubscription` | `category_subscriptions` | Subscription to a content type/category combination; stores JSON arrays for categories, tags, types, and levels. |

### Ratings

| Model | Table | Description |
|---|---|---|
| `ProfileRating` | `profile_ratings` | Star rating of a designer's profile across four criteria. Uses `HasApprovalStatus` for moderation. |
| `RatingCriteria` | `rating_criteria` | Admin-configurable rating dimensions (e.g., professionalism, quality). |
| `RatingCriteriaResponse` | `rating_criteria_responses` | Individual criterion scores within a `ProfileRating`. |

### Platform / Discovery

| Model | Table | Description |
|---|---|---|
| `FabLab` | `fab_labs` | Fabrication laboratory entries managed by admins. |
| `Tender` | `tenders` | Tender/opportunity listings; can be created by admin or ingested via Jobs.ps webhook. |
| `Training` | `trainings` | Admin-managed training entries (distinct from `AcademicTraining`). |

### Academic Content

| Model | Table | Description |
|---|---|---|
| `AcademicTraining` | `academic_trainings` | Training programmes submitted by academic accounts; requires admin approval. |
| `AcademicWorkshop` | `academic_workshops` | Workshop events submitted by academic accounts; requires admin approval. |
| `AcademicAnnouncement` | `academic_announcements` | Announcements from academic institutions; requires admin approval. |

### Taxonomy / Settings

| Model | Table | Description |
|---|---|---|
| `Category` | `categories` | Hierarchical content categories. |
| `DesignCategory` | `design_categories` | Design-specific subcategories used in the registration wizard. |
| `Skill` | `skills` | Skills that designers can associate with their profiles. |
| `DropdownOption` | `dropdown_options` | CMS-managed dropdown values with Arabic translations, used across forms. |
| `AdminSetting` | `admin_settings` | Key/value store for admin-configurable feature flags (e.g., auto-accept toggles). |
| `SiteSetting` | `site_settings` | Key/value store for public-facing site configuration (hero image, counter labels, etc.). |

---

## Traits

### `Traits/HasApprovalStatus`
Applied to `Product`, `Project`, `Service`, `MarketplacePost`, `ProfileRating`, `AcademicTraining`, `AcademicWorkshop`, and `AcademicAnnouncement`.

Provides:
- `bootHasApprovalStatus()` — sets the initial status (`pending` or `approved`) based on `AdminSetting`.
- `scopePending()`, `scopeApproved()`, `scopeRejected()` — query scopes.
- `scopeVisibleTo($designerId)` — shows approved items plus the owner's own items.
- `approve($adminId)` / `reject($adminId, $reason)` / `resetToPending()` — state transition methods that also send notifications.
- Computed attributes: `approvalBadgeColor`, `approvalLabel`.

### `Traits/HasSubscriptions`
Applied to `Designer` and `AcademicAccount`.

Provides profile and category subscription management: toggle, subscribe, unsubscribe, check, and count helpers. Backed by `ProfileSubscription` and `CategorySubscription` models.

---

## Key Patterns

### Cache Hooks
`Designer`, `Product`, `Project`, `Service`, and `MarketplacePost` call `CacheService::clearDashboardCache()` in their `booted()` / `saved` / `deleted` hooks to keep cached statistics fresh.

### Soft Deletes
Most content models do not use soft deletes — records are hard-deleted or rejected.

### Arabic / Bilingual Fields
Many models store bilingual content in separate fields (e.g., `title` / `title_ar`, `description` / `description_ar`). The active locale determines which field is displayed in Blade templates.
