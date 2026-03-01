<?php

namespace App\Console\Commands;

use App\Models\InstallmentEntry;
use App\Notifications\InstallmentOverdueNotification;
use Illuminate\Console\Command;

class CheckOverdueInstallments extends Command
{
    protected $signature = 'installments:check-overdue';

    protected $description = 'Check for overdue installments and notify schedule owners';

    public function handle(): int
    {
        $overdueEntries = InstallmentEntry::where('status', 'pending')
            ->where('due_date', '<', now())
            ->with(['schedule.order', 'schedule.customer', 'schedule.user'])
            ->get();

        $count = 0;

        foreach ($overdueEntries as $entry) {
            $entry->update(['status' => 'overdue']);

            $owner = $entry->schedule->user;
            if ($owner) {
                $owner->notify(new InstallmentOverdueNotification($entry));
                $count++;
            }
        }

        $this->info("Processed {$overdueEntries->count()} overdue installments, sent {$count} notifications.");

        return self::SUCCESS;
    }
}
