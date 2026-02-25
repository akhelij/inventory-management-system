<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Payment;

class PaymentObserver
{
    public function updated(Payment $payment): void
    {
        // Payment-to-order allocation is handled manually
        // via OrderPaymentController on the customer page.
    }

    public function deleting(Payment $payment): void
    {
        // Collect affected order IDs before cascade delete removes pivot records
        $payment->affectedOrderIds = $payment->orders()->pluck('orders.id')->toArray();
    }

    public function deleted(Payment $payment): void
    {
        // Recalculate pay/due for orders that had this payment allocated
        if (! empty($payment->affectedOrderIds)) {
            Order::whereIn('id', $payment->affectedOrderIds)
                ->each(fn (Order $order) => $order->recalculatePayments());
        }
    }
}
