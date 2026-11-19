<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VendorManagementController;
use App\Http\Controllers\Admin\ProductManagementController;
use App\Http\Controllers\Admin\VendorPurchaseController;
use App\Http\Controllers\Admin\GoodsReceiptController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\InventoryAdjustmentController;
use App\Http\Controllers\Admin\CustomerCatalogController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\VendorMarketplaceController;
use App\Http\Controllers\StorefrontProductImageController;
use App\Http\Controllers\Vendor\VendorController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\VendorNotificationController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Vendor\VendorPurchaseController as VendorPortalPurchaseController;

/*
|--------------------------------------------------------------------------
| Public customer storefront
|--------------------------------------------------------------------------
*/

Route::get('/', [StorefrontController::class, 'index'])
    ->name('storefront.index');

Route::get('/shop/{listing}', [StorefrontController::class, 'show'])
    ->name('storefront.show');

    Route::get('/shop/{listing}/image', [
    StorefrontController::class,
    'image',
])->name('storefront.image');


/*
|--------------------------------------------------------------------------
| Customer authentication
|--------------------------------------------------------------------------
*/

Route::prefix('customer')->name('customer.')->group(function () {

    Route::middleware('guest')->group(function () {

        Route::get('/login', [CustomerAuthController::class, 'showLogin'])
            ->name('login');

        Route::post('/otp/send', [CustomerAuthController::class, 'sendOtp'])
            ->middleware('throttle:5,1')
            ->name('otp.send');

        Route::post('/otp/verify', [CustomerAuthController::class, 'verifyOtp'])
            ->middleware('throttle:10,1')
            ->name('otp.verify');
    });

    Route::middleware('auth')->group(function () {

        Route::get('/account', [CustomerAuthController::class, 'account'])
            ->name('account');

        Route::post('/logout', [CustomerAuthController::class, 'logout'])
            ->name('logout');
    });
});


/*
|--------------------------------------------------------------------------
| Public admin login routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.submit');
});


/*
|--------------------------------------------------------------------------
| Protected admin routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');


        // Vendor management
        Route::get('/vendors', [VendorManagementController::class, 'index'])
            ->name('vendors.index');

        Route::get('/vendors/{vendor}', [VendorManagementController::class, 'show'])
            ->name('vendors.show');

        Route::get('/vendors/{vendor}/documents/{document}/download', [
            VendorManagementController::class,
            'downloadDocument'
        ])->name('vendors.documents.download');

        Route::patch('/vendors/{vendor}/status', [
            VendorManagementController::class,
            'updateStatus'
        ])->name('vendors.status.update');


        // Product management
        Route::get('/products', [ProductManagementController::class, 'index'])
            ->name('products.index');

        Route::get('/products/{product}', [ProductManagementController::class, 'show'])
            ->name('products.show');

        Route::patch('/products/{product}/status', [
            ProductManagementController::class,
            'updateStatus'
        ])->name('products.status.update');

        Route::get('/products/{product}/image', [
            ProductManagementController::class,
            'previewImage',
        ])->name('products.image');


        // Vendor marketplace
        Route::get('/marketplace', [VendorMarketplaceController::class, 'index'])
            ->name('marketplace.index');


        // Vendor procurement
        Route::get('/purchases', [VendorPurchaseController::class, 'index'])
            ->name('purchases.index');

        Route::get('/purchases/create', [VendorPurchaseController::class, 'create'])
            ->name('purchases.create');

        Route::post('/purchases', [VendorPurchaseController::class, 'store'])
            ->name('purchases.store');

        Route::get('/purchases/{purchase}', [VendorPurchaseController::class, 'show'])
            ->name('purchases.show');

        Route::patch('/purchases/{purchase}/status', [
            VendorPurchaseController::class,
            'updateStatus'
        ])->name('purchases.status.update');


        // Goods receipts
        Route::get('/purchases/{purchase}/receive', [
            GoodsReceiptController::class,
            'create'
        ])->name('purchases.receive');

        Route::post('/purchases/{purchase}/receive', [
            GoodsReceiptController::class,
            'store'
        ])->name('purchases.receive.store');


        // Inventory
        Route::get('/inventory', [InventoryController::class, 'index'])
            ->name('inventory.index');

        Route::get('/inventory/{product}', [InventoryController::class, 'show'])
            ->name('inventory.show');

        Route::post('/inventory/batches/{batch}/adjustments', [
            InventoryAdjustmentController::class,
            'store'
        ])->name('inventory.adjustments.store');


        // Admin customer catalog
        Route::get('/catalog', [CustomerCatalogController::class, 'index'])
            ->name('catalog.index');

        Route::get('/catalog/create', [CustomerCatalogController::class, 'create'])
            ->name('catalog.create');

        Route::post('/catalog', [CustomerCatalogController::class, 'store'])
            ->name('catalog.store');

        Route::get('/catalog/{listing}/edit', [CustomerCatalogController::class, 'edit'])
            ->name('catalog.edit');

        Route::put('/catalog/{listing}', [CustomerCatalogController::class, 'update'])
            ->name('catalog.update');

        Route::patch('/catalog/{listing}/status', [
            CustomerCatalogController::class,
            'toggleStatus'
        ])->name('catalog.status');


        // Product category management
        Route::get('/categories', [ProductCategoryController::class, 'index'])
            ->name('categories.index');

        Route::get('/categories/create', [ProductCategoryController::class, 'create'])
            ->name('categories.create');

        Route::post('/categories', [ProductCategoryController::class, 'store'])
            ->name('categories.store');

        Route::get('/categories/{category}/edit', [ProductCategoryController::class, 'edit'])
            ->name('categories.edit');

        Route::put('/categories/{category}', [ProductCategoryController::class, 'update'])
            ->name('categories.update');

        Route::patch('/categories/{category}/status', [
            ProductCategoryController::class,
            'toggleStatus',
        ])->name('categories.status');

        Route::get('/catalog/{listing}/image', [
            \App\Http\Controllers\Admin\CustomerCatalogController::class,
            'showImage',
        ])->name('catalog.image');
        
});

Route::get(
    '/shop/products/{product}/image',
    [StorefrontProductImageController::class, 'show']
)->name('storefront.products.image');


/*
|--------------------------------------------------------------------------
| Public vendor authentication routes
|--------------------------------------------------------------------------
*/

Route::prefix('vendor')->name('vendor.')->middleware('guest')->group(function () {

    Route::get('/register', [VendorController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [VendorController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.submit');

    Route::get('/login', [VendorController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [VendorController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');
});


/*
|--------------------------------------------------------------------------
| Protected vendor routes
|--------------------------------------------------------------------------
*/

Route::prefix('vendor')
    ->name('vendor.')
    ->middleware(['auth', 'role:vendor'])
    ->group(function () {

        Route::get('/dashboard', [VendorController::class, 'dashboard'])
            ->name('dashboard');

        Route::post('/logout', [VendorController::class, 'logout'])
            ->name('logout');

        Route::resource('products', ProductController::class)
            ->except(['show'])
            ->names('products');
        
        Route::get('/purchases', [VendorPortalPurchaseController::class, 'index'])
            ->name('purchases.index');

        Route::get('/purchases/{purchase}', [VendorPortalPurchaseController::class, 'show'])
            ->name('purchases.show');

        Route::get('/notifications', [VendorNotificationController::class, 'index'])
            ->name('notifications.index');

        Route::patch('/notifications/read-all', [VendorNotificationController::class, 'markAllAsRead'])
            ->name('notifications.read-all');

        Route::patch('/notifications/{notification}/read', [VendorNotificationController::class, 'markAsRead'])
            ->name('notifications.read');
});
