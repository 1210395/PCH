<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DesignerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\FabLabController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ValidationController;

use App\Http\Controllers\EmailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TenderController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AcademicTevetsController;
use App\Http\Controllers\ProfileRatingController;
use App\Http\Controllers\ConversationRatingController;
use App\Http\Controllers\SubscriptionController;

// Include Admin Panel routes
require __DIR__ . '/admin.php';


// ============================================================
// TEMPORARY: Redirect everything to signup wizard
// Remove this section when ready to launch the full site
// ============================================================

// Image serving route - serves images from storage/app/public without symlinks
Route::get('/storage/{folder}/{filename}', function ($folder, $filename) {
    // Prevent path traversal attacks
    $basePath = realpath(storage_path('app/public'));
    $path = realpath(storage_path("app/public/{$folder}/{$filename}"));

    // Ensure the resolved path is within the allowed base directory
    if (!$path || !$basePath || !str_starts_with($path, $basePath . DIRECTORY_SEPARATOR)) {
        abort(404);
    }

    return response()->file($path, [
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('folder', '[a-zA-Z0-9_/-]+')->where('filename', '[a-zA-Z0-9._-]+');

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
        ->middleware('throttle:100,1')
        ->name('home');

    // Search route (rate limited)
    Route::get('/search', [HomeController::class, 'search'])
        ->middleware('throttle:60,1')
        ->name('search');

    // Instant search API for navbar autocomplete (rate limited)
    Route::get('/search/instant', [HomeController::class, 'instantSearch'])
        ->middleware('throttle:120,1')
        ->name('search.instant');

    // ============================================================
    // SIGNUP WIZARD ROUTES (Active)
    // ============================================================
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.post');
    Route::get('/register/success', [AuthController::class, 'showRegistrationSuccess'])->name('register.success');

    // AJAX validation endpoints (BUG-011 Fix: Added rate limiting)
    Route::post('/validate/email', [ValidationController::class, 'checkEmail'])
        ->middleware('throttle:10,1')
        ->name('validate.email');

    // Progressive image upload endpoint
    Route::post('/upload-registration-image', [\App\Http\Controllers\Auth\ImageUploadController::class, 'uploadRegistrationImage'])
        ->middleware('throttle:10,1')
        ->name('upload.registration.image');

    // Progressive PDF upload endpoint (certifications)
    Route::post('/upload-registration-pdf', [\App\Http\Controllers\Auth\ImageUploadController::class, 'uploadRegistrationPdf'])
        ->middleware('throttle:30,1')
        ->name('upload.registration.pdf');

    // ============================================================
    // AUTHENTICATION ROUTES (Active)
    // ============================================================

    // Login routes (rate limited to prevent brute force attacks)
    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->middleware('throttle:60,1')
        ->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:15,1')
        ->name('login.post');

    // Certification PDF download
    Route::get('/certification/download/{filename}', function ($locale, $filename) {
        // Sanitize filename
        $filename = basename($filename);
        $path = storage_path("app/public/certifications/{$filename}");
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->download($path);
    })->where('filename', '[a-zA-Z0-9_\-\.]+')->middleware('throttle:60,1')->name('certification.download');

    // Public designer portfolio view (no authentication required, rate limited)
    Route::get('/designer/{id}', [DesignerController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('designer.portfolio');

    // Public routes for viewing (rate limited to prevent abuse)
    Route::post('/designer/{id}/track-view', [DesignerController::class, 'trackView'])
        ->middleware('throttle:30,1')
        ->name('designer.track-view');
    Route::get('/designer/{id}/check-following', [DesignerController::class, 'checkFollowing'])
        ->middleware('throttle:30,1')
        ->name('designer.check-following');

    // Public ratings endpoint (view ratings for any designer profile)
    Route::get('/designer/{designerId}/ratings', [ProfileRatingController::class, 'index'])
        ->middleware('throttle:60,1')
        ->name('designer.ratings');

    // Subscription routes (no guard middleware - controller handles both designer/academic auth)
    Route::post('/subscriptions/profile/toggle', [SubscriptionController::class, 'toggleProfileSubscription'])
        ->middleware('throttle:30,1')
        ->name('subscriptions.profile.toggle');
    Route::get('/subscriptions/profile/check', [SubscriptionController::class, 'checkProfileSubscription'])
        ->middleware('throttle:60,1')
        ->name('subscriptions.profile.check');
    Route::get('/subscriptions/category/{contentType}', [SubscriptionController::class, 'getCategorySubscription'])
        ->middleware('throttle:60,1')
        ->name('subscriptions.category.get');
    Route::post('/subscriptions/category/{contentType}', [SubscriptionController::class, 'saveCategorySubscription'])
        ->middleware('throttle:30,1')
        ->name('subscriptions.category.save');
    Route::delete('/subscriptions/category/{contentType}', [SubscriptionController::class, 'deleteCategorySubscription'])
        ->middleware('throttle:30,1')
        ->name('subscriptions.category.delete');

    // Logout route
    Route::middleware('auth:designer')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [DesignerController::class, 'showProfile'])->name('profile');
        Route::get('/account/settings', [DesignerController::class, 'accountSettings'])->name('account.settings');
        Route::post('/account/password/update', [DesignerController::class, 'updatePassword'])->name('account.password.update');
        Route::post('/account/privacy/update', [DesignerController::class, 'updatePrivacySettings'])->name('account.privacy.update');
        Route::post('/account/email/update', [DesignerController::class, 'updateEmailPreferences'])->name('account.email.update');

        // Notification routes (authenticated only, rate limited)
        Route::get('/notifications', [NotificationController::class, 'index'])
            ->middleware('throttle:60,1')
            ->name('notifications.index');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
            ->middleware('throttle:120,1')
            ->name('notifications.unreadCount');
        Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])
            ->middleware('throttle:60,1')
            ->name('notifications.markAsRead');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
            ->middleware('throttle:30,1')
            ->name('notifications.markAllAsRead');

        // Messaging routes (authenticated only, rate limited)
        Route::get('/messages', [MessagesController::class, 'index'])
            ->middleware('throttle:60,1')
            ->name('messages.index');
        Route::get('/messages/unread-count', [MessagesController::class, 'getUnreadCount'])
            ->middleware('throttle:120,1')
            ->name('messages.unreadCount');
        Route::get('/messages/pending-requests-count', [MessagesController::class, 'getPendingRequestsCount'])
            ->middleware('throttle:120,1')
            ->name('messages.pendingRequestsCount');
        Route::get('/messages/requests', [MessagesController::class, 'requests'])
            ->middleware('throttle:60,1')
            ->name('messages.requests');
        Route::post('/messages/send-request/{designerId}', [MessagesController::class, 'sendRequest'])
            ->middleware('throttle:30,1')
            ->name('messages.sendRequest');
        Route::get('/messages/check-pending-request/{designerId}', [MessagesController::class, 'checkPendingRequest'])
            ->middleware('throttle:60,1')
            ->name('messages.checkPendingRequest');
        Route::get('/messages/compose/{designerId}', [MessagesController::class, 'compose'])
            ->middleware('throttle:30,1')
            ->name('messages.compose');
        Route::post('/messages/send/{designerId}', [MessagesController::class, 'send'])
            ->middleware('throttle:10,1')
            ->name('messages.send');
        Route::get('/messages/chat/{designerId}', [MessagesController::class, 'chat'])
            ->middleware('throttle:30,1')
            ->name('messages.chat');
        Route::post('/messages/chat/{conversationId}/send', [MessagesController::class, 'sendInChat'])
            ->middleware('throttle:30,1')
            ->name('messages.sendInChat');
        Route::get('/messages/chat/{conversationId}/messages', [MessagesController::class, 'getMessages'])
            ->middleware('throttle:60,1')
            ->name('messages.getMessages');
        Route::post('/messages/requests/{requestId}/accept', [MessagesController::class, 'acceptRequest'])
            ->middleware('throttle:10,1')
            ->name('messages.acceptRequest');
        Route::post('/messages/requests/{requestId}/decline', [MessagesController::class, 'declineRequest'])
            ->middleware('throttle:10,1')
            ->name('messages.declineRequest');

        // Chat panel routes (for popup chat)
        Route::get('/messages/{conversationId}/fetch', [MessagesController::class, 'fetchMessages'])
            ->middleware('throttle:120,1')
            ->name('messages.fetch');
        Route::post('/messages/{conversationId}/send', [MessagesController::class, 'sendMessageInConversation'])
            ->middleware('throttle:60,1')
            ->name('messages.sendInConversation');
        Route::post('/messages/{conversationId}/mark-read', [MessagesController::class, 'markAsRead'])
            ->middleware('throttle:60,1')
            ->name('messages.markAsRead');

        // Conversation rating routes
        Route::get('/messages/{conversationId}/rating-status', [ConversationRatingController::class, 'status'])
            ->middleware('throttle:60,1')
            ->name('messages.rating.status');
        Route::post('/messages/{conversationId}/rate', [ConversationRatingController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('messages.rating.store');
        Route::put('/messages/{conversationId}/rate', [ConversationRatingController::class, 'update'])
            ->middleware('throttle:10,1')
            ->name('messages.rating.update');

        // Product management routes
        Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
        Route::post('/products', [ProductController::class, 'store'])->middleware('throttle:10,1')->name('products.store');
        Route::match(['PUT', 'POST'], '/products/{id}', [ProductController::class, 'update'])->name('products.update');
        Route::match(['DELETE', 'POST'], '/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Project management routes
        Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('projects.show');
        Route::post('/projects', [ProjectController::class, 'store'])->middleware('throttle:10,1')->name('projects.store');
        Route::match(['PUT', 'POST'], '/projects/{id}', [ProjectController::class, 'update'])->name('projects.update');
        Route::match(['DELETE', 'POST'], '/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');

        // Service management routes
        Route::get('/services/{id}', [\App\Http\Controllers\ServiceController::class, 'show'])->name('services.manage');
        Route::post('/services', [\App\Http\Controllers\ServiceController::class, 'store'])->middleware('throttle:10,1')->name('services.store');
        Route::match(['PUT', 'POST'], '/services/{id}', [\App\Http\Controllers\ServiceController::class, 'update'])->name('services.update');
        Route::match(['DELETE', 'POST'], '/services/{id}', [\App\Http\Controllers\ServiceController::class, 'destroy'])->name('services.destroy');

        // Marketplace post management routes
        Route::post('/marketplace-posts', [\App\Http\Controllers\MarketplacePostController::class, 'store'])->middleware('throttle:10,1')->name('marketplace-posts.store');
        Route::match(['PUT', 'POST'], '/marketplace-posts/{id}', [\App\Http\Controllers\MarketplacePostController::class, 'update'])->name('marketplace-posts.update');
        Route::match(['DELETE', 'POST'], '/marketplace-posts/{id}', [\App\Http\Controllers\MarketplacePostController::class, 'destroy'])->name('marketplace-posts.destroy');
        Route::get('/marketplace-posts/source-data', [\App\Http\Controllers\MarketplacePostController::class, 'getSourceData'])->name('marketplace-posts.source-data');
        Route::post('/marketplace-posts/{id}/share', [\App\Http\Controllers\MarketplacePostController::class, 'shareToUsers'])->middleware('throttle:10,1')->name('marketplace-posts.share');

        // User search (for sharing)
        Route::get('/designers/search-users', [\App\Http\Controllers\DesignerController::class, 'searchUsers'])->name('designers.search-users');

        // Marketplace comments routes (authenticated only, rate limited)
        Route::post('/marketplace/{postId}/comments', [\App\Http\Controllers\MarketplaceCommentController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('marketplace.comments.store');
        Route::put('/marketplace/{postId}/comments/{commentId}', [\App\Http\Controllers\MarketplaceCommentController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('marketplace.comments.update');
        Route::delete('/marketplace/{postId}/comments/{commentId}', [\App\Http\Controllers\MarketplaceCommentController::class, 'destroy'])
            ->middleware('throttle:30,1')
            ->name('marketplace.comments.destroy');

        // Designer profile management routes
        Route::get('/profile/edit', [DesignerController::class, 'editProfile'])->name('profile.edit');
        Route::post('/profile/update', [DesignerController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/update-certifications', [DesignerController::class, 'updateCertifications'])->name('profile.update-certifications');
        Route::post('/designer/update-bio', [DesignerController::class, 'updateBio'])->name('designer.update-bio');
        Route::post('/designer/update-skills', [DesignerController::class, 'updateSkills'])->name('designer.update-skills');

        // Follow/Unfollow routes (authenticated only, rate limited)
        Route::post('/designer/{id}/follow', [DesignerController::class, 'follow'])
            ->middleware('throttle:30,1')
            ->name('designer.follow');
        Route::post('/designer/{id}/unfollow', [DesignerController::class, 'unfollow'])
            ->middleware('throttle:30,1')
            ->name('designer.unfollow');

        // Like routes (authenticated only, rate limited)
        Route::post('/products/{id}/like', [ProductController::class, 'toggleLike'])
            ->middleware('throttle:60,1')
            ->name('product.like');
        Route::post('/projects/{id}/like', [ProjectController::class, 'toggleLike'])
            ->middleware('throttle:60,1')
            ->name('project.like');
        Route::post('/designer/{id}/like', [DesignerController::class, 'toggleLike'])
            ->middleware('throttle:60,1')
            ->name('designer.like');
        Route::post('/marketplace/{id}/like', [MarketplaceController::class, 'toggleLike'])
            ->middleware('throttle:60,1')
            ->name('marketplace.like');

        // Email routes (authenticated only, rate limited)
        Route::get('/email/compose/{designerId}', [EmailController::class, 'compose'])
            ->middleware('throttle:30,1')
            ->name('email.compose');
        Route::post('/email/send/{designerId}', [EmailController::class, 'send'])
            ->middleware('throttle:5,1')
            ->name('email.send');

        // Profile rating routes (authenticated only, rate limited)
        Route::post('/designer/{designerId}/rate', [ProfileRatingController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('designer.rate');
        Route::get('/designer/{designerId}/my-rating', [ProfileRatingController::class, 'show'])
            ->middleware('throttle:30,1')
            ->name('designer.my-rating');
        Route::put('/designer/{designerId}/rate', [ProfileRatingController::class, 'update'])
            ->middleware('throttle:10,1')
            ->name('designer.rate.update');
        Route::delete('/designer/{designerId}/rate', [ProfileRatingController::class, 'destroy'])
            ->middleware('throttle:10,1')
            ->name('designer.rate.delete');
    });

    // ============================================================
    // PUBLIC BROWSE ROUTES (Rate limited to prevent abuse)
    // ============================================================

    // Projects and Products listing pages (public, rate limited)
    Route::get('/projects', [ProjectController::class, 'index'])
        ->middleware('throttle:100,1')
        ->name('projects');
    Route::get('/projects/{id}', [ProjectController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('project.detail');
    Route::get('/products', [ProductController::class, 'index'])
        ->middleware('throttle:100,1')
        ->name('products');
    Route::get('/products/{id}', [ProductController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('product.detail');

    // Fab Labs listing and detail pages (public, rate limited)
    Route::get('/fab-labs', [FabLabController::class, 'index'])
        ->middleware('throttle:100,1')
        ->name('fab-labs');
    Route::get('/fab-labs/{id}', [FabLabController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('fab-lab.detail');

    // Designers and Manufacturers listing page (public, rate limited)
    Route::get('/designers', [DesignerController::class, 'index'])
        ->middleware('throttle:100,1')
        ->name('designers');

    // Marketplace listing and detail pages (public, rate limited)
    Route::get('/marketplace', [MarketplaceController::class, 'index'])
        ->middleware('throttle:100,1')
        ->name('marketplace.index');
    Route::get('/marketplace/{id}', [MarketplaceController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('marketplace.show');

    // Marketplace comments (public read, auth required for write)
    Route::get('/marketplace/{postId}/comments', [\App\Http\Controllers\MarketplaceCommentController::class, 'index'])
        ->middleware('throttle:60,1')
        ->name('marketplace.comments.index');

    // Trainings listing and detail pages (public, rate limited) - uses AcademicTraining
    Route::get('/trainings', [TrainingController::class, 'index'])
        ->middleware('throttle:100,1')
        ->name('trainings.index');
    Route::get('/trainings/{id}', [TrainingController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('trainings.show');

    // Tenders listing and detail pages (public, rate limited)
    Route::get('/tenders', [TenderController::class, 'index'])
        ->middleware('throttle:100,1')
        ->name('tenders.index');
    Route::get('/tenders/{id}', [TenderController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('tenders.show');

    // Services listing and detail pages (public, rate limited)
    Route::get('/services', [ServiceController::class, 'index'])
        ->middleware('throttle:100,1')
        ->name('services');
    Route::get('/services/{id}', [ServiceController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('services.show');

    // Academic & Private Sectors listing and detail pages (public, rate limited)
    Route::get('/academic-tevets', [AcademicTevetsController::class, 'index'])
        ->middleware('throttle:100,1')
        ->name('academic-tevets');
    Route::get('/academic-tevets/{id}', [AcademicTevetsController::class, 'showAcademic'])
        ->middleware('throttle:60,1')
        ->name('academic-institution.show');

    // Note: Removed fallback to avoid catching asset files (js, css, images)
    // Assets are served directly by the web server

    /* ============================================================
    // ORIGINAL ROUTES - Uncomment when ready to launch full site
    // ============================================================

    // Home page
    // Route::get('/', [HomeController::class, 'index'])->name('home');

    // Projects
    // Route::get('/projects', [ProjectController::class, 'index'])->name('projects');
    // Route::get('/project/{id}', [ProjectController::class, 'show'])->name('project.detail');
    // Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create')->middleware('auth');
    // Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store')->middleware('auth');

    // Designers
    // Route::get('/designers', [DesignerController::class, 'index'])->name('designers');
    // Route::get('/designer/{id}', [DesignerController::class, 'show'])->name('designer.portfolio');

    // Products
    // Route::get('/products', [ProductController::class, 'index'])->name('products');
    // Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.detail');

    // Fab Labs
    // Route::get('/fab-labs', [FabLabController::class, 'index'])->name('fab-labs');
    // Route::get('/fab-lab/{id}', [FabLabController::class, 'show'])->name('fab-lab.detail');

    // Marketplace
    // Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace');
    // Route::get('/post/{id}', [MarketplaceController::class, 'show'])->name('post.detail');

    // Authentication Routes
    // Route::middleware('guest')->group(function () {
    //     Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    //     Route::post('/login', [AuthController::class, 'login']);
    //     Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    //     Route::post('/register', [AuthController::class, 'register']);
    //     Route::get('/register/success', [AuthController::class, 'showRegistrationSuccess'])->name('register.success');
    //     Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    // });

    // Route::middleware('auth')->group(function () {
    //     Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // });
    */
});

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
