<?php

use App\Http\Controllers\Auth\DemoLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\AnalyticsController;
use App\Http\Controllers\Dashboard\CustomerController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\NotificationController;
use App\Http\Controllers\Dashboard\OrderController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\OrderTrackingController;
use App\Http\Controllers\Storefront\ShopController;
use App\Models\Store;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $store = Store::where('status', 'active')->orderBy('id')->first();

    return $store
        ? redirect()->route('storefront.home', $store)
        : redirect()->route('login');
})->name('home');

Route::prefix('s/{store:slug}')->name('storefront.')->group(function () {
    Route::get('/', [ShopController::class, 'home'])->name('home');
    Route::get('/menu', [ShopController::class, 'menu'])->name('menu');
    Route::get('/product/{product:slug}', [ShopController::class, 'product'])->name('product');

    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{key}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{key}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});

Route::get('/orders/{order:public_token}', [OrderTrackingController::class, 'show'])->name('track.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::post('/demo-login', DemoLoginController::class)->name('demo-login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/analytics', AnalyticsController::class)->name('analytics.index');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::prefix('dashboard/orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->name('status');
        Route::patch('/{order}/deposit', [OrderController::class, 'updateDeposit'])->name('deposit');
        Route::patch('/{order}/notes', [OrderController::class, 'updateNotes'])->name('notes');
        Route::post('/{order}/messages', [OrderController::class, 'storeMessage'])->name('messages.store');
    });

    Route::post('/dashboard/notifications/read', [NotificationController::class, 'markAllRead'])->name('notifications.read');
    Route::post('/dashboard/notifications/{notification}', [NotificationController::class, 'markRead'])->name('notifications.open');

    Route::prefix('dashboard/products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');

        Route::post('/categories', [ProductController::class, 'storeCategory'])->name('categories.store');
        Route::delete('/categories/{category}', [ProductController::class, 'destroyCategory'])->name('categories.destroy');

        Route::get('/{product:id}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::patch('/{product:id}', [ProductController::class, 'update'])->name('update');
        Route::patch('/{product:id}/availability', [ProductController::class, 'toggle'])->name('availability');
        Route::delete('/{product:id}', [ProductController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('dashboard/customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::patch('/{customer}', [CustomerController::class, 'update'])->name('update');
    });

    Route::prefix('dashboard/settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::patch('/profile', [SettingsController::class, 'updateProfile'])->name('profile');
        Route::patch('/store', [SettingsController::class, 'updateStore'])->name('store');
        Route::patch('/hours', [SettingsController::class, 'updateHours'])->name('hours');
        Route::post('/blocked-dates', [SettingsController::class, 'storeBlockedDate'])->name('blocked.store');
        Route::delete('/blocked-dates/{blockedDate}', [SettingsController::class, 'destroyBlockedDate'])->name('blocked.destroy');
    });
});
