<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;

class ProductCart extends Component
{
    public $listeners = ['productSelected', 'discountModalRefresh'];

    public $cart_instance;

    public $global_discount;

    public $global_tax;

    public $shipping;

    public $quantity;

    public $check_quantity;

    public $discount_type;

    public $item_discount;

    public $unit_price;

    public $data;

    private $product;

    public function mount($cart_instance, $global_discount = 0, $global_tax = 0, $shipping = 0): void
    {
        $this->cart_instance = $cart_instance;
        $this->global_discount = $global_discount;
        $this->global_tax = $global_tax;
        $this->shipping = $shipping;

        // Initialize arrays
        $this->check_quantity = [];
        $this->quantity = [];
        $this->discount_type = [];
        $this->item_discount = [];
        $this->unit_price = [];

        if (auth()->check()) {
            // Initialize discount and tax from session if available
            $this->global_discount = session('cart_global_discount', $this->global_discount);
            $this->global_tax = session('cart_global_tax', $this->global_tax);

            // Get cart items using our CartService
            $cartService = app(CartService::class)->instance($this->cart_instance);
            $cart_items = $cartService->content(auth()->id());

            foreach ($cart_items as $cart_item) {
                // Initialize arrays with values from cart items
                $this->check_quantity[$cart_item->id] = [$cart_item->options->stock ?? 0];
                $this->quantity[$cart_item->id] = $cart_item->qty;
                $this->discount_type[$cart_item->id] = $cart_item->options->product_discount_type ?? 'fixed';
                $this->item_discount[$cart_item->id] = $cart_item->options->product_discount ?? 0;
                $this->unit_price[$cart_item->id] = $cart_item->options->unit_price ?? $cart_item->price;
            }
        }
    }

    public function render()
    {
        $cartService = app(CartService::class)->instance($this->cart_instance);
        $cart_items = $cartService->content(auth()->id());

        return view('livewire.product-cart', [
            'cart_items' => $cart_items,
        ]);
    }

    public function productSelected($product): void
    {
        $cartService = app(CartService::class)->instance($this->cart_instance);
        
        // Check if product already exists in cart
        $cart = $cartService->getCart(auth()->id());
        $exists = false;
        $existingRowId = null;
        
        foreach ($cart->items as $item) {
            if ($item->product_id == $product['id']) {
                $exists = true;
                $existingRowId = $item->rowId;
                break;
            }
        }

        if ($exists) {
            $this->emit('error', 'Product already added to cart!');
            return;
        }

        $options = [
            'sub_total' => $product['price'],
            'code' => $product['code'],
            'stock' => $product['quantity'],
            'unit_price' => $product['price'],
            'product_discount' => 0.00,
            'product_discount_type' => 'fixed',
        ];

        // Add item to cart
        $cartService->addItem(
            auth()->id(),
            $product['id'],
            $product['name'],
            $product['price'],
            1,
            $options
        );

        $this->check_quantity[$product['id']] = [$product['quantity']];
        $this->quantity[$product['id']] = 1;
        $this->unit_price[$product['id']] = $product['price'];
        $this->item_discount[$product['id']] = 0;
        $this->discount_type[$product['id']] = 'fixed';
    }

    public function removeItem($row_id): void
    {
        app(CartService::class)->instance($this->cart_instance)->removeItem(auth()->id(), $row_id);
    }

    public function updatedGlobalTax(): void
    {
        // Our CartService doesn't have a setGlobalTax method yet, so we'll implement it later
        // For now, we'll just store the value in the session
        session(['cart_global_tax' => (int) $this->global_tax]);
    }

    public function updatedGlobalDiscount(): void
    {
        // Our CartService doesn't have a setGlobalDiscount method yet, so we'll implement it later
        // For now, we'll just store the value in the session
        session(['cart_global_discount' => (int) $this->global_discount]);
    }

    public function updateQuantity($row_id, $product_id): void
    {
        // Check if quantity is valid
        if (isset($this->check_quantity[$product_id])) {
            if ($this->quantity[$product_id] > $this->check_quantity[$product_id][0]) {
                $this->quantity[$product_id] = $this->check_quantity[$product_id][0];
            }
        }

        // Update quantity using our CartService
        $cartService = app(CartService::class)->instance($this->cart_instance);
        $cartService->updateQuantity(auth()->id(), $row_id, $this->quantity[$product_id]);

        // Get updated cart item
        $cart = $cartService->getCart(auth()->id());
        $item = $cart->items()->where('rowId', $row_id)->first();

        if ($item) {
            // Update options
            $options = $item->options ?? [];
            $options['sub_total'] = $item->price * $item->quantity;
            
            $cartService->updateItemOptions(auth()->id(), $row_id, $options);
        }
    }

