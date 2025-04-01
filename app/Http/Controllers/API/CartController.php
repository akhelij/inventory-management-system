<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Get the current cart items
     */
    public function index(Request $request)
    {
        $cart = $this->getCartFromRequest($request);
        return response()->json($cart);
    }

    /**
     * Add a product to the cart
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:products,id',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'sometimes|numeric|min:1'
        ]);

        $cart = $this->getCartFromRequest($request);
        
        // Generate UUID for the cart item
        $uuid = Str::uuid()->toString();
        
        // Check if product already exists in cart
        $productExists = collect($cart)->contains(function ($item) use ($validated) {
            return $item['product_id'] == $validated['id'];
        });
        
        if ($productExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'This item is already in your cart'
            ], 400);
        }

        // Check available stock
        $product = Product::findOrFail($validated['id']);
        $requestedQty = $validated['quantity'] ?? 1;
        
        if ($requestedQty > $product->quantity) {
            return response()->json([
                'status' => 'error',
                'message' => 'Requested quantity exceeds available stock (' . $product->quantity . ')'
            ], 400);
        }

        // Add new item with UUID
        $newItem = [
            'uuid' => $uuid,
            'product_id' => $product->id,
            'name' => $product->name,
            'qty' => $requestedQty,
            'price' => (float) $validated['price'],
            'basePrice' => (float) $product->selling_price,
            'subtotal' => (float) $validated['price'] * $requestedQty,
            'max_qty' => $product->quantity
        ];

        $cart[] = $newItem;
        
        return $this->responseWithUpdatedCart($cart);
    }

    /**
     * Update quantity or price of cart item
     */
    public function update(Request $request, $uuid)
    {
        $validated = $request->validate([
            'quantity' => 'sometimes|numeric|min:1',
            'price' => 'sometimes|numeric|min:0',
        ]);

        $cart = $this->getCartFromRequest($request);
        
        // Find item by UUID
        $itemIndex = $this->findItemIndexByUuid($cart, $uuid);
        
        if ($itemIndex === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found in cart'
            ], 404);
        }

        // If updating quantity, check stock availability
        if (isset($validated['quantity'])) {
            $productId = $cart[$itemIndex]['product_id'];
            $product = Product::findOrFail($productId);
            
            if ($validated['quantity'] > $product->quantity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Requested quantity exceeds available stock (' . $product->quantity . ')'
                ], 400);
            }
            
            $cart[$itemIndex]['qty'] = (int) $validated['quantity'];
            $cart[$itemIndex]['max_qty'] = $product->quantity;
        }

        // Update price if provided, ensure it's not below base price
        if (isset($validated['price'])) {
            $basePrice = $cart[$itemIndex]['basePrice'];
            $newPrice = max((float) $validated['price'], $basePrice);
            $cart[$itemIndex]['price'] = $newPrice;
        }

        // Recalculate subtotal
        $cart[$itemIndex]['subtotal'] = $cart[$itemIndex]['price'] * $cart[$itemIndex]['qty'];
        
        return $this->responseWithUpdatedCart($cart);
    }

    /**
     * Remove an item from the cart based on UUID
     */
    public function destroy(Request $request, $uuid)
    {
        $cart = $this->getCartFromRequest($request);
        
        // Find and remove item by UUID
        $filteredCart = array_values(array_filter($cart, function ($item) use ($uuid) {
            return $item['uuid'] !== $uuid;
        }));
        
        if (count($filteredCart) === count($cart)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found in cart'
            ], 404);
        }
        
        return $this->responseWithUpdatedCart($filteredCart);
    }

    /**
     * Clear the entire cart
     */
    public function clear()
    {
        return $this->responseWithUpdatedCart([]);
    }

    /**
     * Get cart data from the cookie
     */
    protected function getCartFromRequest(Request $request)
    {
        $cartJson = $request->cookie('cart_data');
        
        if (empty($cartJson)) {
            return [];
        }
        
        $cart = json_decode($cartJson, true);
        return is_array($cart) ? $cart : [];
    }

    /**
     * Create response with updated cart and set cookie
     */
    protected function responseWithUpdatedCart(array $cart)
    {
        $cartJson = json_encode($cart);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Cart updated',
            'cart' => $cart
        ])->cookie('cart_data', $cartJson, 60 * 24 * 7); // 1 week expiration
    }

    /**
     * Find item index in cart by UUID
     */
    protected function findItemIndexByUuid($cart, $uuid)
    {
        foreach ($cart as $index => $item) {
            if ($item['uuid'] === $uuid) {
                return $index;
            }
        }
        
        return false;
    }
}
