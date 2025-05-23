<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Dashboards\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Order\DueOrderController;
use App\Http\Controllers\Order\OrderCompleteController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\OrderPendingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentExportController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductExportController;
use App\Http\Controllers\Product\ProductImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressItemController;
use App\Http\Controllers\Purchase\PurchaseController;
use App\Http\Controllers\Quotation\QuotationController;
use App\Http\Controllers\RepairTicketController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('dashboard/', [DashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('/users', UserController::class); // ->except(['show']);
    Route::resource('/roles', RoleController::class);

    Route::put('/user/change-password/{username}', [UserController::class, 'updatePassword'])->name('users.updatePassword');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings');
    Route::get('/profile/store-settings', [ProfileController::class, 'store_settings'])->name('profile.store.settings');
    Route::post('/profile/store-settings', [ProfileController::class, 'store_settings_store'])->name('profile.store.settings.store');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('/quotations', QuotationController::class);
    Route::resource('/customers', CustomerController::class);
    Route::resource('/suppliers', SupplierController::class);
    Route::resource('/categories', CategoryController::class);
    Route::resource('/warehouses', WarehouseController::class);
    Route::resource('/drivers', DriverController::class);
    Route::resource('/units', UnitController::class);

    // Route Products
    Route::get('products/import/', [ProductImportController::class, 'create'])->name('products.import.view');
    Route::post('products/import/', [ProductImportController::class, 'store'])->name('products.import.store');
    Route::get('products/export/', [ProductExportController::class, 'create'])->name('products.export.store');
    Route::resource('/products', ProductController::class);

    Route::post('invoice/create/', [InvoiceController::class, 'create'])->name('invoice.create');

    // Route Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/pending', OrderPendingController::class)->name('orders.pending');
    Route::get('/orders/complete', OrderCompleteController::class)->name('orders.complete');

    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');

    // SHOW ORDER
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/order-details/{order}', [OrderController::class, 'show'])->name('orderDetails.show');
    Route::post('/orders/{order}/update_items', [OrderController::class, 'updateItems'])->name('orders.update_items');
    Route::put('/orders/update/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::get('/orders/update_status/{order}/{order_status}', [OrderController::class, 'updateStatus']);
    Route::post('/orders/update_status/{order}/{order_status}', [OrderController::class, 'updateStatus']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

    // DUES
    Route::get('due/orders/', [DueOrderController::class, 'index'])->name('due.index');
    Route::get('due/order/view/{order}', [DueOrderController::class, 'show'])->name('due.show');
    Route::get('due/order/edit/{order}', [DueOrderController::class, 'edit'])->name('due.edit');
    Route::put('due/order/update/{order}', [DueOrderController::class, 'update'])->name('due.update');

    // TODO: Remove from OrderController
    Route::get('/orders/details/{order_id}/download', [OrderController::class, 'downloadInvoice'])->name('order.downloadInvoice');
    Route::post('/orders/details/download', [OrderController::class, 'bulkDownloadInvoice'])->name('order.bulk.download');

    // Route Purchases
    Route::get('/purchases/approved', [PurchaseController::class, 'approvedPurchases'])->name('purchases.approvedPurchases');
    Route::get('/purchases/report', [PurchaseController::class, 'dailyPurchaseReport'])->name('purchases.dailyPurchaseReport');
    Route::get('/purchases/report/export', [PurchaseController::class, 'getPurchaseReport'])->name('purchases.getPurchaseReport');
    Route::post('/purchases/report/export', [PurchaseController::class, 'exportPurchaseReport'])->name('purchases.exportPurchaseReport');

    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');

    // Route::get('/purchases/show/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');

    // Route::get('/purchases/edit/{purchase}', [PurchaseController::class, 'edit'])->name('purchases.edit');
    Route::get('/purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');

    Route::put('/purchases/update/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');
    Route::delete('/purchases/delete/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.delete');

    Route::get('/activity-logs', [UserController::class, 'activityLogs'])->name('activity-logs');

    Route::resource('repair-tickets', RepairTicketController::class);
    Route::put('repair-tickets/{repairTicket}/update-status', [RepairTicketController::class, 'updateStatus'])->name('repair-tickets.update-status');
    Route::put('repair-tickets/{repairTicket}/process-return', [RepairTicketController::class, 'processReturn'])->name('repair-tickets.process-return');
    Route::post('repair-tickets/{repairTicket}/upload-photos', [RepairTicketController::class, 'uploadPhotos'])->name('repair-tickets.upload-photos');
    Route::delete('repair-photos/{photoId}', [RepairTicketController::class, 'deletePhoto'])->name('repair-tickets.delete-photo');

    // Payment Export Route
    Route::get('payments/export', [PaymentExportController::class, 'create'])->name('payments.export');

    // Payment Routes
    Route::get('payments/{customer}/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments/{customer}', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('payments/{payment}/report', [PaymentController::class, 'report'])->name('payments.report');
    Route::post('payments/{payment}/cash-in', [PaymentController::class, 'cash_in'])->name('payments.cash_in');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    Route::get('/lang/{lang}', function ($lang) {
        // Validate the language
        $supportedLocales = ['en', 'fr'];
        if (! in_array($lang, $supportedLocales)) {
            abort(400, 'Unsupported language');
        }

        // Set the locale
        App::setLocale($lang);

        // Store the locale in the session
        session(['locale' => $lang]);

        return redirect()->back();
    })->name('lang.switch');
    
    // Cart Web Routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [\App\Http\Controllers\CartController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\CartController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\CartController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\CartController::class, 'destroy']);
        Route::delete('/', [\App\Http\Controllers\CartController::class, 'clear']);
    });

    // Documentation Routes
    Route::get('documentation/toast', function () {
        return view('documentation.toast');
    })->name('documentation.toast');
    
    // Progress Item Management Routes
    Route::resource('progress-items', ProgressItemController::class);
    Route::post('progress-items/{progressItem}/payment', [ProgressItemController::class, 'recordPayment'])->name('progress-items.payment');
});

require __DIR__.'/auth.php';

Route::get('test/', function () {
    return view('test');
});
