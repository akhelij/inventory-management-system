<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function create(): View
    {
        return view('invoices.create', [
            'carts' => auth()->check() ? auth()->user()->getCart() : collect(),
            'customers' => Customer::all(),
        ]);
    }
}
