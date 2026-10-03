<?php

namespace App\Http\Controllers;

use App\Models\ActingDelegation;
use App\Models\User;
use App\Services\AuditTrailService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DelegationController extends Controller
{
    /**
     * Display list of delegations
     */
    public function index()
    {
        $user = auth()->user();

        // Admin can view all, others view delegations given or received
        $query = ActingDelegation::with(['delegator.department', 'delegatee.department'])->latest();

        if (!$user->hasRole('admin')) {
            $query->where(function ($q) use ($user) {
                $q->where('delegator_user_id', $user->id)
                  ->orWhere('delegatee_user_id', $user->id);
            });
        }

        $delegations = $query->paginate(15);
        $myActiveDelegation = $user->actingDelegationsGiven()->activeNow()->first();

        return view('delegations.index', compact('delegations', 'myActiveDelegation'));
    }

    /**
     * Show form to create new Plt delegation
     */
    public function create()
    {
        $user = auth()->user();

        // Allowed users to delegate to: active employees in company (excluding self)
        $candidates = User::where('id', '!=', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('delegations.create', compact('candidates'));
    }

    /**
     * Store new Plt delegation
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'delegatee_user_id' => [
                'required',
                'exists:users,id',
                'different:delegator_id',
                function ($attribute, $value, $fail) use ($user) {
                    if ((int) $value === $user->id) {
                        $fail('Anda tidak dapat mendelegasikan wewenang kepada diri sendiri.');
                    }
                },
            ],
            'role_delegated' => 'required|string|in:manager,finance,hod',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'reason'         => 'required|string|max:255',
        ], [
            'delegatee_user_id.required' => 'Pilih pegawai yang ditunjuk sebagai Pelaksana Tugas (Plt).',
            'end_date.after_or_equal'    => 'Tanggal berakhir harus sama atau setelah tanggal mulai delegasi.',
            'reason.required'            => 'Alasan pelimpahan wewenang Plt wajib diisi (misal: Cuti Tahunan / Dinas Luar Kota).',
        ]);

        $delegatee = User::findOrFail($request->delegatee_user_id);

        $delegation = ActingDelegation::create([
            'delegator_user_id' => $user->id,
            'delegatee_user_id' => $delegatee->id,
            'role_delegated'    => $request->role_delegated,
            'start_date'        => $request->start_date,
            'end_date'          => $request->end_date,
            'reason'            => $request->reason,
            'is_active'         => true,
        ]);

        // Audit Trail
        AuditTrailService::record(
            'delegation_created',
            $delegation,
            "PLT-{$delegation->id}",
            beforeState: null,
            afterState: [
                'delegator' => $user->name,
                'delegatee' => $delegatee->name,
                'role'      => $request->role_delegated,
                'period'    => "{$request->start_date} s/d {$request->end_date}",
            ],
            description: "Wewenang {$request->role_delegated} didelegasikan oleh {$user->name} kepada {$delegatee->name} (Plt)."
        );

        // Multi-Channel Notification to delegatee
        NotificationService::send(
            $delegatee,
            "Penugasan Pelaksana Tugas (Plt): {$user->name}",
            "Anda telah ditunjuk oleh {$user->name} sebagai Plt {$request->role_delegated} periode " . Carbon::parse($request->start_date)->format('d/m/Y') . " s/d " . Carbon::parse($request->end_date)->format('d/m/Y') . ". Alasan: {$request->reason}",
            route('approvals.index'),
            'delegation_assigned'
        );

        return redirect()->route('delegations.index')
            ->with('success', "Pelimpahan wewenang Plt kepada {$delegatee->name} berhasil diaktifkan.");
    }

    /**
     * Toggle active status of a delegation
     */
    public function toggleActive(ActingDelegation $delegation)
    {
        $user = auth()->user();

        if (!$user->hasRole('admin') && $delegation->delegator_user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk mengubah status delegasi ini.');
        }

        $delegation->is_active = !$delegation->is_active;
        $delegation->save();

        $statusText = $delegation->is_active ? 'diaktifkan kembali' : 'dinonaktifkan / dicabut';

        AuditTrailService::record(
            'delegation_toggled',
            $delegation,
            "PLT-{$delegation->id}",
            beforeState: ['is_active' => !$delegation->is_active],
            afterState: ['is_active' => $delegation->is_active],
            description: "Wewenang Plt {$delegation->role_delegated} {$statusText} oleh {$user->name}."
        );

        return back()->with('success', "Wewenang Plt telah berhasil {$statusText}.");
    }

    /**
     * Delete delegation
     */
    public function destroy(ActingDelegation $delegation)
    {
        $user = auth()->user();

        if (!$user->hasRole('admin') && $delegation->delegator_user_id !== $user->id) {
            abort(403);
        }

        $delegation->delete();

        return redirect()->route('delegations.index')
            ->with('success', 'Catatan delegasi Plt berhasil dihapus.');
    }
}
