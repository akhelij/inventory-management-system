<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');
        $search = $request->input('search', '');
        
        $products = Product::query()
            ->with(['category', 'warehouse', 'unit'])
            ->where('quantity', '>', 0)
            ->whereNull('deleted_at');
        
        if ($search) {
            $products->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhereHas('category', function ($query) use ($search) {
                          $query->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('warehouse', function($query) use ($search) {
                          $query->where('name', 'like', "%{$search}%");
                      });
            });
        }
        
        return $products->orderBy($sortField, $sortDirection)
            ->paginate($perPage);
    }
} 