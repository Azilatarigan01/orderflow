<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VendorController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/switch-role/{role}', [DashboardController::class, 'switchRole'])->name('switch-role');

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
