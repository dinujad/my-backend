<?php

use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\ProductReviewApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API — no authentication required
|--------------------------------------------------------------------------
*/
$publicRoutes = function () {
    Route::get('/products',                            [ProductApiController::class, 'index']);
    Route::get('/products/by-category/{categorySlug}', [ProductApiController::class, 'byCategory']);
    Route::get('/products/{slug}/reviews',             [ProductReviewApiController::class, 'index']);
    Route::post('/products/{slug}/reviews',            [ProductReviewApiController::class, 'store']);
    Route::get('/products/{slug}',                     [ProductApiController::class, 'show']);

    Route::get('/categories',        [CategoryApiController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryApiController::class, 'show']);

    Route::post('/checkout', [\App\Http\Controllers\Api\CheckoutApiController::class, 'store']);

    // Live Chat Widget Routes
    Route::post('/chat/init', [\App\Http\Controllers\Api\LiveChatApiController::class, 'init']);
    Route::get('/chat/{sessionId}', [\App\Http\Controllers\Api\LiveChatApiController::class, 'getHistory']);
    Route::post('/chat/{sessionId}/message', [\App\Http\Controllers\Api\LiveChatApiController::class, 'sendMessage']);
    Route::post('/chat/{sessionId}/read', [\App\Http\Controllers\Api\LiveChatApiController::class, 'markRead']);

    // API Authentication for Next.js Frontend
    Route::post('/auth/login', [\App\Http\Controllers\Api\ApiAuthController::class, 'login']);
    Route::post('/auth/register', [\App\Http\Controllers\Api\ApiAuthController::class, 'register']);

    // Quote Request (public — guest + logged-in customers)
    Route::post('/quote-requests', [\App\Http\Controllers\Api\QuoteRequestApiController::class, 'store']);
    Route::get('/quote-requests/products/search', [\App\Http\Controllers\Api\QuoteRequestApiController::class, 'productSearch']);
    Route::get('/quote/view/{token}', [\App\Http\Controllers\Api\QuoteRequestApiController::class, 'viewQuotation']);

    // Shipping
    Route::get('/shipping/districts', [\App\Http\Controllers\Api\ShippingApiController::class, 'districts']);
    Route::get('/shipping/rates', [\App\Http\Controllers\Api\ShippingApiController::class, 'rates']);

    // Payment methods for checkout
    Route::get('/payment-methods', [\App\Http\Controllers\Api\PaymentMethodApiController::class, 'index']);
    Route::post('/payment-methods/for-cart', [\App\Http\Controllers\Api\PaymentMethodApiController::class, 'forCart']);

    // PayHere initiate (needs order_number)
    Route::post('/payments/payhere/initiate', [\App\Http\Controllers\Api\PayhereController::class, 'initiate']);
};

// Register public routes both with and without /v1 prefix for backwards compatibility
Route::prefix('v1')->group($publicRoutes);
Route::group([], $publicRoutes);

/*
|--------------------------------------------------------------------------
| Authenticated user endpoint
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Customer Dashboard
    Route::get('/dashboard/summary', [\App\Http\Controllers\Api\CustomerDashboardController::class, 'summary']);
    Route::get('/dashboard/orders', [\App\Http\Controllers\Api\CustomerDashboardController::class, 'orders']);
    Route::get('/dashboard/orders/{id}', [\App\Http\Controllers\Api\CustomerDashboardController::class, 'orderDetails']);
    Route::get('/dashboard/invoices', [\App\Http\Controllers\Api\CustomerDashboardController::class, 'invoices']);
});

/*
|--------------------------------------------------------------------------
| Admin endpoints
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/orders', [\App\Http\Controllers\Api\AdminOrderController::class, 'index']);
    Route::get('/orders/{id}', [\App\Http\Controllers\Api\AdminOrderController::class, 'show']);
    Route::post('/orders/{id}/status', [\App\Http\Controllers\Api\AdminOrderController::class, 'updateStatus']);
    Route::post('/orders/{id}/payment-status', [\App\Http\Controllers\Api\AdminOrderController::class, 'updatePaymentStatus']);
    Route::post('/orders/{id}/shipment', [\App\Http\Controllers\Api\AdminOrderController::class, 'addShipment']);
    Route::post('/orders/{id}/invoice', [\App\Http\Controllers\Api\AdminOrderController::class, 'generateInvoice']);
    Route::post('/email-logs/{id}/resend', [\App\Http\Controllers\Api\AdminOrderController::class, 'resendEmail']);
    Route::post('/sms-logs/{id}/resend', [\App\Http\Controllers\Api\AdminOrderController::class, 'resendSms']);
});

/*
|--------------------------------------------------------------------------
| PayHere Notify (webhook — public, no auth, no CSRF)
|--------------------------------------------------------------------------
*/
Route::post('/payments/payhere/notify', [\App\Http\Controllers\Api\PayhereController::class, 'notify'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

/*
|--------------------------------------------------------------------------
| Admin Quote Endpoints
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin/quotes')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\AdminQuoteController::class, 'index']);
    Route::get('/statuses', [\App\Http\Controllers\Api\AdminQuoteController::class, 'statuses']);
    Route::get('/{id}', [\App\Http\Controllers\Api\AdminQuoteController::class, 'show']);
    Route::post('/{id}/status', [\App\Http\Controllers\Api\AdminQuoteController::class, 'updateStatus']);
    Route::post('/{id}/assign', [\App\Http\Controllers\Api\AdminQuoteController::class, 'assignStaff']);
    Route::post('/{id}/admin-notes', [\App\Http\Controllers\Api\AdminQuoteController::class, 'updateAdminNotes']);
    Route::post('/{id}/quotation', [\App\Http\Controllers\Api\AdminQuoteController::class, 'createQuotation']);

    // Quotation-level actions (by quotation ID)
    Route::put('/quotation/{quotationId}', [\App\Http\Controllers\Api\AdminQuoteController::class, 'updateQuotation']);
    Route::post('/quotation/{quotationId}/send-whatsapp', [\App\Http\Controllers\Api\AdminQuoteController::class, 'sendWhatsApp']);
    Route::post('/quotation/{quotationId}/generate-pdf', [\App\Http\Controllers\Api\AdminQuoteController::class, 'generatePdf']);
    Route::post('/quotation/{quotationId}/status', [\App\Http\Controllers\Api\AdminQuoteController::class, 'updateQuotationStatus']);
    Route::post('/quotation/{quotationId}/notes', [\App\Http\Controllers\Api\AdminQuoteController::class, 'addNote']);
});

/*
|--------------------------------------------------------------------------
| Admin Chat Endpoints
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', \App\Http\Middleware\ChatStaffMiddleware::class])->prefix('admin/chat')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\AdminLiveChatController::class, 'index']);
    Route::get('/agents', [\App\Http\Controllers\Api\AdminLiveChatController::class, 'agents']);
    Route::post('/agents', [\App\Http\Controllers\Api\AdminLiveChatController::class, 'storeAgent']);
    Route::get('/{id}', [\App\Http\Controllers\Api\AdminLiveChatController::class, 'show']);
    Route::post('/{id}/message', [\App\Http\Controllers\Api\AdminLiveChatController::class, 'sendMessage']);
    Route::post('/{id}/assign', [\App\Http\Controllers\Api\AdminLiveChatController::class, 'assign']);
    Route::post('/{id}/transfer', [\App\Http\Controllers\Api\AdminLiveChatController::class, 'transfer']);
    Route::post('/{id}/close', [\App\Http\Controllers\Api\AdminLiveChatController::class, 'processClose']);
});
