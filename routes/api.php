<?php

use App\Http\Controllers\Api\V1\IntegrationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.token', 'throttle:60,1'])->group(function () {
    Route::get('/customers', [IntegrationController::class, 'customers']);
    Route::get('/customers/{id}', [IntegrationController::class, 'customer']);
    Route::get('/customers/{id}/vehicles', [IntegrationController::class, 'vehicles']);
    Route::get('/customers/{id}/service-history', [IntegrationController::class, 'serviceHistory']);
    Route::get('/service-orders', [IntegrationController::class, 'serviceOrders']);
    Route::get('/service-orders/{id}', [IntegrationController::class, 'serviceOrder']);
    Route::post('/service-orders', [IntegrationController::class, 'createServiceOrder']);
    Route::get('/invoices', [IntegrationController::class, 'invoices']);
    Route::get('/invoices/{id}', [IntegrationController::class, 'invoice']);
    Route::get('/spareparts', [IntegrationController::class, 'spareparts']);
    Route::get('/spareparts/{id}/stock', [IntegrationController::class, 'sparepartStock']);
    Route::get('/reports/revenue', [IntegrationController::class, 'revenue']);
    Route::get('/reports/service-summary', [IntegrationController::class, 'serviceSummary']);
});
