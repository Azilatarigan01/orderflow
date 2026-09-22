<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\VendorController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/switch-role/{role}', [DashboardController::class, 'switchRole'])->name('switch-role');

    // Approval Workflow Queue & Actions (Manager, Finance, Admin)
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('/approvals/{purchase_request}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{purchase_request}/revision', [ApprovalController::class, 'requestRevision'])->name('approvals.revision');
    Route::post('/approvals/{purchase_request}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');

    // In-App Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Purchase Requests (PR)
    Route::resource('purchase-requests', PurchaseRequestController::class);
    Route::post('/purchase-requests/{purchase_request}/submit', [PurchaseRequestController::class, 'submit'])->name('purchase-requests.submit');

    // Vendor Management (Viewable by authenticated staff)
    Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
    Route::get('/vendors/{vendor}', [VendorController::class, 'show'])->whereNumber('vendor')->name('vendors.show');

    // Vendor Creation / Modification (Restricted to Procurement and Admin)
    Route::middleware('role:procurement,admin')->group(function () {
        Route::get('/vendors/create', [VendorController::class, 'create'])->name('vendors.create');
        Route::post('/vendors', [VendorController::class, 'store'])->name('vendors.store');
        Route::get('/vendors/{vendor}/edit', [VendorController::class, 'edit'])->whereNumber('vendor')->name('vendors.edit');
        Route::put('/vendors/{vendor}', [VendorController::class, 'update'])->whereNumber('vendor')->name('vendors.update');
        Route::delete('/vendors/{vendor}', [VendorController::class, 'destroy'])->whereNumber('vendor')->name('vendors.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
