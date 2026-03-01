<?php

namespace App\Notifications;

use App\Models\InstallmentEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InstallmentOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public InstallmentEntry $entry,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $schedule = $this->entry->schedule;

        return [
            'entry_id' => $this->entry->id,
            'order_invoice' => $schedule->order->invoice_no,
            'customer_name' => $schedule->customer->name,
            'amount' => $this->entry->amount,
            'due_date' => $this->entry->due_date->format('d/m/Y'),
            'installment_number' => $this->entry->installment_number,
            'message' => "Installment #{$this->entry->installment_number} of {$schedule->order->invoice_no} ({$schedule->customer->name}) is overdue. Amount: {$this->entry->amount} MAD, Due: {$this->entry->due_date->format('d/m/Y')}",
        ];
    }
}
