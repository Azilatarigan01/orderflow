<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\DelegationController;
use App\Http\Controllers\InvoiceController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/session-keepalive', function () {
        return response()->json([
            'status' => 'alive',
            'authenticated' => auth()->check(),
            'csrf_token' => csrf_token(),
            'timestamp' => now()->toIso8601String(),
        ]);
    })->name('session.keepalive');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/management-dashboard', [DashboardController::class, 'reactDashboard'])->name('management.dashboard');
    Route::get('/web-api/management-dashboard-data', [\App\Http\Controllers\Api\DashboardApiController::class, 'managementDashboardData'])->name('web.management.dashboard-data');
    Route::post('/switch-role/{role}', [DashboardController::class, 'switchRole'])->name('switch-role');

    // Approval Workflow Queue & Actions (Manager, Finance, Admin)
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('/approvals/{purchase_request}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{purchase_request}/revision', [ApprovalController::class, 'requestRevision'])->name('approvals.revision');
    Route::post('/approvals/{purchase_request}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    Route::post('/approvals/{approval}/reassign', [ApprovalController::class, 'reassign'])->name('approvals.reassign');

    // Plt Acting Delegations (Pelimpahan Wewenang Pejabat Cuti / Berhalangan)
    Route::get('/delegations', [DelegationController::class, 'index'])->name('delegations.index');
    Route::get('/delegations/create', [DelegationController::class, 'create'])->name('delegations.create');
    Route::post('/delegations', [DelegationController::class, 'store'])->name('delegations.store');
    Route::post('/delegations/{delegation}/toggle', [DelegationController::class, 'toggleActive'])->name('delegations.toggle');
    Route::delete('/delegations/{delegation}', [DelegationController::class, 'destroy'])->name('delegations.destroy');

    // In-App Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Purchase Requests (PR)
    Route::resource('purchase-requests', PurchaseRequestController::class);
    Route::post('/purchase-requests/{purchase_request}/submit', [PurchaseRequestController::class, 'submit'])->name('purchase-requests.submit');
    Route::post('/purchase-requests/{purchase_request}/withdraw', [PurchaseRequestController::class, 'withdraw'])->name('purchase-requests.withdraw');
    Route::post('/purchase-requests/{purchase_request}/cancel', [PurchaseRequestController::class, 'cancel'])->name('purchase-requests.cancel');
    Route::get('/purchase-requests/{purchase_request}/attachment', [PurchaseRequestController::class, 'downloadAttachment'])->name('purchase-requests.attachment');

    // RFQ & Vendor Quotation Matrix (Viewable by Procurement, Admin, Finance, Auditor)
    Route::get('/quotations', [QuotationController::class, 'index'])->name('quotations.index');
    Route::get('/purchase-requests/{purchase_request}/quotations', [QuotationController::class, 'compare'])->name('quotations.compare');
    Route::get('/quotations/{quotation}/attachment', [QuotationController::class, 'downloadAttachment'])->name('quotations.attachment');

    // Quotation Management (Restricted to Procurement and Admin)
    Route::middleware('role:procurement,admin')->group(function () {
        Route::get('/purchase-requests/{purchase_request}/quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
        Route::post('/purchase-requests/{purchase_request}/quotations', [QuotationController::class, 'store'])->name('quotations.store');
        Route::patch('/purchase-requests/{purchase_request}/rfq-deadline', [QuotationController::class, 'updateRfqDeadline'])->name('quotations.rfq-deadline');
        Route::post('/purchase-requests/{purchase_request}/quotations/select', [QuotationController::class, 'selectVendor'])->name('quotations.select');
        Route::post('/purchase-requests/{purchase_request}/fail-tender', [QuotationController::class, 'failTender'])->name('quotations.fail-tender');
        Route::delete('/quotations/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
    });

    // Purchase Orders (PO) & Printable PDF
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::get('/purchase-orders/{purchase_order}/print', [PurchaseOrderController::class, 'printPdf'])->name('purchase-orders.print');

    // Goods Receipts (GR / BAST)
    Route::get('/goods-receipts/{goods_receipt}', [GoodsReceiptController::class, 'show'])->name('goods-receipts.show');
    Route::get('/goods-receipts/{goods_receipt}/delivery-note', [GoodsReceiptController::class, 'downloadDeliveryNote'])->name('goods-receipts.delivery-note');
    Route::get('/goods-receipts/{goods_receipt}/bast', [GoodsReceiptController::class, 'downloadBast'])->name('goods-receipts.bast');

    // PO Operations (Restricted to Procurement and Admin)
    Route::middleware('role:procurement,admin')->group(function () {
        Route::get('/purchase-orders-create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::post('/purchase-orders/{purchase_order}/default-notice', [PurchaseOrderController::class, 'sendDefaultNotice'])->name('purchase-orders.default-notice');
        Route::post('/purchase-orders/{purchase_order}/short-close', [PurchaseOrderController::class, 'shortClose'])->name('purchase-orders.short-close');
        Route::post('/purchase-orders/{purchase_order}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
        Route::delete('/purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');
    });

    // Goods Receipt & BAST Operations (Warehouse, Procurement, Requester, Admin — Segregation of Duties)
    Route::middleware('role:warehouse,procurement,requester,admin')->group(function () {
        Route::get('/goods-receipts-create', [GoodsReceiptController::class, 'create'])->name('goods-receipts.create');
        Route::post('/goods-receipts', [GoodsReceiptController::class, 'store'])->name('goods-receipts.store');
    });

    // Vendor Invoices & 3-Way Matching (Finance, Procurement, Auditor, Admin)
    Route::middleware('role:finance,procurement,auditor,admin')->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices-create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    });

    // Invoice Payment Settlement (Restricted strictly to Finance and Admin)
    Route::middleware('role:finance,admin')->group(function () {
        Route::post('/invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.payment');
    });

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

    // Reports (Restricted to Management, Finance, Procurement, Auditor, Warehouse, and Admin)
    Route::middleware('role:admin,auditor,finance,procurement,manager,hod,warehouse')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/pr', [ReportController::class, 'prReport'])->name('reports.pr');
        Route::get('/reports/po', [ReportController::class, 'poReport'])->name('reports.po');
        Route::get('/reports/gr', [ReportController::class, 'grReport'])->name('reports.gr');
        Route::get('/reports/vendor-spend', [ReportController::class, 'vendorSpend'])->name('reports.vendor-spend');
    });

    // Audit Trail (Admin & Auditor only)
    Route::middleware('role:admin,auditor')->group(function () {
        Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail.index');
    });

    // REST API Docs & Live Postman Test Console (Tahap 7)
    Route::get('/api-docs', function () {
        return view('api-docs');
    })->name('api-docs');
});

require __DIR__.'/auth.php';
