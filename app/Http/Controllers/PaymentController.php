<?php

namespace App\Http\Controllers;

use App\Enums\MoroccanBank;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function create(int $customer_id): View
    {
        return view('payments.create', [
            'customer' => Customer::findOrFail($customer_id),
            'banks' => MoroccanBank::cases(),
        ]);
    }

    public function store(Request $request, Customer $customer): RedirectResponse|JsonResponse
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
            'order_id' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['date'] = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');
        $data['echeance'] = Carbon::createFromFormat('d/m/Y', $request->echeance)->format('Y-m-d');

        $payment = Payment::create($data);

        if ($request->expectsJson()) {
            $responseData = [
                'payment' => [
                    'id' => $payment->id,
                    'nature' => $payment->nature,
                    'payment_type' => $payment->payment_type,
                    'date' => $payment->date,
                    'echeance' => $payment->echeance,
                    'amount' => (float) $payment->amount,
                    'bank' => $payment->bank,
                    'cashed_in' => (bool) $payment->cashed_in,
                    'reported' => (bool) $payment->reported,
                    'unallocated_amount' => $payment->unallocated_amount,
                    'is_fully_allocated' => $payment->is_fully_allocated,
                    'dragging' => false,
                ],
            ];

            if ($request->filled('order_id')) {
                $order = Order::where('id', $request->order_id)
                    ->where('customer_id', $customer->id)
                    ->where('order_status', OrderStatus::APPROVED)
                    ->where('due', '>', 0)
                    ->first();

                if ($order) {
                    $allocatedAmount = min($payment->amount, $order->due);
                    $order->payments()->attach($payment->id, [
                        'allocated_amount' => $allocatedAmount,
                        'user_id' => auth()->id(),
                    ]);
                    $order->recalculatePayments();
                    $order->refresh();
                    $payment->refresh();

                    $responseData['allocation'] = [
                        'allocated_amount' => $allocatedAmount,
                        'order' => [
                            'id' => $order->id,
                            'pay' => $order->pay,
                            'due' => $order->due,
                        ],
                    ];
                    $responseData['payment']['unallocated_amount'] = $payment->unallocated_amount;
                    $responseData['payment']['is_fully_allocated'] = $payment->is_fully_allocated;
                }
            }

            return response()->json($responseData, 201);
        }

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
