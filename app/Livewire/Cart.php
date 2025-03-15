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
    }

    public function updateQuantity($rowId, $quantity)
    {
        G_Cart::update($rowId, $quantity);
    }

    public function updatePrice($rowId, $price)
    {
        $cart = G_Cart::get($rowId);
        if ($price > Product::find($cart->id)->selling_price) {
            $cart->updateFromArray(['price' => $price]);
        }
    }

    #[On('item-added')]
    public function render()
    {
        $carts = G_Cart::content();

        return view('livewire.cart')->with(compact('carts'));
    }
}
