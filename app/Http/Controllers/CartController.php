<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Get the current cart items
     */
    public function index(Request $request)
    {
        $cart = $this->getUserCart();
        return response()->json($this->formatCartItems($cart));
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
            'quantity' => 'sometimes|numeric|min:1',
            'is_free' => 'sometimes|boolean'
        ]);

        $cart = $this->getUserCart();
        
        // Only check for duplicates if this is not a free item
        $isFree = $request->has('is_free') && $validated['is_free'] === true;
        $productExists = false;
        
        if (!$isFree) {
            // Check if non-free version of this product already exists
            $productExists = $cart->items()
                ->where('product_id', $validated['id'])
                ->whereJsonDoesntContain('options->is_free', true)
                ->exists();
        }
        
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

        // Prepare options
        $options = [
            'product_id' => $product->id,
            'basePrice' => $isFree ? 0 : (float) $product->selling_price,
            'max_qty' => $product->quantity,
            'is_free' => $isFree,
            'uuid' => Str::uuid()->toString()
        ];

        // Generate rowId for compatibility
        $rowId = CartItem::generateRowId($product->id, $options);
        
        // Create cart item
        $cartItem = new CartItem([
            'product_id' => $product->id,
            'name' => $isFree ? $product->name . ' (Gift)' : $product->name,
            'quantity' => $requestedQty,
            'price' => $isFree ? 0 : (float) $validated['price'],
            'total' => $isFree ? 0 : (float) $validated['price'] * $requestedQty,
            'options' => $options,
            'rowId' => $rowId
        ]);
        
        // Associate with cart and save
        $cart->items()->save($cartItem);
        
        // Recalculate cart total
        $cart->recalculate();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Item added to cart',
            'cart' => $this->formatCartItems($cart)
        ]);
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

        $cart = $this->getUserCart();
        
        // Find item by UUID stored in options
        $cartItem = $cart->items()
            ->whereJsonContains('options->uuid', $uuid)
            ->first();
        
        if (!$cartItem) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found in cart'
            ], 404);
        }

        // If updating quantity, check stock availability
        if (isset($validated['quantity'])) {
            $product = Product::findOrFail($cartItem->product_id);
            
            if ($validated['quantity'] > $product->quantity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Requested quantity exceeds available stock (' . $product->quantity . ')'
                ], 400);
            }
            
            $cartItem->quantity = (int) $validated['quantity'];
            
            // Update max_qty in options
            $options = $cartItem->options;
            $options['max_qty'] = $product->quantity;
            $cartItem->options = $options;
        }

        // Update price if provided
        if (isset($validated['price'])) {
            $isFree = isset($cartItem->options['is_free']) && $cartItem->options['is_free'];
            $basePrice = $cartItem->options['basePrice'] ?? 0;
            
            if ($isFree) {
                $cartItem->price = 0;
            } else {
                $newPrice = max((float) $validated['price'], $basePrice);
                $cartItem->price = $newPrice;
            }
        }

        // Recalculate item total
        $cartItem->total = $cartItem->price * $cartItem->quantity;
        $cartItem->save();
        
        // Recalculate cart total
        $cart->recalculate();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Cart item updated',
            'cart' => $this->formatCartItems($cart)
        ]);
    }

    /**
     * Remove an item from the cart based on UUID
     */
    public function destroy(Request $request, $uuid)
    {
        $cart = $this->getUserCart();
        
        // Find and delete item by UUID in options
        $deleted = $cart->items()
            ->whereJsonContains('options->uuid', $uuid)
            ->delete();
        
        if (!$deleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found in cart'
            ], 404);
        }
        
        // Recalculate cart total
        $cart->recalculate();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Item removed from cart',
            'cart' => $this->formatCartItems($cart)
        ]);
    }

    /**
     * Clear the entire cart
     */
    public function clear()
    {
        $cart = $this->getUserCart();
        
        // Delete all items
        $cart->items()->delete();
        
        // Reset cart total
        $cart->total = 0;
        $cart->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Cart cleared',
            'cart' => []
        ]);
    }

    /**
     * Get or create a cart for the current user
     */
    protected function getUserCart()
    {
        $user = auth()->user();
        
        // Get or create user's cart with default instance
        return Cart::firstOrCreate(
            ['user_id' => $user->id, 'instance' => 'default'],
            ['total' => 0]
        );
    }

    /**
     * Format cart items for frontend compatibility
     */
    protected function formatCartItems(Cart $cart)
    {
        // Ensure items are loaded
        $cart->load('items');
        
        return $cart->items->map(function ($item) {
            $options = $item->options ?? [];
            
            return [
                'uuid' => $options['uuid'] ?? Str::uuid()->toString(),
                'product_id' => $item->product_id,
                'name' => $item->name,
                'qty' => $item->quantity,
                'price' => (float) $item->price,
                'basePrice' => $options['basePrice'] ?? (float) $item->price,
                'subtotal' => (float) $item->total,
                'max_qty' => $options['max_qty'] ?? 0,
                'is_free' => $options['is_free'] ?? false,
                'rowId' => $item->rowId
            ];
        })->toArray();
    }
}
