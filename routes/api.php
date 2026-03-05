<?php

use App\Http\Controllers\Api\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
|
| These routes handle incoming webhooks from external services.
| Note: These routes should NOT have CSRF protection.
|
*/

Route::prefix('v1')->group(function () {
    // Jobs.ps Tender Webhook
    // Endpoint: POST /api/v1/tenders/receive
    // Also handles GET for status check and other methods gracefully
    Route::any('/tenders/receive', [WebhookController::class, 'handleTender'])
        ->name('api.webhooks.tenders.receive');
});
