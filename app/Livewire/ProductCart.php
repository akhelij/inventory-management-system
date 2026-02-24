<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductCart extends Component
{
    public string $cart_instance;

    public int $global_discount = 0;

    public int $global_tax = 0;

    public int $shipping = 0;

    public array $quantity = [];

    public array $check_quantity = [];

    public array $discount_type = [];

    public array $item_discount = [];

    public array $unit_price = [];

    public function mount(string $cart_instance, int $global_discount = 0, int $global_tax = 0, int $shipping = 0): void
    {
        $this->cart_instance = $cart_instance;
        $this->global_discount = $global_discount;
        $this->global_tax = $global_tax;
        $this->shipping = $shipping;

        if (! auth()->check()) {
            return;
        }

        $this->global_discount = session('cart_global_discount', $this->global_discount);
        $this->global_tax = session('cart_global_tax', $this->global_tax);

        $cart_items = $this->cartService()->content(auth()->id());

        foreach ($cart_items as $cart_item) {
            $this->check_quantity[$cart_item->id] = [$cart_item->options->stock ?? 0];
            $this->quantity[$cart_item->id] = $cart_item->qty;
            $this->discount_type[$cart_item->id] = $cart_item->options->product_discount_type ?? 'fixed';
            $this->item_discount[$cart_item->id] = $cart_item->options->product_discount ?? 0;
            $this->unit_price[$cart_item->id] = $cart_item->options->unit_price ?? $cart_item->price;
        }
    }

    #[On('productSelected')]
    public function productSelected(array $product): void
    {
        $cartService = $this->cartService();
        $cart = $cartService->getCart(auth()->id());

        foreach ($cart->items as $item) {
            if ($item->product_id == $product['id']) {
                $this->emit('error', 'Product already added to cart!');

                return;
            }
        }

        $cartService->addItem(
            auth()->id(),
            $product['id'],
            $product['name'],
            $product['price'],
            1,
            [
                'sub_total' => $product['price'],
                'code' => $product['code'],
                'stock' => $product['quantity'],
                'unit_price' => $product['price'],
                'product_discount' => 0.00,
                'product_discount_type' => 'fixed',
            ]
        );

        $this->check_quantity[$product['id']] = [$product['quantity']];
        $this->quantity[$product['id']] = 1;
        $this->unit_price[$product['id']] = $product['price'];
        $this->item_discount[$product['id']] = 0;
        $this->discount_type[$product['id']] = 'fixed';
    }

    public function removeItem(string $row_id): void
    {
        $this->cartService()->removeItem(auth()->id(), $row_id);
    }

    public function updatedGlobalTax(): void
    {
        session(['cart_global_tax' => (int) $this->global_tax]);
    }

    public function updatedGlobalDiscount(): void
    {
        session(['cart_global_discount' => (int) $this->global_discount]);
    }

    public function updateQuantity(string $row_id, int $product_id): void
    {
        if (isset($this->check_quantity[$product_id]) && $this->quantity[$product_id] > $this->check_quantity[$product_id][0]) {
            $this->quantity[$product_id] = $this->check_quantity[$product_id][0];
        }

        $cartService = $this->cartService();
        $cartService->updateQuantity(auth()->id(), $row_id, $this->quantity[$product_id]);

        $cart = $cartService->getCart(auth()->id());
        $item = $cart->items()->where('rowId', $row_id)->first();

        if ($item) {
            $options = $item->options ?? [];
            $options['sub_total'] = $item->price * $item->quantity;
            $cartService->updateItemOptions(auth()->id(), $row_id, $options);
        }
    }

    public function updatedDiscountType(mixed $value, string $name): void
    {
        $this->item_discount[$name] = 0;
    }

    #[On('discountModalRefresh')]
    public function discountModalRefresh(int $product_id, string $row_id): void
    {
        $this->updateQuantity($row_id, $product_id);
    }

    public function setProductDiscount(string $row_id, int $product_id): void
    {
        $cartService = $this->cartService();
        $cart = $cartService->getCart(auth()->id());
        $item = $cart->items()->where('rowId', $row_id)->first();

        if (! $item) {
            return;
        }

        $originalPrice = $item->options['unit_price'] ?? $item->price;

        $discountAmount = match ($this->discount_type[$product_id]) {
            'percentage' => $originalPrice * ($this->item_discount[$product_id] / 100),
            default => $this->item_discount[$product_id],
        };

        $newPrice = max(0, $originalPrice - $discountAmount);

        $cartService->updatePrice(auth()->id(), $row_id, $newPrice);

        $options = $item->options ?? [];
        $options['product_discount'] = $discountAmount;
        $options['product_discount_type'] = $this->discount_type[$product_id];
        $cartService->updateItemOptions(auth()->id(), $row_id, $options);
    }

    public function setProductPrice(string $row_id, int $product_id): void
    {
        $cartService = $this->cartService();
        $cartService->updatePrice(auth()->id(), $row_id, $this->unit_price[$product_id]);

        $cart = $cartService->getCart(auth()->id());
        $item = $cart->items()->where('rowId', $row_id)->first();

        if ($item) {
            $options = $item->options ?? [];
            $options['sub_total'] = $item->price * $item->quantity;
            $options['unit_price'] = $this->unit_price[$product_id];
            $cartService->updateItemOptions(auth()->id(), $row_id, $options);
        }
    }

    public function updatePrice(string $row_id, int $product_id): void
    {
        $this->setProductPrice($row_id, $product_id);
    }

    public function calculate(array $product, ?float $new_price = null): array
    {
        if ($new_price) {
            $product_price = $new_price;
        } else {
            $this->unit_price[$product['id']] = $product['selling_price'];

            if (in_array($this->cart_instance, ['purchase', 'purchase_return'])) {
                $this->unit_price[$product['id']] = $product['product_cost'];
            }

            $product_price = $this->unit_price[$product['id']];
        }

        return [
            'price' => $product_price,
            'unit_price' => $product_price,
            'tax' => 0.00,
            'sub_total' => $product_price,
        ];
    }

    public function updateCartOptions(string $row_id, int $product_id, $cart_item, float $discount_amount): void
    {
        $this->cartService()->updateItemOptions(auth()->id(), $row_id, [
            'sub_total' => $cart_item->price * $cart_item->qty,
            'code' => $cart_item->options->code ?? '',
            'stock' => $cart_item->options->stock ?? 0,
            'unit' => $cart_item->options->unit ?? '',
            'product_tax' => 0,
            'unit_price' => $cart_item->options->unit_price ?? $cart_item->price,
            'product_discount' => $discount_amount,
            'product_discount_type' => $this->discount_type[$product_id] ?? 'fixed',
        ]);
    }

    public function render()
    {
        $cart_items = $this->cartService()->content(auth()->id());

        return view('livewire.product-cart', [
            'cart_items' => $cart_items,
        ]);
    }

    private function cartService(): CartService
    {
        return app(CartService::class)->instance($this->cart_instance);
    }
}
