<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDesignerController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminProjectController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\Admin\AdminMarketplaceController;
use App\Http\Controllers\Admin\AdminFabLabController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminLayoutSettingsController;
use App\Http\Controllers\Admin\AdminCounterSettingsController;
use App\Http\Controllers\Admin\AdminDropdownController;
use App\Http\Controllers\Admin\AdminTrainingController;
use App\Http\Controllers\Admin\AdminTenderController;
use App\Http\Controllers\Admin\AdminAcademicAccountController;
use App\Http\Controllers\Admin\AdminAcademicContentController;
use App\Http\Controllers\Admin\AdminProfileRatingController;
use App\Http\Controllers\Admin\AdminRatingCriteriaController;
use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\Admin\AdminAnalyticsController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes are protected by the admin middleware and require
| the authenticated user to have admin privileges.
|
*/

// Protected Admin Routes
Route::prefix('{locale}/admin')
    ->middleware(['auth:designer', 'admin'])
    ->group(function () {

        // Analytics
        Route::prefix('analytics')->group(function () {
            // Index alias → defaults to overview
            Route::get('/', [AdminAnalyticsController::class, 'show'])
                ->name('admin.analytics.index')
                ->defaults('analyticsPage', 'overview');
            $pages = ['overview', 'engagement', 'traffic', 'geographic', 'workflow', 'improvement', 'search', 'insights'];
            foreach ($pages as $p) {
                Route::get("/{$p}", [AdminAnalyticsController::class, 'show'])
                    ->name("admin.analytics.{$p}")
                    ->defaults('analyticsPage', $p);
                Route::get("/{$p}/export", [AdminAnalyticsController::class, 'exportPage'])
                    ->name("admin.analytics.{$p}.export")
                    ->defaults('analyticsPage', $p);
            }
            Route::post('/refresh', [AdminAnalyticsController::class, 'refresh'])
                ->name('admin.analytics.refresh');
        });

        // Dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard.alt');
        Route::get('/pending-counts', [AdminDashboardController::class, 'pendingCounts'])->name('admin.pending-counts');

        // Designers (Accounts) Management
        Route::prefix('designers')->name('admin.designers.')->group(function () {
            Route::get('/', [AdminDesignerController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminDesignerController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminDesignerController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminDesignerController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::post('/{id}/toggle-active', [AdminDesignerController::class, 'toggleActive'])->name('toggle-active')->where('id', '[0-9]+');
            Route::post('/{id}/toggle-trusted', [AdminDesignerController::class, 'toggleTrusted'])->name('toggle-trusted')->where('id', '[0-9]+');
            Route::post('/{id}/reset-password', [AdminDesignerController::class, 'resetPassword'])->name('reset-password')->where('id', '[0-9]+');
            Route::delete('/{id}', [AdminDesignerController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/bulk-action', [AdminDesignerController::class, 'bulkAction'])->name('bulk-action');
        });

        // Products Management
        Route::prefix('products')->name('admin.products.')->group(function () {
            Route::get('/', [AdminProductController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminProductController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminProductController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminProductController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}/images/{imageId}', [AdminProductController::class, 'deleteImage'])->name('deleteImage')->where(['id' => '[0-9]+', 'imageId' => '[0-9]+']);
            Route::post('/{id}/approve', [AdminProductController::class, 'approve'])->name('approve')->where('id', '[0-9]+');
            Route::post('/{id}/reject', [AdminProductController::class, 'reject'])->name('reject')->where('id', '[0-9]+');
            Route::delete('/{id}', [AdminProductController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/bulk-action', [AdminProductController::class, 'bulkAction'])->name('bulk-action');
        });

        // Projects Management
        Route::prefix('projects')->name('admin.projects.')->group(function () {
            Route::get('/', [AdminProjectController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminProjectController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminProjectController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminProjectController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}/images/{imageId}', [AdminProjectController::class, 'deleteImage'])->name('deleteImage')->where(['id' => '[0-9]+', 'imageId' => '[0-9]+']);
            Route::post('/{id}/approve', [AdminProjectController::class, 'approve'])->name('approve')->where('id', '[0-9]+');
            Route::post('/{id}/reject', [AdminProjectController::class, 'reject'])->name('reject')->where('id', '[0-9]+');
            Route::delete('/{id}', [AdminProjectController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/bulk-action', [AdminProjectController::class, 'bulkAction'])->name('bulk-action');
        });

        // Services Management
        Route::prefix('services')->name('admin.services.')->group(function () {
            Route::get('/', [AdminServiceController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminServiceController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminServiceController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminServiceController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::post('/{id}/approve', [AdminServiceController::class, 'approve'])->name('approve')->where('id', '[0-9]+');
            Route::post('/{id}/reject', [AdminServiceController::class, 'reject'])->name('reject')->where('id', '[0-9]+');
            Route::delete('/{id}', [AdminServiceController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/bulk-action', [AdminServiceController::class, 'bulkAction'])->name('bulk-action');
        });

        // Marketplace Posts Management
        Route::prefix('marketplace')->name('admin.marketplace.')->group(function () {
            Route::get('/', [AdminMarketplaceController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminMarketplaceController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminMarketplaceController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminMarketplaceController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::post('/{id}/approve', [AdminMarketplaceController::class, 'approve'])->name('approve')->where('id', '[0-9]+');
            Route::post('/{id}/reject', [AdminMarketplaceController::class, 'reject'])->name('reject')->where('id', '[0-9]+');
            Route::delete('/{id}', [AdminMarketplaceController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/bulk-action', [AdminMarketplaceController::class, 'bulkAction'])->name('bulk-action');
        });

        // FabLabs Management (Full CRUD)
        Route::prefix('fablabs')->name('admin.fablabs.')->group(function () {
            Route::get('/', [AdminFabLabController::class, 'index'])->name('index');
            Route::get('/create', [AdminFabLabController::class, 'create'])->name('create');
            Route::post('/', [AdminFabLabController::class, 'store'])->name('store');
            Route::get('/{id}', [AdminFabLabController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminFabLabController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminFabLabController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', [AdminFabLabController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/bulk-action', [AdminFabLabController::class, 'bulkAction'])->name('bulk-action');
        });

        // Site Settings Management
        Route::prefix('settings')->name('admin.settings.')->group(function () {
            Route::get('/', [AdminSettingsController::class, 'index'])->name('index');
            Route::post('/hero/update', [AdminSettingsController::class, 'updateHeroImage'])->name('hero.update');
            Route::post('/hero/remove', [AdminSettingsController::class, 'removeHeroImage'])->name('hero.remove');
            Route::post('/hero-texts/update', [AdminSettingsController::class, 'updateHeroTexts'])->name('hero-texts.update');
            Route::post('/hero-texts/reset', [AdminSettingsController::class, 'resetHeroTexts'])->name('hero-texts.reset');
            Route::post('/footer/update', [AdminLayoutSettingsController::class, 'updateFooter'])->name('footer.update');
            Route::post('/footer/reset', [AdminLayoutSettingsController::class, 'resetFooter'])->name('footer.reset');
            Route::post('/header/update', [AdminLayoutSettingsController::class, 'updateHeader'])->name('header.update');
            Route::post('/header/reset', [AdminLayoutSettingsController::class, 'resetHeader'])->name('header.reset');
            Route::post('/subheader/update', [AdminLayoutSettingsController::class, 'updateSubheader'])->name('subheader.update');
            Route::post('/subheader/reset', [AdminLayoutSettingsController::class, 'resetSubheader'])->name('subheader.reset');
            Route::get('/counters', [AdminCounterSettingsController::class, 'getCounterSettings'])->name('counters.get');
            Route::post('/counters/update', [AdminCounterSettingsController::class, 'updateCounters'])->name('counters.update');
            Route::post('/counters/reset', [AdminCounterSettingsController::class, 'resetCounters'])->name('counters.reset');
            Route::post('/auto-accept/{type}/toggle', [AdminSettingsController::class, 'toggleAutoAccept'])->name('auto-accept.toggle');
            Route::get('/auto-accept/status', [AdminSettingsController::class, 'getAutoAcceptStatus'])->name('auto-accept.status');
            Route::post('/registration-policies/update', [AdminSettingsController::class, 'updateRegistrationPolicies'])->name('registration-policies.update');
            Route::post('/registration-policies/reset', [AdminSettingsController::class, 'resetRegistrationPolicies'])->name('registration-policies.reset');
        });

        // Dropdown Options Management
        Route::prefix('dropdowns')->name('admin.dropdowns.')->group(function () {
            Route::get('/', [AdminDropdownController::class, 'index'])->name('index');
            Route::get('/{type}', [AdminDropdownController::class, 'show'])->name('show');
            Route::get('/{type}/{parentId}/children', [AdminDropdownController::class, 'showChildren'])->name('children')->where('parentId', '[0-9]+');
            Route::post('/{type}', [AdminDropdownController::class, 'store'])->name('store');
            Route::put('/{type}/{id}', [AdminDropdownController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{type}/{id}', [AdminDropdownController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/{type}/reorder', [AdminDropdownController::class, 'reorder'])->name('reorder');
            Route::post('/{type}/sort-alphabetically', [AdminDropdownController::class, 'sortAlphabetically'])->name('sort-alphabetically');
            Route::post('/{type}/{id}/toggle-active', [AdminDropdownController::class, 'toggleActive'])->name('toggle-active')->where('id', '[0-9]+');
        });

        // CMS Pages Management
        Route::prefix('pages')->name('admin.pages.')->group(function () {
            Route::get('/', [AdminPageController::class, 'index'])->name('index');
            Route::get('/{slug}/edit', [AdminPageController::class, 'edit'])->name('edit');
            Route::post('/{slug}/update', [AdminPageController::class, 'update'])->name('update');
            Route::post('/{slug}/upload-image', [AdminPageController::class, 'uploadImage'])->name('upload-image');
            Route::post('/{slug}/remove-image', [AdminPageController::class, 'removeImage'])->name('remove-image');
            Route::post('/{slug}/reset', [AdminPageController::class, 'reset'])->name('reset');
            Route::post('/{slug}/add-section', [AdminPageController::class, 'addSection'])->name('add-section');
            Route::post('/{slug}/add-faq-item', [AdminPageController::class, 'addFaqItem'])->name('add-faq-item');
            Route::post('/{slug}/add-team-member', [AdminPageController::class, 'addTeamMember'])->name('add-team-member');
        });

        // Public API for dropdowns (used by frontend)
        Route::get('/api/dropdowns/{type}', [AdminDropdownController::class, 'api'])->name('admin.api.dropdowns');

        // Trainings Management (Full CRUD - Admin managed, no approval workflow)
        Route::prefix('trainings')->name('admin.trainings.')->group(function () {
            Route::get('/', [AdminTrainingController::class, 'index'])->name('index');
            Route::get('/create', [AdminTrainingController::class, 'create'])->name('create');
            Route::post('/', [AdminTrainingController::class, 'store'])->name('store');
            Route::get('/{id}', [AdminTrainingController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminTrainingController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminTrainingController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', [AdminTrainingController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/bulk-action', [AdminTrainingController::class, 'bulkAction'])->name('bulk-action');
        });

        // Tenders Management (Full CRUD - Admin managed, no approval workflow)
        Route::prefix('tenders')->name('admin.tenders.')->group(function () {
            Route::get('/', [AdminTenderController::class, 'index'])->name('index');
            Route::get('/create', [AdminTenderController::class, 'create'])->name('create');
            Route::post('/', [AdminTenderController::class, 'store'])->name('store');
            Route::get('/{id}', [AdminTenderController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminTenderController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminTenderController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::post('/{id}', [AdminTenderController::class, 'update'])->where('id', '[0-9]+'); // Alternative POST route for form with _method
            Route::delete('/{id}', [AdminTenderController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/{id}/toggle-visibility', [AdminTenderController::class, 'toggleVisibility'])->name('toggle-visibility')->where('id', '[0-9]+');
            Route::post('/bulk-action', [AdminTenderController::class, 'bulkAction'])->name('bulk-action');
        });

        // ==========================================
        // Academic Accounts Management
        // ==========================================
        Route::prefix('academic-accounts')->name('admin.academic-accounts.')->group(function () {
            Route::get('/', [AdminAcademicAccountController::class, 'index'])->name('index');
            Route::get('/create', [AdminAcademicAccountController::class, 'create'])->name('create');
            Route::post('/', [AdminAcademicAccountController::class, 'store'])->name('store');
            Route::get('/{id}', [AdminAcademicAccountController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminAcademicAccountController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminAcademicAccountController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', [AdminAcademicAccountController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/{id}/toggle-active', [AdminAcademicAccountController::class, 'toggleActive'])->name('toggle-active')->where('id', '[0-9]+');
            Route::post('/{id}/reset-password', [AdminAcademicAccountController::class, 'resetPassword'])->name('reset-password')->where('id', '[0-9]+');
        });

        // ==========================================
        // Academic Content Approval
        // ==========================================
        Route::prefix('academic-content')->name('admin.academic-content.')->group(function () {
            // Trainings
            Route::get('/trainings', [AdminAcademicContentController::class, 'trainings'])->name('trainings');
            Route::get('/trainings/{id}', [AdminAcademicContentController::class, 'showTraining'])->name('trainings.show')->where('id', '[0-9]+');
            Route::post('/trainings/{id}/approve', [AdminAcademicContentController::class, 'approveTraining'])->name('trainings.approve')->where('id', '[0-9]+');
            Route::post('/trainings/{id}/reject', [AdminAcademicContentController::class, 'rejectTraining'])->name('trainings.reject')->where('id', '[0-9]+');
            Route::delete('/trainings/{id}', [AdminAcademicContentController::class, 'deleteTraining'])->name('trainings.delete')->where('id', '[0-9]+');

            // Workshops
            Route::get('/workshops', [AdminAcademicContentController::class, 'workshops'])->name('workshops');
            Route::get('/workshops/{id}', [AdminAcademicContentController::class, 'showWorkshop'])->name('workshops.show')->where('id', '[0-9]+');
            Route::post('/workshops/{id}/approve', [AdminAcademicContentController::class, 'approveWorkshop'])->name('workshops.approve')->where('id', '[0-9]+');
            Route::post('/workshops/{id}/reject', [AdminAcademicContentController::class, 'rejectWorkshop'])->name('workshops.reject')->where('id', '[0-9]+');
            Route::delete('/workshops/{id}', [AdminAcademicContentController::class, 'deleteWorkshop'])->name('workshops.delete')->where('id', '[0-9]+');

            // Announcements
            Route::get('/announcements', [AdminAcademicContentController::class, 'announcements'])->name('announcements');
            Route::get('/announcements/{id}', [AdminAcademicContentController::class, 'showAnnouncement'])->name('announcements.show')->where('id', '[0-9]+');
            Route::post('/announcements/{id}/approve', [AdminAcademicContentController::class, 'approveAnnouncement'])->name('announcements.approve')->where('id', '[0-9]+');
            Route::post('/announcements/{id}/reject', [AdminAcademicContentController::class, 'rejectAnnouncement'])->name('announcements.reject')->where('id', '[0-9]+');
            Route::delete('/announcements/{id}', [AdminAcademicContentController::class, 'deleteAnnouncement'])->name('announcements.delete')->where('id', '[0-9]+');

            // Bulk actions
            Route::post('/bulk-action', [AdminAcademicContentController::class, 'bulkAction'])->name('bulk-action');
        });

        // ==========================================
        // Profile Ratings Management
        // ==========================================
        Route::prefix('ratings')->name('admin.ratings.')->group(function () {
            Route::get('/', [AdminProfileRatingController::class, 'index'])->name('index');
            Route::get('/stats', [AdminProfileRatingController::class, 'stats'])->name('stats');
            Route::get('/analytics', [AdminProfileRatingController::class, 'analytics'])->name('analytics');
            Route::get('/{id}', [AdminProfileRatingController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::post('/{id}/approve', [AdminProfileRatingController::class, 'approve'])->name('approve')->where('id', '[0-9]+');
            Route::post('/{id}/reject', [AdminProfileRatingController::class, 'reject'])->name('reject')->where('id', '[0-9]+');
            Route::post('/bulk-action', [AdminProfileRatingController::class, 'bulkAction'])->name('bulk-action');
            Route::post('/toggle-auto-accept', [AdminProfileRatingController::class, 'toggleAutoAccept'])->name('toggle-auto-accept');

            // Rating Criteria management (nested)
            Route::prefix('criteria')->name('criteria.')->group(function () {
                Route::get('/', [AdminRatingCriteriaController::class, 'index'])->name('index');
                Route::post('/', [AdminRatingCriteriaController::class, 'store'])->name('store');
                Route::put('/{id}', [AdminRatingCriteriaController::class, 'update'])->name('update')->where('id', '[0-9]+');
                Route::post('/{id}/toggle', [AdminRatingCriteriaController::class, 'toggleActive'])->name('toggle')->where('id', '[0-9]+');
                Route::post('/reorder', [AdminRatingCriteriaController::class, 'reorder'])->name('reorder');
                Route::delete('/{id}', [AdminRatingCriteriaController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            });
        });
    });
