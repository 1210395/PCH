<?php

use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
|
| These routes handle incoming webhooks from external services.
| Note: These routes should NOT have CSRF protection.
|
*/

// L-38: removed dead `Route::get('/user')` Sanctum scaffold —
// no Sanctum guard configured and no UI references it.

Route::prefix('v1')->group(function () {
    // Jobs.ps Tender Webhook
    // L-37: scope to GET (status check) + POST (ingest) only.
    // The previous Route::any accepted PUT/DELETE/PATCH/HEAD/OPTIONS,
    // which gave attackers free hits on the controller dispatcher
    // even though handleTender immediately returns on non-POST.
    Route::match(['GET', 'POST'], '/tenders/receive', [WebhookController::class, 'handleTender'])
        ->name('api.webhooks.tenders.receive');
});
