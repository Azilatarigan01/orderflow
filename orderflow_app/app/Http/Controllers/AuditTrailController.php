<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    /**
     * Audit Trail Index — Admin & Auditor only
     */
    public function index(Request $request)
    {
        $query = AuditTrail::with('user')->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('entity_label')) {
            $query->where('entity_label', 'like', '%' . $request->entity_label . '%');
        }

        $trails         = $query->paginate(50)->withQueryString();
        $users          = User::orderBy('name')->get();
        $entityTypes    = AuditTrail::select('entity_type')->distinct()->pluck('entity_type');
        $actions        = AuditTrail::select('action')->distinct()->pluck('action');
        $integrityCheck = \App\Services\AuditTrailService::verifyChainIntegrity();

        return view('audit_trail.index', compact('trails', 'users', 'entityTypes', 'actions', 'integrityCheck'));
    }
}
