<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class Cart extends Component
{
    public function delete($rowId)
    {
        app(CartService::class)->removeItem(auth()->id(), $rowId);
    }

    public function updateQuantity($rowId, $quantity)
    {
        app(CartService::class)->updateQuantity(auth()->id(), $rowId, $quantity);
    }

    public function updatePrice($rowId, $price)
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getCart(auth()->id());
        $item = $cart->items()->where('rowId', $rowId)->first();
        
        if ($item) {
            $product = Product::find($item->product_id);
            if ($price > $product->selling_price) {
                $cartService->updatePrice(auth()->id(), $rowId, $price);
            }
        }
    }

    #[On('item-added')]
    public function render()
    {
        // Get cart content using our new CartService
        $carts = auth()->check() 
            ? app(CartService::class)->content(auth()->id())
            : collect();
            
        return view('livewire.cart')->with(compact('carts'));
    }
}
