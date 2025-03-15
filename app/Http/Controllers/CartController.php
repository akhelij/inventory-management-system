<?php

namespace App\Http\Controllers;

use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request)
    {
        Cart::add('', '', '', '', '', [

        ]);
    }

    public function update() {}

    public function delete() {}
}
