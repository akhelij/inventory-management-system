<?php

namespace App\Livewire;

use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart as G_Cart;
use Livewire\Attributes\On;
use Livewire\Component;

class Cart extends Component
{
    public function delete($rowId)
    {
        G_Cart::remove($rowId);
        $this->storeCart();
    }

    public function updateQuantity($rowId, $quantity)
    {
        G_Cart::update($rowId, $quantity);
        $this->storeCart();
    }

    public function updatePrice($rowId, $price)
    {
        $cart = G_Cart::get($rowId);
        if ($price > Product::find($cart->id)->selling_price) {
            $cart->updateFromArray(['price' => $price]);
            $this->storeCart();
        }
    }

    /**
     * Store the current cart in the database
     */
    private function storeCart(): void
    {
        if (auth()->check()) {
            try {
                // Delete existing cart before storing the new one
                G_Cart::erase(auth()->id());
                G_Cart::store(auth()->id());
            } catch (\Exception $e) {
                // Log error or handle silently
            }
        }
    }

    #[On('item-added')]
    public function handleItemAdded()
    {
        // When an item is added to the cart, store it in the database
        $this->storeCart();
    }

    #[On('item-added')]
    public function render()
    {
        // Get cart content directly from database using the User model
        $carts = auth()->check() ? auth()->user()->getCart() : collect();
        return view('livewire.cart')->with(compact('carts'));
    }
}
