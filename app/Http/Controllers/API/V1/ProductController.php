<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->whereNull('deleted_at')
            ->when($request->has('category_id'), fn ($query) => $query->where('category_id', $request->get('category_id')))
            ->get();

        return response()->json($products);
    }
}
