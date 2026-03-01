<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cart = $this->getUserCart();

        return response()->json($this->formatCartItems($cart));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|exists:products,id',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'sometimes|numeric|min:1',
            'is_free' => 'sometimes|boolean',
        ]);

        $cart = $this->getUserCart();

        $isFree = $request->has('is_free') && $validated['is_free'] === true;

        if (! $isFree) {
            $productExists = $cart->items()
                ->where('product_id', $validated['id'])
                ->whereJsonDoesntContain('options->is_free', true)
                ->exists();

            if ($productExists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This item is already in your cart',
                ], 400);
            }
        }

        $product = Product::findOrFail($validated['id']);
        $requestedQty = $validated['quantity'] ?? 1;

        // Sum quantity of all cart items for this product (both paid and gift)
        $cartQuantityForProduct = $cart->items()
            ->where('product_id', $product->id)
            ->sum('quantity');

        $totalAfterAdd = $cartQuantityForProduct + $requestedQty;

        if ($totalAfterAdd > $product->quantity) {
            $remaining = $product->quantity - $cartQuantityForProduct;

            return response()->json([
                'status' => 'error',
                'message' => "Not enough stock. Available: {$product->quantity}, Already in cart: {$cartQuantityForProduct}, Remaining: {$remaining}",
            ], 400);
        }

        // Check pending orders for the same product
        $pendingOrderQty = OrderDetails::whereHas('order', fn ($q) => $q->whereNull('order_status')->where('stock_affected', false))
            ->where('product_id', $product->id)
            ->sum('quantity');

        $effectiveAvailable = $product->quantity - $pendingOrderQty;

        if ($totalAfterAdd > $effectiveAvailable) {
            $pendingOrders = Order::whereNull('order_status')
                ->where('stock_affected', false)
                ->whereHas('details', fn ($q) => $q->where('product_id', $product->id))
                ->with('customer:id,name')
                ->get(['id', 'invoice_no', 'customer_id']);

            $orderList = $pendingOrders->map(fn ($o) => "{$o->invoice_no} ({$o->customer->name})")->join(', ');

            return response()->json([
                'status' => 'error',
                'message' => "Product exists in pending orders ({$orderList}). Available stock: {$product->quantity}, Reserved in pending: {$pendingOrderQty}. Remove from other orders first.",
                'pending_orders' => $pendingOrders,
            ], 400);
        }

        $options = [
            'product_id' => $product->id,
            'basePrice' => $isFree ? 0 : (float) $product->selling_price,
            'max_qty' => $product->quantity,
            'is_free' => $isFree,
            'uuid' => Str::uuid()->toString(),
        ];

        $rowId = CartItem::generateRowId($product->id, $options);

        $cartItem = new CartItem([
            'product_id' => $product->id,
            'name' => $isFree ? $product->name.' (Gift)' : $product->name,
            'quantity' => $requestedQty,
            'price' => $isFree ? 0 : (float) $validated['price'],
            'total' => $isFree ? 0 : (float) $validated['price'] * $requestedQty,
            'options' => $options,
            'rowId' => $rowId,
        ]);

        $cart->items()->save($cartItem);
        $cart->recalculate();

        return response()->json([
            'status' => 'success',
            'message' => 'Item added to cart',
            'cart' => $this->formatCartItems($cart),
        ]);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'sometimes|numeric|min:1',
            'price' => 'sometimes|numeric|min:0',
        ]);

        $cart = $this->getUserCart();

        $cartItem = $cart->items()
            ->whereJsonContains('options->uuid', $uuid)
            ->first();

        if (! $cartItem) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found in cart',
            ], 404);
        }

        if (isset($validated['quantity'])) {
            $product = Product::findOrFail($cartItem->product_id);

            if ($validated['quantity'] > $product->quantity) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Requested quantity exceeds available stock ({$product->quantity})",
                ], 400);
            }

            $cartItem->quantity = (int) $validated['quantity'];

            $options = $cartItem->options;
            $options['max_qty'] = $product->quantity;
            $cartItem->options = $options;
        }

        if (isset($validated['price'])) {
            $isFree = $cartItem->options['is_free'] ?? false;
            $basePrice = $cartItem->options['basePrice'] ?? 0;

            $cartItem->price = $isFree ? 0 : max((float) $validated['price'], $basePrice);
        }

        $cartItem->total = $cartItem->price * $cartItem->quantity;
        $cartItem->save();

        $cart->recalculate();

        return response()->json([
            'status' => 'success',
            'message' => 'Cart item updated',
            'cart' => $this->formatCartItems($cart),
        ]);
    }

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $cart = $this->getUserCart();

        $deleted = $cart->items()
            ->whereJsonContains('options->uuid', $uuid)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found in cart',
            ], 404);
        }

        $cart->recalculate();

        return response()->json([
            'status' => 'success',
            'message' => 'Item removed from cart',
            'cart' => $this->formatCartItems($cart),
        ]);
    }

    public function clear(): JsonResponse
    {
        $cart = $this->getUserCart();

        $cart->items()->delete();
        $cart->update(['total' => 0]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart cleared',
            'cart' => [],
        ]);
    }

    protected function getUserCart(): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => auth()->id(), 'instance' => 'default'],
            ['total' => 0]
        );
    }

    protected function formatCartItems(Cart $cart): array
    {
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
                'rowId' => $item->rowId,
            ];
        })->toArray();
    }
}
