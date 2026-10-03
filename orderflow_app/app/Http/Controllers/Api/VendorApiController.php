<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorApiController extends BaseApiController
{
    /**
     * Get vendor directory with category filter, search, and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Vendor::query();

        // Filter active status (default to active only unless specified)
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        } else {
            $query->where('is_active', true);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginated = $query->orderBy('name')->paginate($perPage);

        $categories = Vendor::select('category')->distinct()->pluck('category');

        $data = [
            'items' => VendorResource::collection($paginated->items()),
            'categories' => $categories,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
                'has_more'     => $paginated->hasMorePages(),
            ],
        ];

        return $this->sendResponse($data, 'Direktori rekanan vendor berhasil diambil');
    }
}
