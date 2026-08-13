<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\SetPasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PhoneVerificationController;
use App\Http\Controllers\Webhooks\MidtransWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');

// Must be registered before the {category:slug} wildcard below, or Laravel
// would greedily match "software-services" as a category slug instead.
Route::get('/catalog/software-services', [CatalogController::class, 'softwareServices'])->name('catalog.software-services');

Route::get('/catalog/{category:slug}', [CatalogController::class, 'show'])->name('catalog.show');

// Placeholder — product detail page isn't built yet.
Route::get('/p/{listing:slug}', function () {
    abort(404);
})->name('listings.show');

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::middleware('auth')->group(function () {
    Route::get('/settings/set-password', [SetPasswordController::class, 'show'])->name('settings.set-password');
    Route::post('/settings/set-password', [SetPasswordController::class, 'store'])->name('settings.set-password.store');

    Route::get('/phone/verify', [PhoneVerificationController::class, 'show'])->name('phone.verify');
    Route::post('/phone/send-otp', [PhoneVerificationController::class, 'sendOtp'])
        ->middleware('throttle:send-otp')->name('phone.send-otp');
    Route::post('/phone/verify-otp', [PhoneVerificationController::class, 'verifyOtp'])
        ->middleware('throttle:verify-otp')->name('phone.verify-otp');
});

// Cart — browsing and adding to cart stay open to everyone, including guests.
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::delete('/cart/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');

// Checkout — cart summary is open to everyone (per catalog scope), but placing
// the order itself requires being logged in with a verified phone number.
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');

Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])
    ->middleware(['auth', 'phone.verified'])->name('checkout.place-order');

Route::get('/orders/{order:order_number}', [OrderController::class, 'show'])
    ->middleware('auth')->name('orders.show');

Route::post('/orders/{order:order_number}/pay', [OrderController::class, 'pay'])
    ->middleware('auth')->name('orders.pay');

// CSRF-exempt (see bootstrap/app.php) — Midtrans's servers call this, not a
// browser session. The signature check inside handleWebhook() is the real
// security boundary, not the CSRF token.
Route::post('/webhooks/midtrans', MidtransWebhookController::class)->name('webhooks.midtrans');
