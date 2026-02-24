<?php

namespace App\Http\Controllers\Product;

use App\Enums\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Picqer\Barcode\BarcodeGeneratorHTML;

class ProductController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_PRODUCTS), 403);

        return view('products.index', [
            'products' => Product::count(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_PRODUCTS), 403);

        $categories = $request->has('category')
            ? Category::whereSlug($request->get('category'))->get()
            : Category::get(['id', 'name']);

        $units = $request->has('unit')
            ? Unit::whereSlug($request->get('unit'))->get()
            : Unit::get(['id', 'name']);

        return view('products.create', [
            'categories' => $categories,
            'warehouses' => Warehouse::all(),
            'units' => $units,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_PRODUCTS), 403);

        $image = $request->hasFile('product_image')
            ? $request->file('product_image')->store('products', 'public')
            : '';

        Product::create([
            'code' => strtoupper($request->code),
            'product_image' => $image,
            'name' => $request->name,
            'category_id' => $request->category_id,
            'warehouse_id' => $request->warehouse_id,
            'unit_id' => $request->unit_id,
            'quantity' => $request->quantity,
            'buying_price' => $request->buying_price,
            'selling_price' => $request->selling_price,
            'quantity_alert' => $request->quantity_alert,
            'tax' => $request->tax,
            'tax_type' => $request->tax_type,
            'notes' => $request->notes,
            'user_id' => auth()->id(),
            'slug' => Str::slug($request->name, '-'),
            'uuid' => Str::uuid(),
        ]);

        return to_route('products.index')->with('success', 'Product has been created!');
    }

    public function show(string $uuid): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_PRODUCTS), 403);

        $product = Product::where('uuid', $uuid)->firstOrFail();

        $product_entries = $product->activities()
            ->where('event', 'updated')
            ->whereRaw("JSON_EXTRACT(properties, '$.attributes.quantity') IS NOT NULL")
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($activity) {
                $properties = json_decode($activity->properties);
                $oldQuantity = $properties->old->quantity ?? null;
                $newQuantity = $properties->attributes->quantity;

                return [
                    'date' => $activity->created_at->format('Y-m-d H:i:s'),
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'difference' => $oldQuantity !== null ? $newQuantity - $oldQuantity : 0,
                    'user' => $activity->causer->name ?? 'Unknown',
                ];
            });

        $generator = new BarcodeGeneratorHTML;

        return view('products.show', [
            'product' => $product,
            'product_entries' => $product_entries,
            'barcode' => $generator->getBarcode($product->code, $generator::TYPE_CODE_128),
        ]);
    }

    public function edit(string $uuid): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_PRODUCTS), 403);

        return view('products.edit', [
            'categories' => Category::get(),
            'warehouses' => Warehouse::all(),
            'units' => Unit::get(),
            'product' => Product::where('uuid', $uuid)->firstOrFail(),
        ]);
    }

    public function update(UpdateProductRequest $request, string $uuid): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_PRODUCTS), 403);

        $product = Product::where('uuid', $uuid)->firstOrFail();

        $image = $product->product_image;
        if ($request->hasFile('product_image')) {
            if ($product->product_image) {
                unlink(public_path('storage/').$product->product_image);
            }
            $image = $request->file('product_image')->store('products', 'public');
        }

        $product->update(array_merge($request->except('product_image'), [
            'slug' => Str::slug($request->name, '-'),
            'product_image' => $image,
        ]));

        return to_route('products.index')->with('success', 'Product has been updated!');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::DELETE_PRODUCTS), 403);

        Product::where('uuid', $uuid)->firstOrFail()->delete();

        return to_route('products.index')->with('success', 'Product has been deleted!');
    }

    public function trashed(): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_PRODUCTS), 403);

        return view('products.trashed', [
            'trashedProducts' => Product::onlyTrashed()->count(),
        ]);
    }

    public function restore(string $uuid): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_PRODUCTS), 403);

        Product::onlyTrashed()->where('uuid', $uuid)->firstOrFail()->restore();

        return to_route('products.trashed')->with('success', 'Product has been restored!');
    }

    public function forceDelete(string $uuid): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::DELETE_PRODUCTS), 403);

        $product = Product::onlyTrashed()->where('uuid', $uuid)->firstOrFail();

        if ($product->product_image && file_exists(public_path('storage/').$product->product_image)) {
            unlink(public_path('storage/').$product->product_image);
        }

        $product->forceDelete();

        return to_route('products.trashed')->with('success', 'Product has been permanently deleted!');
    }
}
