<?php

namespace App\Observers;

use App\Models\RepairTicket;

class RepairTicketObserver
{
    public function updating(RepairTicket $repairTicket)
    {
        // Check if status is being changed
        if ($repairTicket->isDirty('status')) {
            $repairTicket->statusHistories()->create([
                'user_id' => auth()->id(),
                'from_status' => $repairTicket->getOriginal('status'),
                'to_status' => $repairTicket->status,
                'comment' => request('status_comment') ?? 'Status updated'
            ]);
        }
    }

    public function created(RepairTicket $repairTicket)
    {
        // Create initial status history
        $repairTicket->statusHistories()->create([
            'user_id' => auth()->id(),
            'from_status' => null,
            'to_status' => $repairTicket->status,
            'comment' => 'Ticket created'
        ]);
    }
}
