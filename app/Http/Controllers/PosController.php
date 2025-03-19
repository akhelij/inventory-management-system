<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function index(Request $request)
    {
        // Restore the cart from the database for the current user
        $this->restoreCart();

        $carts = auth()->check() ? auth()->user()->getCart() : collect();
        $products = Product::with(['category', 'unit'])->get();
        $customers = Customer::all()->sortBy('name');
        $warehouses = Warehouse::latest()->get();

        return view('pos.index', [
            'products' => $products,
            'customers' => $customers,
            'carts' => $carts,
            'warehouses' => $warehouses,
        ]);
    }

    public function addCartItem(Request $request)
    {
        $request->all();

        $rules = [
            'id' => 'required|numeric',
            'name' => 'required|string',
            'selling_price' => 'required|numeric',
        ];

        $validatedData = $request->validate($rules);

        Cart::add($validatedData['id'],
            $validatedData['name'],
            1,
            $validatedData['selling_price'],
            1,
            (array) $options = null);

        // Store the cart in the database
        $this->storeCart();

        return redirect()
            ->back()
            ->with('success', 'Product has been added to cart!');
    }

    public function updateCartItem(Request $request, $rowId)
    {
        $rules = [
            'quantity' => 'required|numeric',
        ];

        $validatedData = $request->validate($rules);

        Cart::update($rowId, $validatedData['quantity']);

        // Store the cart in the database
        $this->storeCart();

        return redirect()
            ->back()
            ->with('success', 'Product has been updated from cart!');
    }

    public function deleteCartItem(string $rowId)
    {
        Cart::remove($rowId);

        // Store the cart in the database
        $this->storeCart();

        return redirect()
            ->back()
            ->with('success', 'Product has been deleted from cart!');
    }

    /**
     * Store the current cart data to the database.
     */
    private function storeCart()
    {
        if (Auth::check()) {
            try {
                // Delete existing cart before storing the new one
                Cart::erase(Auth::id());
                Cart::store(Auth::id());
            } catch (\Exception $e) {
                // Log error or handle silently
            }
        }
    }

    /**
     * Restore the cart data from the database.
     */
    private function restoreCart()
    {
        if (Auth::check()) {
            try {
                // Clear the current cart first to avoid duplicates
                Cart::destroy();
                
                // Restore the cart for the current user
                Cart::restore(Auth::id());
            } catch (\Exception $e) {
                // If there's no cart to restore, just continue silently
            }
        }
    }
}
