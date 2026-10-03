<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\PurchaseRequestApiController;
use App\Http\Controllers\Api\VendorApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — OrderFlow RESTful API (Laravel Sanctum Protected)
|--------------------------------------------------------------------------
*/

// Public Authentication Endpoint
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Protected Endpoints (Requires valid Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Dashboard KPI Summary
    Route::get('/dashboard/summary', [DashboardApiController::class, 'summary'])->name('api.dashboard.summary');
    Route::get('/management/dashboard-data', [DashboardApiController::class, 'managementDashboardData'])->name('api.management.dashboard-data');

    // Purchase Requests CRUD & Approvals
    Route::get('/purchase-requests', [PurchaseRequestApiController::class, 'index'])->name('api.purchase-requests.index');
    Route::post('/purchase-requests', [PurchaseRequestApiController::class, 'store'])->name('api.purchase-requests.store');
    Route::get('/purchase-requests/{id}', [PurchaseRequestApiController::class, 'show'])->name('api.purchase-requests.show');
    Route::post('/purchase-requests/{id}/approve', [PurchaseRequestApiController::class, 'approve'])->name('api.purchase-requests.approve');
    Route::post('/purchase-requests/{id}/reject', [PurchaseRequestApiController::class, 'reject'])->name('api.purchase-requests.reject');

    // Vendor Directory
    Route::get('/vendors', [VendorApiController::class, 'index'])->name('api.vendors.index');
});
