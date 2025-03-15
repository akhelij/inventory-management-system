<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        //
    }

    public function updated(Payment $payment): void
    {
        if ($payment->wasChanged('cashed_in') && $payment->cashed_in == 1) {
            $customer = $payment->customer;
            $orders = $customer->orders()->where('due', '>', 0)->get();
            $paymentAmount = $payment->amount;
            foreach ($orders as $order) {
                if ($paymentAmount > 0) {
                    $due = $order->due;
                    if ($paymentAmount >= $due) {
                        $paymentAmount -= $due;
                        $order->update(['pay' => $order->due, 'due' => 0, 'order_status' => 2]);
                    } else {
                        $order->update(['pay' => $order->pay + $paymentAmount, 'due' => $due - $paymentAmount]);
                        $paymentAmount = 0;
                    }
                }
            }
        }
    }
}
