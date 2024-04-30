<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($customer_id)
    {
        $customer = Customer::findOrFail($customer_id);

        return view('payments.create', [
            'customer' => $customer,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Customer $customer)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'nature' => 'required|string',
            'bank'   => 'required|string',
            'date'   => 'required|date',
            'echeance' => 'required|date',
        ]);

        Payment::create($request->all());

        return redirect()->route('customers.show', $customer->uuid);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
