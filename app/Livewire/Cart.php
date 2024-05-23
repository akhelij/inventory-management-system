<?php

namespace App\Livewire;

use App\Models\OrderDetails;
use Gloudemans\Shoppingcart\CartItem;
use Livewire\Component;
use Livewire\Attributes\On;
use Gloudemans\Shoppingcart\Facades\Cart as G_Cart;

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

    #[On('item-added')]
    public function render()
    {
        $carts = G_Cart::content();
        return view('livewire.cart')->with(compact('carts'));
    }
}
