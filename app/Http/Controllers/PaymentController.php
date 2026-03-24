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
            'cheque_photo' => 'nullable|string|max:500',
            'order_id' => 'nullable|integer',
        ]);

        $data = $request->except('order_id');
        $data['date'] = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');
        $data['echeance'] = Carbon::createFromFormat('d/m/Y', $request->echeance)->format('Y-m-d');

        $payment = Payment::create($data);

        if ($request->expectsJson()) {
            $responseData = [
                'payment' => [
                    'id' => $payment->id,
                    'nature' => $payment->nature,
                    'payment_type' => $payment->payment_type,
                    'bank' => $payment->bank,
                    'date' => $payment->date,
                    'echeance' => $payment->echeance,
                    'amount' => (float) $payment->amount,
                    'cashed_in' => (bool) $payment->cashed_in,
                    'reported' => (bool) $payment->reported,
                    'cheque_photo' => $payment->cheque_photo,
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
        $request->validate([
            'new_date' => 'required|date_format:d/m/Y',
        ]);

        $payment->update([
            'reported' => true,
            'echeance' => Carbon::createFromFormat('d/m/Y', $request->new_date)->format('Y-m-d'),
        ]);

        return to_route('customers.show', $payment->customer->uuid);
    }

    public function scanCheque(Request $request): JsonResponse
    {
        $request->validate(['cheque_image' => 'required|image|max:5120']);

        $result = app(\App\Services\ChequeOcrService::class)->extract($request->file('cheque_image'));

        $chequePhotoPath = $request->file('cheque_image')->store('payments/cheques', 'public');

        return response()->json([
            'success' => $result['success'],
            'data' => $result['data'] ?? null,
            'error' => $result['error'] ?? null,
            'cheque_photo' => $chequePhotoPath,
        ]);
    }

    public function attachCheque(Request $request, Payment $payment): JsonResponse
    {
        $request->validate(['cheque_image' => 'required|image|max:5120']);

        $chequePhotoPath = $request->file('cheque_image')->store('payments/cheques', 'public');

        $updateData = ['cheque_photo' => $chequePhotoPath];

        // Run OCR to detect bank if not already set
        if (empty($payment->bank)) {
            $result = app(\App\Services\ChequeOcrService::class)->extract($request->file('cheque_image'));
            if ($result['success'] && ! empty($result['data']['bank'])) {
                $updateData['bank'] = $result['data']['bank'];
            }
        }

        $payment->update($updateData);

        return response()->json([
            'cheque_photo' => $chequePhotoPath,
            'bank' => $payment->bank,
        ]);
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $payment->delete();

        return to_route('customers.show', $payment->customer->uuid);
    }
}
