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
            'date'   => 'required|date',
            'bank'   => 'string|nullable',
            'payment_type' => 'required|string|in:HandCash,Cheque,Exchange',
            'nature' => [
                'required',
                'string',
                $request->payment_type != 'HandCash' ? 'unique:payments,nature' : '',
            ],
            'echeance' => 'required|date',
            'amount' => 'required|numeric',
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

    public function cash_in(Payment $payment)
    {
        $payment->update([
            'cashed_in' => true,
            'cashed_in_at' => now(),
            'reported' => false,
        ]);

        return redirect()->route('customers.show', $payment->customer->uuid);
    }

    public function report(Request $request, Payment $payment)
    {
        $payment->update([
            'reported' => true,
            'echeance' => $request->new_date,
        ]);

        return redirect()->route('customers.show', $payment->customer->uuid);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('customers.show', $payment->customer->uuid);
    }
}
