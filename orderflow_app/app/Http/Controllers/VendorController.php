<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Vendor::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $vendors = $query->latest()->paginate(10)->withQueryString();
        $categories = Vendor::select('category')->distinct()->pluck('category');

        return view('vendors.index', compact('vendors', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('vendors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:vendors,code',
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:100',
            'contact_person' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:25',
            'address' => 'required|string',
            'tax_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:50',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:150',
            'rating' => 'nullable|numeric|min:1|max:5',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['rating'] = $validated['rating'] ?? 5.00;

        Vendor::create($validated);

        return redirect()->route('vendors.index')->with('success', "Vendor {$validated['name']} berhasil ditambahkan.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor)
    {
        return view('vendors.show', compact('vendor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', compact('vendor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:vendors,code,' . $vendor->id,
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:100',
            'contact_person' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:25',
            'address' => 'required|string',
            'tax_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:50',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:150',
            'rating' => 'nullable|numeric|min:1|max:5',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $vendor->update($validated);

        return redirect()->route('vendors.index')->with('success', "Data vendor {$vendor->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        return redirect()->route('vendors.index')->with('success', "Vendor {$vendor->name} berhasil dihapus.");
    }
}
