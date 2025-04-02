<?php

namespace App\Observers;

use App\Models\RepairTicket;
use Illuminate\Support\Facades\Auth;

class RepairTicketObserver
{
    public function updating(RepairTicket $repairTicket)
    {
        // Status history is now handled in the controller
        // with detailed information about repairs
    }

    public function created(RepairTicket $repairTicket): void
    {
        // Create initial status history record
        $repairTicket->statusHistories()->create([
            'user_id' => Auth::id(),
            'from_status' => '',
            'to_status' => 'RECEIVED',
            'comment' => 'Ticket created',
        ]);
    }
}
