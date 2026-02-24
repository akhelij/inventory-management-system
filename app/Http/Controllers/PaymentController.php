<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function create(int $customer_id): View
    {
        return view('payments.create', [
            'customer' => Customer::findOrFail($customer_id),
        ]);
    }

    public function store(Request $request, Customer $customer): RedirectResponse
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

        $data = $request->all();
        $data['date'] = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');
        $data['echeance'] = Carbon::createFromFormat('d/m/Y', $request->echeance)->format('Y-m-d');

        Payment::create($data);

        return to_route('customers.show', $customer->uuid);
    }

    public function show(Payment $payment): View
    {
        return (new CustomerController)->show($payment->customer->uuid);
    }

    public function cash_in(Payment $payment): RedirectResponse
    {
        $payment->update([
            'cashed_in' => true,
            'cashed_in_at' => now(),
            'reported' => false,
        ]);

        return to_route('customers.show', $payment->customer->uuid);
    }

    public function report(Request $request, Payment $payment): RedirectResponse
    {
        $payment->update([
            'reported' => true,
            'echeance' => $request->new_date,
        ]);

        return to_route('customers.show', $payment->customer->uuid);
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $payment->delete();

        return to_route('customers.show', $payment->customer->uuid);
    }
}
