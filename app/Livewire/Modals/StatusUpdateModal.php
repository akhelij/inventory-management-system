<?php

namespace App\Livewire\Modals;

use App\Models\RepairTicket;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class StatusUpdateModal extends Component
{
    public ?int $ticketId = null;

    public ?string $newStatus = null;

    public ?string $currentStatus = null;

    public string $statusComment = '';

    protected array $rules = [
        'statusComment' => 'required|string|min:3',
    ];

    private const STATUSES = [
        'RECEIVED' => 'Received',
        'IN_PROGRESS' => 'In Progress',
        'REPAIRED' => 'Repaired',
        'UNREPAIRABLE' => 'Unrepairable',
        'DELIVERED' => 'Delivered',
    ];

    #[On('prepareStatusUpdate')]
    public function prepareStatusUpdate(array $data): void
    {
        $this->ticketId = $data['ticketId'];
        $this->currentStatus = $data['currentStatus'];
        $this->newStatus = $data['newStatus'];
        $this->statusComment = '';
    }

    public function updateStatus(): void
    {
        $this->validate();

        try {
            $ticket = RepairTicket::findOrFail($this->ticketId);

            DB::transaction(function () use ($ticket) {
                $ticket->update(['status' => $this->newStatus]);

                request()->merge(['status_comment' => $this->statusComment]);
            });

            $this->js('
                const modal = bootstrap.Modal.getInstance(document.getElementById("statusUpdateModal"));
                modal.hide();
            ');

            session()->flash('success', __('Status updated successfully'));
            $this->dispatch('statusUpdated');
        } catch (\Exception) {
            session()->flash('error', __('Error updating status'));
        }

        $this->reset(['ticketId', 'newStatus', 'statusComment', 'currentStatus']);
    }

    public function getStatusName(?string $status): string
    {
        return self::STATUSES[$status] ?? $status ?? '';
    }

    public function render()
    {
        return view('livewire.modals.status-update-modal', [
            'statusName' => $this->getStatusName($this->newStatus),
            'currentStatusName' => $this->getStatusName($this->currentStatus),
        ]);
    }
}
