<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    public function updated(Payment $payment): void
    {
        // Payment-to-order allocation is handled manually
        // via OrderPaymentController on the customer page.
    }
}
