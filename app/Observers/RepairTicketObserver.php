<?php

namespace App\Observers;

use App\Models\RepairTicket;
use Illuminate\Support\Facades\Auth;

class RepairTicketObserver
{
    public function created(RepairTicket $repairTicket): void
    {
        $repairTicket->statusHistories()->create([
            'user_id' => Auth::id(),
            'from_status' => '',
            'to_status' => 'RECEIVED',
            'comment' => 'Ticket created',
        ]);
    }
}
