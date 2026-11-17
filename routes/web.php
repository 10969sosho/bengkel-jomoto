<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SaController;
use App\Http\Controllers\TeknisiController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/', fn () => redirect()->route('dashboard'));
Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::middleware('role:sa')->prefix('sa')->name('sa.')->group(function () {
    Route::get('/', [SaController::class, 'index'])->name('index');
    Route::get('/wo/create', [SaController::class, 'create'])->name('create');
    Route::post('/wo', [SaController::class, 'store'])->name('store');
    Route::get('/customers', [SaController::class, 'customers'])->name('customers.index');
    Route::post('/customers', [SaController::class, 'storeCustomer'])->name('customers.store');
    Route::get('/customers/{customer}', [SaController::class, 'showCustomer'])->name('customers.show');
    Route::post('/customers/{customer}/vehicles', [SaController::class, 'storeVehicle'])->name('vehicles.store');
    Route::put('/vehicles/{vehicle}', [SaController::class, 'updateVehicle'])->name('vehicles.update');
    Route::get('/payments', [SaController::class, 'payments'])->name('payments.index');
    Route::post('/payments/{invoice}/pay', [InvoiceController::class, 'pay'])->name('payments.pay');
    Route::get('/wo/{serviceOrder}', [SaController::class, 'show'])->name('show');
    Route::post('/wo/{serviceOrder}/assign', [SaController::class, 'assign'])->name('assign');
    Route::post('/wo/{serviceOrder}/estimate', [SaController::class, 'estimate'])->name('estimate');
    Route::post('/wo/{serviceOrder}/approve', [SaController::class, 'approve'])->name('approve');
    Route::post('/wo/{serviceOrder}/final-check', [SaController::class, 'finalCheck'])->name('finalCheck');
    Route::post('/wo/{serviceOrder}/confirm', [SaController::class, 'confirmResult'])->name('confirm');
    Route::get('/bookings/{booking}', [SaController::class, 'showBooking'])->name('bookings.show');
    Route::get('/checkin', [SaController::class, 'createCheckin'])->name('checkin.create');
    Route::post('/checkin', [SaController::class, 'storeCheckin'])->name('checkin.store');
    Route::get('/wo/{serviceOrder}/print-checkin', [SaController::class, 'printCheckin'])->name('print.checkin');
    Route::post('/wo/{serviceOrder}/spk', [SaController::class, 'issueSpk'])->name('spk.issue');
    Route::get('/wo/{serviceOrder}/print-spk', [SaController::class, 'printSpk'])->name('print.spk');
    Route::post('/wo/{serviceOrder}/invoice', [SaController::class, 'issueInvoice'])->name('invoice');
    Route::get('/wo/{serviceOrder}/print-estimate', [SaController::class, 'printEstimate'])->name('print.estimate');
    Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
});

Route::middleware('role:teknisi')->prefix('teknisi')->name('teknisi.')->group(function () {
    Route::get('/', [TeknisiController::class, 'index'])->name('index');
    Route::get('/wo/{serviceOrder}/inspect', [TeknisiController::class, 'inspect'])->name('inspect');
    Route::post('/wo/{serviceOrder}/inspect', [TeknisiController::class, 'saveInspection'])->name('inspect.save');
    Route::post('/wo/{serviceOrder}/confirm-inspection', [TeknisiController::class, 'confirmInspection'])->name('confirmInspection');
    Route::post('/wo/{serviceOrder}/timer', [TeknisiController::class, 'timer'])->name('timer');
    Route::post('/wo/{serviceOrder}/complete', [TeknisiController::class, 'complete'])->name('complete');
});

Route::middleware('role:customer')->prefix('customer')->name('customer.')->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->name('index');
    Route::get('/vehicles', [CustomerController::class, 'vehicles'])->name('vehicles');
    Route::get('/booking/create', [CustomerController::class, 'createBooking'])->name('booking.create');
    Route::post('/vehicles', [CustomerController::class, 'storeVehicle'])->name('vehicles.store');
    Route::post('/service-orders', [CustomerController::class, 'storeOrder'])->name('orders.store');
    Route::post('/service-orders/{serviceOrder}/approve', [CustomerController::class, 'approveEstimate'])->name('orders.approve');
    Route::post('/service-orders/{serviceOrder}/confirm', [CustomerController::class, 'confirmResult'])->name('orders.confirm');
});

Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/pricelist', [AdminController::class, 'pricelist'])->name('pricelist');
    Route::get('/sub-materials', [AdminController::class, 'subMaterials'])->name('subMaterials');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
});

Route::get('/api-docs', fn () => view('api-docs'))
    ->middleware('role:admin,sa')->name('api-docs');

Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])
    ->middleware('role:admin,sa,customer')->name('invoices.print');
