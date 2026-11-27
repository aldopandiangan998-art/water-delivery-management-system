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
Route::get('/language/{locale}', function (\Illuminate\Http\Request $request, string $locale) {
    if (in_array($locale, ['id', 'en'])) {
        session(['locale' => $locale]);
        session()->save();
        cookie()->queue(cookie()->forever('locale', $locale));
        \Illuminate\Support\Facades\App::setLocale($locale);
    }

    $returnUrl = $request->query('return');
    if ($returnUrl && filter_var($returnUrl, FILTER_VALIDATE_URL) && str_starts_with($returnUrl, url('/'))) {
        return redirect()->to($returnUrl);
    }

    return redirect()->back(fallback: route('home'));
})->name('language.switch');
Route::get('/products/{product:slug}', [PublicController::class, 'product'])->name('public.product');
Route::get('/service-areas', [PublicController::class, 'serviceAreas'])->name('public.service-areas');
Route::get('/ui-kit', fn() => view('ui-kit'))->name('ui-kit');
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

Route::get('/dashboard', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    if ($user && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    if ($user) {
        return redirect()->route('customer.dashboard');
    }
    return redirect()->route('login');
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| Customer Portal Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:customer,admin'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [CustomerDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [CustomerDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::get('/store-profile', [CustomerDashboardController::class, 'storeProfile'])->name('store-profile');

    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [CustomerOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [CustomerOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/address', [CustomerOrderController::class, 'updateAddress'])->name('orders.update-address');
    Route::post('/orders/{order}/confirm-payment', [CustomerOrderController::class, 'confirmPayment'])->name('orders.confirm-payment');
    Route::post('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{order}/track', [CustomerOrderController::class, 'track'])->name('orders.track');
    Route::get('/orders/{order}/status-check', [CustomerOrderController::class, 'statusCheck'])->name('orders.status-check');
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
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [AdminDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [AdminDashboardController::class, 'updateProfile'])->name('profile.update');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/start-delivery', [AdminOrderController::class, 'startDelivery'])->name('orders.start-delivery');
    Route::post('/orders/{order}/complete-delivery', [AdminOrderController::class, 'completeDelivery'])->name('orders.complete-delivery');
    Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::put('/orders/{order}/payment', [AdminOrderController::class, 'updatePayment'])->name('orders.payment');
    Route::post('/orders/{order}/coordinates', [AdminOrderController::class, 'updateCoordinates'])->name('orders.coordinates');
    Route::post('/orders/save-google-maps-key', [AdminOrderController::class, 'saveGoogleMapsKey'])->name('orders.save-google-maps-key');

    Route::get('/deliveries', [AdminDeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('/deliveries/map', [AdminDeliveryController::class, 'map'])->name('deliveries.map');
    Route::get('/deliveries/geocode', [AdminDeliveryController::class, 'geocode'])->name('deliveries.geocode');
    Route::post('/deliveries/update-depot', [AdminDeliveryController::class, 'updateDepotLocation'])->name('deliveries.update-depot');
    Route::post('/deliveries/save-google-maps-key', [AdminDeliveryController::class, 'saveGoogleMapsKey'])->name('deliveries.save-google-maps-key');
    Route::get('/deliveries/{delivery}', [AdminDeliveryController::class, 'show'])->name('deliveries.show');
    Route::post('/deliveries/{delivery}/start', [AdminDeliveryController::class, 'start'])->name('deliveries.start');
    Route::post('/deliveries/{delivery}/arrive', [AdminDeliveryController::class, 'arrive'])->name('deliveries.arrive');
    Route::post('/deliveries/{delivery}/complete', [AdminDeliveryController::class, 'complete'])->name('deliveries.complete');
    Route::post('/api/deliveries/{delivery}/location', [AdminDeliveryController::class, 'updateLocation'])->name('deliveries.location');
    Route::get('/api/deliveries/live-drivers', [AdminDeliveryController::class, 'apiLiveDrivers'])->name('deliveries.live-drivers');

    Route::resource('addresses', AdminAddressController::class)->except(['create', 'store']);
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
