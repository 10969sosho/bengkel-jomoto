<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SaController;
use App\Http\Controllers\TeknisiController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));
Route::get('/dashboard', DashboardController::class)->name('dashboard');
Route::post('/demo/switch', [DemoController::class, 'switch'])->name('demo.switch');

Route::middleware('role:sa')->prefix('sa')->name('sa.')->group(function () {
    Route::get('/', [SaController::class, 'index'])->name('index');
    Route::get('/wo/create', [SaController::class, 'create'])->name('create');
    Route::post('/wo', [SaController::class, 'store'])->name('store');
    Route::post('/customers/{customer}/vehicles', [SaController::class, 'storeVehicle'])->name('vehicles.store');
    Route::get('/wo/{serviceOrder}', [SaController::class, 'show'])->name('show');
    Route::post('/wo/{serviceOrder}/assign', [SaController::class, 'assign'])->name('assign');
    Route::post('/wo/{serviceOrder}/estimate', [SaController::class, 'estimate'])->name('estimate');
    Route::post('/wo/{serviceOrder}/approve', [SaController::class, 'approve'])->name('approve');
    Route::post('/wo/{serviceOrder}/invoice', [SaController::class, 'issueInvoice'])->name('invoice');
    Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
});

Route::middleware('role:teknisi')->prefix('teknisi')->name('teknisi.')->group(function () {
    Route::get('/', [TeknisiController::class, 'index'])->name('index');
    Route::get('/wo/{serviceOrder}/inspect', [TeknisiController::class, 'inspect'])->name('inspect');
    Route::post('/wo/{serviceOrder}/inspect', [TeknisiController::class, 'saveInspection'])->name('inspect.save');
    Route::post('/wo/{serviceOrder}/timer', [TeknisiController::class, 'timer'])->name('timer');
    Route::post('/wo/{serviceOrder}/complete', [TeknisiController::class, 'complete'])->name('complete');
});

Route::middleware('role:customer')->prefix('customer')->name('customer.')->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->name('index');
    Route::post('/vehicles', [CustomerController::class, 'storeVehicle'])->name('vehicles.store');
    Route::post('/service-orders', [CustomerController::class, 'storeOrder'])->name('orders.store');
});

Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
});

Route::get('/api-docs', fn () => view('api-docs'))
    ->middleware('role:admin,sa')->name('api-docs');

Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
