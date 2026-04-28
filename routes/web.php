<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DesignerController;
use App\Http\Controllers\DesignerProfileController;
use App\Http\Controllers\DesignerFollowController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\FabLabController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\MessageRequestController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ValidationController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;

use App\Http\Controllers\EmailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TenderController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AcademicTevetsController;
use App\Http\Controllers\ProfileRatingController;
use App\Http\Controllers\ConversationRatingController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Auth\GoogleOAuthController;

// Include Admin Panel routes
require __DIR__ . '/admin.php';


// ============================================================
// TEMPORARY: Redirect everything to signup wizard
// Remove this section when ready to launch the full site
// ============================================================

// Image serving route - serves images from storage/app/public without symlinks
// Uses /media/ prefix to avoid Apache blocking /storage/ paths on shared hosting
// Supports any nested path depth (e.g., /media/logos/image.jpg or /media/trainings/2024/image.jpg)
Route::get('/media/{path}', function ($path) {
    // Try storage/app/public first, then fall back to public/ directory
    $storagePath = realpath(storage_path("app/public/{$path}"));
    $storageBase = realpath(storage_path('app/public'));
    $publicPath  = realpath(public_path($path));
    $publicBase  = realpath(public_path());

    // Check storage first
    if ($storagePath && $storageBase && str_starts_with($storagePath, $storageBase . DIRECTORY_SEPARATOR)) {
        $filePath = $storagePath;
    // Fall back to public/
    } elseif ($publicPath && $publicBase && str_starts_with($publicPath, $publicBase . DIRECTORY_SEPARATOR)) {
        $filePath = $publicPath;
    } else {
        abort(404);
    }

    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimeMap = [
        'js'    => 'application/javascript',
        'css'   => 'text/css',
        'svg'   => 'image/svg+xml',
        'webp'  => 'image/webp',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
    ];
    $mimeType = $mimeMap[$ext] ?? mime_content_type($filePath) ?: 'application/octet-stream';

    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '[a-zA-Z0-9._\-/]+')->middleware('throttle:500,1');

// Google OAuth2 callback for Gmail API (no locale prefix needed)
Route::get('/oauth2/setup', [GoogleOAuthController::class, 'redirect'])
    ->middleware('throttle:30,1')
    ->name('oauth2.setup');
Route::get('/oauth2/callback', [GoogleOAuthController::class, 'callback'])
    ->middleware('throttle:30,1')
    ->name('oauth2.callback');

// XML Sitemap for search engines
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->middleware('throttle:60,1')->name('sitemap');

// Redirect root based on browser language preference
Route::get('/', function () {
    // Check for saved locale preference in cookie
    $cookieLocale = request()->cookie('locale');
    if ($cookieLocale && in_array($cookieLocale, ['en', 'ar'])) {
        return redirect('/' . $cookieLocale);
    }

    // Detect from Accept-Language header
    $acceptLanguage = request()->header('Accept-Language', '');
    $locale = 'en'; // default
    if (str_contains($acceptLanguage, 'ar')) {
        $locale = 'ar';
    }

    return redirect('/' . $locale);
});

// Favicon route - serve favicon from public directory regardless of locale prefix
Route::get('/{locale}/favicon.ico', function () {
    $path = public_path('favicon.ico');
    if (file_exists($path)) {
        return response()->file($path, ['Content-Type' => 'image/x-icon']);
    }
    abort(404);
})->where('locale', 'en|ar');

// Multilingual routes with locale prefix
Route::group(['prefix' => '{locale}'], function () {
    // Home/Discover page (rate limited)
    Route::get('/', [HomeController::class, 'index'])
        ->middleware(['throttle:200,1', 'track.page:home'])
        ->name('home');

    // Search route (rate limited)
    Route::get('/search', [HomeController::class, 'search'])
        ->middleware('throttle:120,1')
        ->name('search');

    // Instant search API for navbar autocomplete (rate limited)
    Route::get('/search/instant', [HomeController::class, 'instantSearch'])
        ->middleware('throttle:200,1')
        ->name('search.instant');

    // ============================================================
    // CMS STATIC PAGES (About, Terms, Privacy, etc.)
    // ============================================================
    Route::get('/{slug}', [\App\Http\Controllers\PageController::class, 'show'])
        ->where('slug', 'about|support|community-guidelines|terms|privacy|accessibility|sitemap')
        ->name('page.show');

    // ============================================================
    // SIGNUP WIZARD ROUTES (Active)
    // ============================================================
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:20,1')
        ->name('register.post');
    Route::get('/register/success', [AuthController::class, 'showRegistrationSuccess'])->name('register.success');

    // AJAX validation endpoints (BUG-011 Fix: Added rate limiting)
    Route::post('/validate/email', [ValidationController::class, 'checkEmail'])
        ->middleware('throttle:30,1')
        ->name('validate.email');

    // Progressive image upload endpoint
    Route::post('/upload-registration-image', [\App\Http\Controllers\Auth\ImageUploadController::class, 'uploadRegistrationImage'])
        ->middleware('throttle:120,1')
        ->name('upload.registration.image');

    // Progressive PDF upload endpoint (certifications)
    Route::post('/upload-registration-pdf', [\App\Http\Controllers\Auth\ImageUploadController::class, 'uploadRegistrationPdf'])
        ->middleware('throttle:60,1')
        ->name('upload.registration.pdf');

    // ============================================================
    // AUTHENTICATION ROUTES (Active)
    // ============================================================

    // Login routes (rate limited to prevent brute force attacks)
    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->middleware('throttle:120,1')
        ->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:60,1')
        ->name('login.post');

    // ============================================================
    // EMAIL VERIFICATION ROUTES
    // ============================================================
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:20,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:10,5')
        ->name('verification.send');

    // ============================================================
    // PASSWORD RESET ROUTES
    // ============================================================
    Route::get('/password/forgot', [PasswordResetController::class, 'showForgotForm'])
        ->name('password.request');
    Route::post('/password/email', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:3,1')
        ->name('password.email');
    Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/password/reset', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:5,1')
        ->name('password.update');

    // Certification PDF download
    Route::get('/certification/download/{filename}', function ($locale, $filename) {
        // Sanitize filename
        $filename = basename($filename);
        $path = storage_path("app/public/certifications/{$filename}");
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->download($path);
    })->where('filename', '[a-zA-Z0-9_\-\.]+')->middleware('throttle:120,1')->name('certification.download');

    // Public designer portfolio view (no authentication required, rate limited)
    Route::get('/designer/{id}', [DesignerController::class, 'show'])
        ->middleware(['throttle:120,1', 'track.page:designer_profile'])
        ->name('designer.portfolio');

    // Public routes for viewing (rate limited to prevent abuse)
    Route::post('/designer/{id}/track-view', [DesignerController::class, 'trackView'])
        ->middleware('throttle:60,1')
        ->name('designer.track-view');
    Route::get('/designer/{id}/check-following', [DesignerFollowController::class, 'checkFollowing'])
        ->middleware('throttle:60,1')
        ->name('designer.check-following');

    // Public ratings endpoint (view ratings for any designer profile)
    Route::get('/designer/{designerId}/ratings', [ProfileRatingController::class, 'index'])
        ->middleware('throttle:120,1')
        ->name('designer.ratings');

    // Subscription routes (auth required - controller handles multi-guard logic)
    Route::middleware(['auth:designer', 'verified'])->group(function () {
        Route::post('/subscriptions/profile/toggle', [SubscriptionController::class, 'toggleProfileSubscription'])
            ->middleware('throttle:60,1')
            ->name('subscriptions.profile.toggle');
        Route::get('/subscriptions/profile/check', [SubscriptionController::class, 'checkProfileSubscription'])
            ->middleware('throttle:120,1')
            ->name('subscriptions.profile.check');
        Route::get('/subscriptions/category/{contentType}', [SubscriptionController::class, 'getCategorySubscription'])
            ->middleware('throttle:120,1')
            ->name('subscriptions.category.get');
        Route::post('/subscriptions/category/{contentType}', [SubscriptionController::class, 'saveCategorySubscription'])
            ->middleware('throttle:60,1')
            ->name('subscriptions.category.save');
        Route::delete('/subscriptions/category/{contentType}', [SubscriptionController::class, 'deleteCategorySubscription'])
            ->middleware('throttle:60,1')
            ->name('subscriptions.category.delete');
    });

    // Authenticated + verified routes
    Route::middleware(['auth:designer', 'verified'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [DesignerProfileController::class, 'showProfile'])->name('profile');
        Route::get('/account/settings', [DesignerProfileController::class, 'accountSettings'])->name('account.settings');
        Route::post('/account/password/update', [DesignerProfileController::class, 'updatePassword'])->middleware('throttle:5,1')->name('account.password.update');
        Route::post('/account/privacy/update', [DesignerProfileController::class, 'updatePrivacySettings'])->middleware('throttle:30,1')->name('account.privacy.update');
        Route::post('/account/email/update', [DesignerProfileController::class, 'updateEmailPreferences'])->middleware('throttle:30,1')->name('account.email.update');
        Route::post('/account/delete/send-code', [DesignerProfileController::class, 'sendDeleteCode'])->middleware('throttle:3,10')->name('account.delete.send-code');
        Route::post('/account/delete/confirm', [DesignerProfileController::class, 'confirmDelete'])->middleware('throttle:5,10')->name('account.delete.confirm');
        Route::get('/account/upgrade', [DesignerProfileController::class, 'upgradeForm'])->middleware('throttle:60,1')->name('account.upgrade');
        Route::post('/account/upgrade', [DesignerProfileController::class, 'upgradeSubmit'])->middleware('throttle:5,1')->name('account.upgrade.submit');

        // Notification routes (authenticated only, rate limited)
        Route::get('/notifications', [NotificationController::class, 'index'])
            ->middleware('throttle:120,1')
            ->name('notifications.index');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
            ->middleware('throttle:200,1')
            ->name('notifications.unreadCount');
        Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])
            ->middleware('throttle:120,1')
            ->name('notifications.markAsRead');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
            ->middleware('throttle:60,1')
            ->name('notifications.markAllAsRead');

        // Messaging routes (authenticated only, rate limited)
        Route::get('/messages', [MessagesController::class, 'index'])
            ->middleware('throttle:120,1')
            ->name('messages.index');
        Route::get('/messages/unread-count', [MessagesController::class, 'getUnreadCount'])
            ->middleware('throttle:200,1')
            ->name('messages.unreadCount');
        Route::get('/messages/pending-requests-count', [MessageRequestController::class, 'pendingCount'])
            ->middleware('throttle:200,1')
            ->name('messages.pendingRequestsCount');
        Route::get('/messages/requests', [MessageRequestController::class, 'index'])
            ->middleware('throttle:120,1')
            ->name('messages.requests');
        Route::post('/messages/send-request/{designerId}', [MessageRequestController::class, 'send'])
            ->middleware('throttle:60,1')
            ->name('messages.sendRequest');
        Route::get('/messages/check-pending-request/{designerId}', [MessageRequestController::class, 'checkPending'])
            ->middleware('throttle:120,1')
            ->name('messages.checkPendingRequest');
        Route::get('/messages/compose/{designerId}', [MessagesController::class, 'compose'])
            ->middleware('throttle:120,1')
            ->name('messages.compose');
        Route::post('/messages/send/{designerId}', [MessagesController::class, 'send'])
            ->middleware('throttle:60,1')
            ->name('messages.send');
        Route::get('/messages/chat/{designerId}', [MessagesController::class, 'chat'])
            ->middleware('throttle:60,1')
            ->name('messages.chat');
        Route::post('/messages/chat/{conversationId}/send', [MessagesController::class, 'sendInChat'])
            ->middleware('throttle:120,1')
            ->name('messages.sendInChat');
        Route::get('/messages/chat/{conversationId}/messages', [MessagesController::class, 'getMessages'])
            ->middleware('throttle:120,1')
            ->name('messages.getMessages');
        Route::post('/messages/requests/{requestId}/accept', [MessageRequestController::class, 'accept'])
            ->middleware('throttle:60,1')
            ->name('messages.acceptRequest');
        Route::post('/messages/requests/{requestId}/decline', [MessageRequestController::class, 'decline'])
            ->middleware('throttle:60,1')
            ->name('messages.declineRequest');

        // Chat panel routes (for popup chat)
        Route::get('/messages/{conversationId}/fetch', [MessagesController::class, 'fetchMessages'])
            ->middleware('throttle:200,1')
            ->name('messages.fetch');
        Route::post('/messages/{conversationId}/send', [MessagesController::class, 'sendInChat'])
            ->middleware('throttle:120,1')
            ->name('messages.sendInConversation');
        Route::post('/messages/{conversationId}/mark-read', [MessagesController::class, 'markAsRead'])
            ->middleware('throttle:120,1')
            ->name('messages.markAsRead');

        // Conversation rating routes
        Route::get('/messages/{conversationId}/rating-status', [ConversationRatingController::class, 'status'])
            ->middleware('throttle:120,1')
            ->name('messages.rating.status');
        Route::post('/messages/{conversationId}/rate', [ConversationRatingController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('messages.rating.store');
        Route::put('/messages/{conversationId}/rate', [ConversationRatingController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('messages.rating.update');

        // Product management routes (auth required for create/update/delete)
        Route::post('/products', [ProductController::class, 'store'])->middleware('throttle:30,1')->name('products.store');
        Route::match(['PUT', 'POST'], '/products/{id}', [ProductController::class, 'update'])->middleware('throttle:30,1')->name('products.update');
        Route::match(['DELETE', 'POST'], '/products/{id}', [ProductController::class, 'destroy'])->middleware('throttle:30,1')->name('products.destroy');

        // Project management routes (auth required for create/update/delete)
        Route::post('/projects', [ProjectController::class, 'store'])->middleware('throttle:30,1')->name('projects.store');
        Route::match(['PUT', 'POST'], '/projects/{id}', [ProjectController::class, 'update'])->middleware('throttle:30,1')->name('projects.update');
        Route::match(['DELETE', 'POST'], '/projects/{id}', [ProjectController::class, 'destroy'])->middleware('throttle:30,1')->name('projects.destroy');

        // Service management routes (auth required for create/update/delete)
        Route::post('/services', [\App\Http\Controllers\ServiceController::class, 'store'])->middleware('throttle:30,1')->name('services.store');
        Route::match(['PUT', 'POST'], '/services/{id}', [\App\Http\Controllers\ServiceController::class, 'update'])->middleware('throttle:30,1')->name('services.update');
        Route::match(['DELETE', 'POST'], '/services/{id}', [\App\Http\Controllers\ServiceController::class, 'destroy'])->middleware('throttle:30,1')->name('services.destroy');

        // Marketplace post management routes
        Route::post('/marketplace-posts', [\App\Http\Controllers\MarketplacePostController::class, 'store'])->middleware('throttle:30,1')->name('marketplace-posts.store');
        Route::match(['PUT', 'POST'], '/marketplace-posts/{id}', [\App\Http\Controllers\MarketplacePostController::class, 'update'])->middleware('throttle:30,1')->name('marketplace-posts.update');
        Route::match(['DELETE', 'POST'], '/marketplace-posts/{id}/delete', [\App\Http\Controllers\MarketplacePostController::class, 'destroy'])->middleware('throttle:30,1')->name('marketplace-posts.destroy');
        Route::get('/marketplace-posts/source-data', [\App\Http\Controllers\MarketplacePostController::class, 'getSourceData'])->middleware('throttle:60,1')->name('marketplace-posts.source-data');
        Route::post('/marketplace-posts/{id}/share', [\App\Http\Controllers\MarketplacePostController::class, 'shareToUsers'])->middleware('throttle:30,1')->name('marketplace-posts.share');

        // User search & suggestions (for sharing)
        Route::get('/designers/search-users', [DesignerFollowController::class, 'searchUsers'])->middleware('throttle:60,1')->name('designers.search-users');
        Route::get('/designers/suggested-users', [DesignerFollowController::class, 'suggestedUsers'])->middleware('throttle:60,1')->name('designers.suggested-users');

        // Marketplace comments routes (authenticated only, rate limited)
        Route::post('/marketplace/{postId}/comments', [\App\Http\Controllers\MarketplaceCommentController::class, 'store'])
            ->middleware('throttle:60,1')
            ->name('marketplace.comments.store');
        Route::put('/marketplace/{postId}/comments/{commentId}', [\App\Http\Controllers\MarketplaceCommentController::class, 'update'])
            ->middleware('throttle:60,1')
            ->name('marketplace.comments.update');
        Route::delete('/marketplace/{postId}/comments/{commentId}', [\App\Http\Controllers\MarketplaceCommentController::class, 'destroy'])
            ->middleware('throttle:60,1')
            ->name('marketplace.comments.destroy');

        // Designer profile management routes
        Route::get('/profile/edit', [DesignerProfileController::class, 'editProfile'])->name('profile.edit');
        Route::post('/profile/update', [DesignerProfileController::class, 'updateProfile'])->middleware('throttle:30,1')->name('profile.update');
        Route::post('/profile/update-certifications', [DesignerProfileController::class, 'updateCertifications'])->middleware('throttle:30,1')->name('profile.update-certifications');
        Route::post('/designer/update-bio', [DesignerProfileController::class, 'updateBio'])->middleware('throttle:30,1')->name('designer.update-bio');
        Route::post('/designer/update-skills', [DesignerProfileController::class, 'updateSkills'])->middleware('throttle:30,1')->name('designer.update-skills');

        // Follow/Unfollow routes (authenticated only, rate limited)
        Route::post('/designer/{id}/follow', [DesignerFollowController::class, 'follow'])
            ->middleware('throttle:60,1')
            ->name('designer.follow');
        Route::post('/designer/{id}/unfollow', [DesignerFollowController::class, 'unfollow'])
            ->middleware('throttle:60,1')
            ->name('designer.unfollow');

        // Like routes (authenticated only, rate limited)
        Route::post('/products/{id}/like', [ProductController::class, 'toggleLike'])
            ->middleware('throttle:120,1')
            ->name('product.like');
        Route::post('/projects/{id}/like', [ProjectController::class, 'toggleLike'])
            ->middleware('throttle:120,1')
            ->name('project.like');
        Route::post('/designer/{id}/like', [DesignerFollowController::class, 'toggleLike'])
            ->middleware('throttle:120,1')
            ->name('designer.like');
        Route::post('/marketplace/{id}/like', [MarketplaceController::class, 'toggleLike'])
            ->middleware('throttle:120,1')
            ->name('marketplace.like');

        // Email routes (authenticated only, rate limited)
        Route::get('/email/compose/{designerId}', [EmailController::class, 'compose'])
            ->middleware('throttle:60,1')
            ->name('email.compose');
        Route::post('/email/send/{designerId}', [EmailController::class, 'send'])
            ->middleware('throttle:5,1')
            ->name('email.send');

        // Profile rating routes (authenticated only, rate limited)
        Route::post('/designer/{designerId}/rate', [ProfileRatingController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('designer.rate');
        Route::get('/designer/{designerId}/my-rating', [ProfileRatingController::class, 'show'])
            ->middleware('throttle:60,1')
            ->name('designer.my-rating');
        Route::put('/designer/{designerId}/rate', [ProfileRatingController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('designer.rate.update');
        Route::delete('/designer/{designerId}/rate', [ProfileRatingController::class, 'destroy'])
            ->middleware('throttle:30,1')
            ->name('designer.rate.delete');
    });

    // ============================================================
    // PUBLIC BROWSE ROUTES (Rate limited to prevent abuse)
    // ============================================================

    // Projects and Products listing pages (public, rate limited)
    Route::get('/projects', [ProjectController::class, 'index'])
        ->middleware(['throttle:200,1', 'track.page:projects'])
        ->name('projects');
    Route::get('/projects/{id}', [ProjectController::class, 'show'])
        ->middleware(['throttle:120,1', 'track.page:project_detail'])
        ->name('project.detail');
    Route::get('/products', [ProductController::class, 'index'])
        ->middleware(['throttle:200,1', 'track.page:products'])
        ->name('products');
    Route::get('/products/{id}', [ProductController::class, 'show'])
        ->middleware(['throttle:120,1', 'track.page:product_detail'])
        ->name('product.detail');

    // Fab Labs listing and detail pages (public, rate limited)
    Route::get('/fab-labs', [FabLabController::class, 'index'])
        ->middleware('throttle:200,1')
        ->name('fab-labs');
    Route::get('/fab-labs/{id}', [FabLabController::class, 'show'])
        ->middleware('throttle:120,1')
        ->name('fab-lab.detail');

    // Designers and Manufacturers listing page (public, rate limited)
    Route::get('/designers', [DesignerController::class, 'index'])
        ->middleware(['throttle:200,1', 'track.page:designers'])
        ->name('designers');

    // Marketplace listing and detail pages (public, rate limited)
    Route::get('/marketplace', [MarketplaceController::class, 'index'])
        ->middleware(['throttle:200,1', 'track.page:marketplace'])
        ->name('marketplace.index');
    Route::get('/marketplace/{id}', [MarketplaceController::class, 'show'])
        ->middleware(['throttle:120,1', 'track.page:marketplace_detail'])
        ->name('marketplace.show');

    // Marketplace comments (public read, auth required for write)
    Route::get('/marketplace/{postId}/comments', [\App\Http\Controllers\MarketplaceCommentController::class, 'index'])
        ->middleware('throttle:120,1')
        ->name('marketplace.comments.index');

    // Trainings listing and detail pages (public, rate limited) - uses AcademicTraining
    Route::get('/trainings', [TrainingController::class, 'index'])
        ->middleware('throttle:200,1')
        ->name('trainings.index');
    Route::get('/trainings/{id}', [TrainingController::class, 'show'])
        ->middleware('throttle:120,1')
        ->name('trainings.show');

    // Announcements (news) - public listing filtered from trainings page
    Route::get('/announcements', function ($locale) {
        return redirect()->route('trainings.index', ['locale' => $locale, 'type' => 'announcement']);
    })->middleware('throttle:200,1')->name('announcements.index');
    Route::get('/announcements/{id}', function ($locale, $id) {
        return redirect()->route('trainings.show', ['locale' => $locale, 'id' => $id, 'type' => 'announcement']);
    })->middleware('throttle:120,1')->name('announcements.show');

    // Tenders listing and detail pages (public, rate limited)
    Route::get('/tenders', [TenderController::class, 'index'])
        ->middleware('throttle:200,1')
        ->name('tenders.index');
    Route::get('/tenders/{id}', [TenderController::class, 'show'])
        ->middleware('throttle:120,1')
        ->name('tenders.show');

    // Services listing and detail pages (public, rate limited)
    Route::get('/services', [ServiceController::class, 'index'])
        ->middleware(['throttle:200,1', 'track.page:services'])
        ->name('services');
    Route::get('/services/{id}', [ServiceController::class, 'show'])
        ->middleware(['throttle:120,1', 'track.page:service_detail'])
        ->name('services.show');

    // Academic & Private Sectors listing and detail pages (public, rate limited)
    Route::get('/academic-tevets', [AcademicTevetsController::class, 'index'])
        ->middleware('throttle:200,1')
        ->name('academic-tevets');
    Route::get('/academic-tevets/{id}', [AcademicTevetsController::class, 'showAcademic'])
        ->middleware('throttle:120,1')
        ->name('academic-institution.show');

    // Note: Removed fallback to avoid catching asset files (js, css, images)
    // Assets are served directly by the web server
})->where('locale', 'en|ar');

// ============================================================
// ADMIN TOOLS (Password Protected)
// ============================================================
use App\Http\Controllers\Admin\ImageMigrationController;

Route::middleware(['auth:designer', 'admin'])->group(function () {
    Route::get('/admin/image-migration', [ImageMigrationController::class, 'index'])
        ->name('admin.image-migration');

    Route::post('/admin/image-migration/migrate', [ImageMigrationController::class, 'migrate'])
        ->name('admin.image-migration.migrate');
});

