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
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Picqer\Barcode\BarcodeGeneratorHTML;

class ProductController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_PRODUCTS), 403);
        $products = Product::count();

        return view('products.index', [
            'products' => $products,
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_PRODUCTS), 403);
        $categories = Category::get(['id', 'name']);
        $units = Unit::get(['id', 'name']);

        if ($request->has('category')) {
            $categories = Category::whereSlug($request->get('category'))->get();
        }

        if ($request->has('unit')) {
            $units = Unit::whereSlug($request->get('unit'))->get();
        }

        return view('products.create', [
            'categories' => $categories,
            'warehouses' => Warehouse::all(),
            'units' => $units,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_PRODUCTS), 403);
        /**
         * Handle upload image
         */
        $image = '';
        if ($request->hasFile('product_image')) {
            $image = $request->file('product_image')->store('products', 'public');
        }

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

    /**
     * Generate unique random product code between 5 to 12 characters
     */
    private function generateProductCode(): string
    {
        do {
            // Generate random length between 5 and 12
            $length = rand(5, 12);

            // Define characters that can be used in the code
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

            // Generate random string
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }

            // Check if the code already exists
            $exists = Product::where('code', $code)->exists();
        } while ($exists);

        return $code;
    }

    public function show($uuid)
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_PRODUCTS), 403);
        $product = Product::where('uuid', $uuid)->firstOrFail();

        $product_entries = $product->activities()
            ->where('event', 'updated')
            ->whereRaw("JSON_EXTRACT(properties, '$.attributes.quantity') IS NOT NULL")
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($activity) {
                $properties = json_decode($activity->properties);
                $oldQuantity = $properties->old->quantity ?? null;
                $newQuantity = $properties->attributes->quantity;
                $difference = $oldQuantity !== null ? $newQuantity - $oldQuantity : 0;

                return [
                    'date' => $activity->created_at->format('Y-m-d H:i:s'),
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'difference' => $difference,
                    'user' => $activity->causer->name ?? 'Unknown',
                ];
            });

        // Generate a barcode
        $generator = new BarcodeGeneratorHTML;
        $barcode = $generator->getBarcode($product->code, $generator::TYPE_CODE_128);

        return view('products.show', [
            'product' => $product,
            'product_entries' => $product_entries,
            'barcode' => $barcode,
        ]);
    }

    public function edit($uuid)
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_PRODUCTS), 403);
        $product = Product::where('uuid', $uuid)->firstOrFail();

        return view('products.edit', [
            'categories' => Category::get(),
            'warehouses' => Warehouse::all(),
            'units' => Unit::get(),
            'product' => $product,
        ]);
    }

    public function update(UpdateProductRequest $request, $uuid)
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_PRODUCTS), 403);
        $product = Product::where('uuid', $uuid)->firstOrFail();

        $image = $product->product_image;
        if ($request->hasFile('product_image')) {

            // Delete Old Photo
            if ($product->product_image) {
                unlink(public_path('storage/').$product->product_image);
            }
            $image = $request->file('product_image')->store('products', 'public');
        }

        $product->update(array_merge($request->except('product_image'), [
            'slug' => Str::slug($request->name, '-'),
            'product_image' => $image,
        ]));

        return redirect()
            ->route('products.index')
            ->with('success', 'Product has been updated!');
    }

    public function destroy($uuid)
    {
        abort_unless(auth()->user()->can(PermissionEnum::DELETE_PRODUCTS), 403);
        $product = Product::where('uuid', $uuid)->firstOrFail();
        /**
         * Delete photo if exists.
         */
        if ($product->product_image) {
            // check if image exists in our file system
            if (file_exists(public_path('storage/').$product->product_image)) {
                unlink(public_path('storage/').$product->product_image);
            }
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product has been deleted!');
    }
}
