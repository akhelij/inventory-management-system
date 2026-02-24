<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $products = Product::query()
            ->with(['category', 'warehouse', 'unit'])
            ->where('quantity', '>', 0)
            ->whereNull('deleted_at')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('warehouse', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            }))
            ->orderBy(
                $request->input('sort_field', 'id'),
                $request->input('sort_direction', 'desc')
            )
            ->paginate($request->input('per_page', 15));

        return $products;
    }
}