    public function updatedDiscountType($value, $name): void
    {
        $this->item_discount[$name] = 0;
    }

    public function discountModalRefresh($product_id, $row_id): void
    {
        $this->updateQuantity($row_id, $product_id);
    }

    public function setProductDiscount($row_id, $product_id): void
    {
        $cartService = app(CartService::class)->instance($this->cart_instance);
        $cart = $cartService->getCart(auth()->id());
        $item = $cart->items()->where('rowId', $row_id)->first();

        if (!$item) {
            return;
        }

        // Get the original price from options
        $originalPrice = $item->options['unit_price'] ?? $item->price;
        $newPrice = $originalPrice;
        $discountAmount = 0;

        if ($this->discount_type[$product_id] == 'fixed') {
            $newPrice = $originalPrice - $this->item_discount[$product_id];
            $discountAmount = $this->item_discount[$product_id];
        } elseif ($this->discount_type[$product_id] == 'percentage') {
            $discountAmount = $originalPrice * ($this->item_discount[$product_id] / 100);
            $newPrice = $originalPrice - $discountAmount;
        }

        // Ensure price doesn't go below zero
        $newPrice = max(0, $newPrice);

        // Update price
        $cartService->updatePrice(auth()->id(), $row_id, $newPrice);

        // Update options
        $options = $item->options ?? [];
        $options['product_discount'] = $discountAmount;
        $options['product_discount_type'] = $this->discount_type[$product_id];
        
        $cartService->updateItemOptions(auth()->id(), $row_id, $options);
    }

    public function setProductPrice($row_id, $product_id): void
    {
        $product = Product::findOrFail($product_id);
        $cartService = app(CartService::class)->instance($this->cart_instance);
        
        // Update price
        $cartService->updatePrice(auth()->id(), $row_id, $this->unit_price[$product['id']]);

        // Get updated cart item
        $cart = $cartService->getCart(auth()->id());
        $item = $cart->items()->where('rowId', $row_id)->first();

        if ($item) {
            // Update options
            $options = $item->options ?? [];
            $options['sub_total'] = $item->price * $item->quantity;
            $options['unit_price'] = $this->unit_price[$product['id']];
            
            $cartService->updateItemOptions(auth()->id(), $row_id, $options);
        }
    }

    // Alias for setProductPrice to maintain backward compatibility
    public function updatePrice($row_id, $product_id): void
    {
        $this->setProductPrice($row_id, $product_id);
    }

    public function calculate($product, $new_price = null): array
    {
        if ($new_price) {
            $product_price = $new_price;
        } else {
            $this->unit_price[$product['id']] = $product['selling_price']; // selling price?

            if ($this->cart_instance == 'purchase' || $this->cart_instance == 'purchase_return') {
                $this->unit_price[$product['id']] = $product['product_cost'];
            }

            $product_price = $this->unit_price[$product['id']];
        }
        $price = 0;
        $unit_price = 0;
        $product_tax = 0;
        $sub_total = 0;

        $price = $product_price;
        $unit_price = $product_price;
        $product_tax = 0.00;
        $sub_total = $product_price;

        return ['price' => $price, 'unit_price' => $unit_price, 'tax' => $product_tax, 'sub_total' => $sub_total];
    }

    public function updateCartOptions($row_id, $product_id, $cart_item, $discount_amount): void
    {
        $cartService = app(CartService::class)->instance($this->cart_instance);
        
        $options = [
            'sub_total' => $cart_item->price * $cart_item->qty,
            'code' => $cart_item->options->code ?? '',
            'stock' => $cart_item->options->stock ?? 0,
            'unit' => $cart_item->options->unit ?? '',
            'product_tax' => 0,
            'unit_price' => $cart_item->options->unit_price ?? $cart_item->price,
            'product_discount' => $discount_amount,
            'product_discount_type' => $this->discount_type[$product_id] ?? 'fixed',
        ];
        
        $cartService->updateItemOptions(auth()->id(), $row_id, $options);
    }

    /**
     * Store the current cart in the database
     */
    private function storeCart(): void
    {
        if (auth()->check()) {
            try {
                // Our new CartService handles storage automatically, so this is now a no-op
                // Keeping the method for backward compatibility
            } catch (\Exception $e) {
                // Log error or handle silently
            }
        }
    }
}
