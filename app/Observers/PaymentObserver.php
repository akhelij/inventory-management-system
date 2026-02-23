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
        // Auto-cascade removed. Payment-to-order allocation is now manual
        // via OrderPaymentController drag-and-drop on the customer page.
    }
}
