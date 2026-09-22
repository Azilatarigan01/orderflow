<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // General stats
        $totalVendors = Vendor::count();
        $activeVendors = Vendor::where('is_active', true)->count();
        $totalDepartments = Department::count();
        $totalUsers = User::count();

        // Recent vendors
        $recentVendors = Vendor::latest()->take(5)->get();

        return view('dashboard', compact(
            'user',
            'totalVendors',
            'activeVendors',
            'totalDepartments',
            'totalUsers',
            'recentVendors'
        ));
    }

    /**
     * Demo role switch for recruiter / evaluation preview
     */
    public function switchRole(Request $request, string $role)
    {
        if (! in_array($role, ['admin', 'manager', 'procurement', 'finance', 'requester', 'auditor'])) {
            return back()->with('error', 'Role tidak valid.');
        }

        $demoUser = User::where('role', $role)->first();
        if ($demoUser) {
            auth()->login($demoUser);
            return redirect()->route('dashboard')->with('success', "Berhasil beralih ke peran demo: {$demoUser->role_label} ({$demoUser->email})");
        }

        return back()->with('error', 'Akun demo untuk peran ini belum tersedia.');
    }
}
