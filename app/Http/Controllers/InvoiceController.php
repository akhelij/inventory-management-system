<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Models\Customer;
use Illuminate\Support\Collection;

class InvoiceController extends Controller
{
    public function create()
    {
        $carts = auth()->check() ? auth()->user()->getCart() : collect();
        $customers = Customer::all();

        return view('invoices.create', compact('carts', 'customers'));
    }
}
