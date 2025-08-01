<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
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
            'date' => 'required|date_format:d/m/Y',
            'bank' => 'string|nullable',
            'payment_type' => 'required|string|in:HandCash,Cheque,Exchange',
            'nature' => [
                'required',
                'string',
                $request->payment_type != 'HandCash' ? 'unique:payments,nature' : '',
            ],
            'echeance' => 'required|date_format:d/m/Y',
            'amount' => 'required|numeric',
            'description' => 'nullable|string|max:1000',
        ]);

        // Get all request data
        $data = $request->all();
        
        // Convert dates from d/m/Y to Y-m-d for database storage
        $data['date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');
        $data['echeance'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->echeance)->format('Y-m-d');

        Payment::create($data);

        return redirect()->route('customers.show', $customer->uuid);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        return (new CustomerController)->show($payment->customer->uuid);
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
