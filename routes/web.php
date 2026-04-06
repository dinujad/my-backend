<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AiController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\QuoteRequestController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ProductAjaxController;
use App\Http\Controllers\Admin\WhatsAppCampaignController;
use App\Http\Controllers\Admin\SmsCampaignController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['app' => 'PrintWorks API', 'version' => '1.0'];
});

// Admin login (guest only)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Admin panel
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Products
    Route::post('products/ajax/generate-variations', [ProductAjaxController::class, 'generateVariations'])->name('products.ajax.generate-variations');
    Route::post('products/ajax/categories', [ProductAjaxController::class, 'storeCategory'])->name('products.ajax.categories');
    Route::post('products/ajax/tags', [ProductAjaxController::class, 'storeTag'])->name('products.ajax.tags');
    Route::post('products/ajax/brands', [ProductAjaxController::class, 'storeBrand'])->name('products.ajax.brands');
    Route::resource('products', ProductController::class)->except(['show']);

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Orders
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

    // Customers
    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

    // Blog
    Route::resource('blog', BlogController::class)->except(['show']);

    // Media
    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::post('media', [MediaController::class, 'store'])->name('media.store');
    Route::patch('media/{medium}', [MediaController::class, 'update'])->name('media.update');
    Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');

    // Coupons
    Route::resource('coupons', CouponController::class)->except(['show']);

    // SEO Settings
    Route::get('seo', [SeoController::class, 'index'])->name('seo.index');
    Route::put('seo', [SeoController::class, 'update'])->name('seo.update');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // Quote Requests
    Route::get('quote-requests', [QuoteRequestController::class, 'index'])->name('quote-requests.index');
    Route::get('quote-requests/{quoteRequest}', [QuoteRequestController::class, 'show'])->name('quote-requests.show');
    Route::post('quote-requests/{quoteRequest}/status', [QuoteRequestController::class, 'updateStatus'])->name('quote-requests.status');
    Route::post('quote-requests/{quoteRequest}/notes', [QuoteRequestController::class, 'updateNotes'])->name('quote-requests.notes');
    // AJAX endpoints (session auth, no Sanctum token needed)
    Route::post('quote-requests/{quoteRequest}/save-quotation', [QuoteRequestController::class, 'saveQuotation'])->name('quote-requests.save-quotation');
    Route::post('quote-requests/{quoteRequest}/send-whatsapp', [QuoteRequestController::class, 'sendWhatsApp'])->name('quote-requests.send-whatsapp');
    Route::get('quote-requests/{quoteRequest}/download-pdf', [QuoteRequestController::class, 'downloadPdf'])->name('quote-requests.download-pdf');

    // Payment Methods
    Route::get('payments', [PaymentMethodController::class, 'index'])->name('payments.index');
    Route::get('payments/create', [PaymentMethodController::class, 'create'])->name('payments.create');
    Route::post('payments', [PaymentMethodController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}/edit', [PaymentMethodController::class, 'edit'])->name('payments.edit');
    Route::put('payments/{payment}', [PaymentMethodController::class, 'update'])->name('payments.update');
    Route::delete('payments/{payment}', [PaymentMethodController::class, 'destroy'])->name('payments.destroy');
    Route::post('payments/{payment}/toggle', [PaymentMethodController::class, 'toggle'])->name('payments.toggle');
    Route::post('payments/payhere/settings', [PaymentMethodController::class, 'updatePayhereSettings'])->name('payments.payhere.update');

    // Shipping Management
    Route::get('shipping', [ShippingController::class, 'index'])->name('shipping.index');
    // Zones
    Route::get('shipping/zones/create', [ShippingController::class, 'createZone'])->name('shipping.zones.create');
    Route::post('shipping/zones', [ShippingController::class, 'storeZone'])->name('shipping.zones.store');
    Route::get('shipping/zones/{zone}/edit', [ShippingController::class, 'editZone'])->name('shipping.zones.edit');
    Route::put('shipping/zones/{zone}', [ShippingController::class, 'updateZone'])->name('shipping.zones.update');
    Route::delete('shipping/zones/{zone}', [ShippingController::class, 'destroyZone'])->name('shipping.zones.destroy');
    // Methods
    Route::get('shipping/methods/create', [ShippingController::class, 'createMethod'])->name('shipping.methods.create');
    Route::post('shipping/methods', [ShippingController::class, 'storeMethod'])->name('shipping.methods.store');
    Route::get('shipping/methods/{method}/edit', [ShippingController::class, 'editMethod'])->name('shipping.methods.edit');
    Route::put('shipping/methods/{method}', [ShippingController::class, 'updateMethod'])->name('shipping.methods.update');
    Route::delete('shipping/methods/{method}', [ShippingController::class, 'destroyMethod'])->name('shipping.methods.destroy');

    // AI (FastAPI-backed)
    Route::get('ai/overview', [AiController::class, 'overview'])->name('ai.overview');
    Route::get('ai/predictions', [AiController::class, 'predictions'])->name('ai.predictions');
    Route::get('ai/chat', [AiController::class, 'chatUi'])->name('ai.chat');
    Route::post('ai/chat', [AiController::class, 'chatApi'])->name('ai.chat.api');

    // WhatsApp Campaigns
    Route::get('whatsapp/campaigns', [WhatsAppCampaignController::class, 'index'])->name('whatsapp.campaigns');
    Route::post('whatsapp/campaigns/send', [WhatsAppCampaignController::class, 'send'])->name('whatsapp.send');
    Route::get('whatsapp/campaigns/sample', [WhatsAppCampaignController::class, 'downloadSample'])->name('whatsapp.sample');

    // SMS Campaigns
    Route::get('sms/campaigns', [SmsCampaignController::class, 'index'])->name('sms.campaigns');
    Route::post('sms/campaigns/send', [SmsCampaignController::class, 'send'])->name('sms.send');
    Route::get('sms/campaigns/sample', [SmsCampaignController::class, 'downloadSample'])->name('sms.sample');
});
