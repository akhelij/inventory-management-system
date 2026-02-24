<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;

class OrderPaymentController extends Controller
{
    public function store(Order $order, Payment $payment): JsonResponse
    {
        if ($order->customer_id !== $payment->customer_id) {
            return response()->json(['message' => 'Payment and order must belong to the same customer.'], 422);
        }

        if (! $payment->cashed_in) {
            return response()->json(['message' => 'Only cashed-in payments can be allocated.'], 422);
        }

        if ($order->order_status !== OrderStatus::APPROVED) {
            return response()->json(['message' => 'Only approved orders can receive payment allocations.'], 422);
        }

        if ($order->due <= 0) {
            return response()->json(['message' => 'Order is already fully paid.'], 422);
        }

        if ($order->payments()->where('payment_id', $payment->id)->exists()) {
            return response()->json(['message' => 'This payment is already allocated to this order.'], 422);
        }

        $unallocated = $payment->unallocated_amount;

        if ($unallocated <= 0) {
            return response()->json(['message' => 'Payment is fully allocated.'], 422);
        }

        $allocatedAmount = min($unallocated, $order->due);

        $order->payments()->attach($payment->id, [
            'allocated_amount' => $allocatedAmount,
            'user_id' => auth()->id(),
        ]);

        $order->recalculatePayments();
        $order->load('payments');
        $payment->refresh();

        return response()->json([
            'message' => 'Payment allocated successfully.',
            'allocated_amount' => $allocatedAmount,
            'order' => [
                'id' => $order->id,
                'pay' => $order->pay,
                'due' => $order->due,
            ],
            'payment' => [
                'id' => $payment->id,
                'unallocated_amount' => $payment->unallocated_amount,
                'is_fully_allocated' => $payment->is_fully_allocated,
            ],
        ]);
    }

    public function destroy(Order $order, Payment $payment): JsonResponse
    {
        if (! $order->payments()->where('payment_id', $payment->id)->exists()) {
            return response()->json(['message' => 'Allocation not found.'], 404);
        }

        $order->payments()->detach($payment->id);
        $order->recalculatePayments();
        $order->load('payments');
        $payment->refresh();

        return response()->json([
            'message' => 'Payment allocation removed.',
            'order' => [
                'id' => $order->id,
                'pay' => $order->pay,
                'due' => $order->due,
            ],
            'payment' => [
                'id' => $payment->id,
                'unallocated_amount' => $payment->unallocated_amount,
                'is_fully_allocated' => $payment->is_fully_allocated,
            ],
        ]);
    }
}
