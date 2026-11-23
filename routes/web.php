<?php

use App\Http\Controllers\Admin\AddressController as AdminAddressController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DeliveryController as AdminDeliveryController;
use App\Http\Controllers\Admin\FinanceController as AdminFinanceController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\ServiceAreaController as AdminServiceAreaController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\AddressController as CustomerAddressController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\StorageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/products/{product:slug}', [PublicController::class, 'product'])->name('public.product');
Route::get('/service-areas', [PublicController::class, 'serviceAreas'])->name('public.service-areas');
Route::get('/storage/{path}', [StorageController::class, 'show'])->where('path', '.*')->name('storage.show');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Customer Portal Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:customer,admin,super_admin'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [CustomerDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [CustomerDashboardController::class, 'updateProfile'])->name('profile.update');

    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [CustomerOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [CustomerOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{order}/track', [CustomerOrderController::class, 'track'])->name('orders.track');
    Route::get('/api/orders/{order}/location', [CustomerOrderController::class, 'apiLocation'])->name('orders.api-location');
    Route::post('/orders/{order}/review', [CustomerOrderController::class, 'review'])->name('orders.review');

    Route::get('/addresses', [CustomerAddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [CustomerAddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [CustomerAddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [CustomerAddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/addresses/{address}/default', [CustomerAddressController::class, 'setDefault'])->name('addresses.set-default');
});

/*
|--------------------------------------------------------------------------
| Admin Dashboard Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/assign-driver', [AdminOrderController::class, 'assignDriver'])->name('orders.assign-driver');
    Route::put('/orders/{order}/payment', [AdminOrderController::class, 'updatePayment'])->name('orders.payment');

    Route::get('/deliveries', [AdminDeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('/deliveries/map', [AdminDeliveryController::class, 'map'])->name('deliveries.map');
    Route::get('/deliveries/{delivery}', [AdminDeliveryController::class, 'show'])->name('deliveries.show');
    Route::post('/deliveries/{delivery}/start', [AdminDeliveryController::class, 'start'])->name('deliveries.start');
    Route::post('/deliveries/{delivery}/arrive', [AdminDeliveryController::class, 'arrive'])->name('deliveries.arrive');
    Route::post('/deliveries/{delivery}/complete', [AdminDeliveryController::class, 'complete'])->name('deliveries.complete');
    Route::post('/api/deliveries/{delivery}/location', [AdminDeliveryController::class, 'updateLocation'])->name('deliveries.location');
    Route::get('/api/deliveries/live-drivers', [AdminDeliveryController::class, 'apiLiveDrivers'])->name('deliveries.live-drivers');

    Route::resource('addresses', AdminAddressController::class);
    Route::post('/addresses/{address}/default', [AdminAddressController::class, 'setDefault'])->name('addresses.set-default');

    Route::resource('products', AdminProductController::class);
    Route::post('/products/{product}/adjust-stock', [AdminProductController::class, 'adjustStock'])->name('products.adjust-stock');

    Route::get('/finance', [AdminFinanceController::class, 'index'])->name('finance.index');
    Route::post('/finance/expenses', [AdminFinanceController::class, 'storeExpense'])->name('finance.store-expense');
    Route::delete('/finance/expenses/{expense}', [AdminFinanceController::class, 'destroyExpense'])->name('finance.destroy-expense');

    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/toggle', [AdminReviewController::class, 'toggleApproval'])->name('reviews.toggle');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::resource('service-areas', AdminServiceAreaController::class);

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
    Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');
});
