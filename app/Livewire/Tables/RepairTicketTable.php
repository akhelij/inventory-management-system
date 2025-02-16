<?php

namespace App\Livewire\Tables;

use App\Models\RepairTicket;
use Livewire\Component;
use Livewire\WithPagination;

class RepairTicketTable extends Component
{
    use WithPagination;

    public $perPage = 25;
    public $search = '';
    public $sortField = 'id';
    public $sortAsc = false;
    public $statusComment = '';

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }

        $this->sortField = $field;
    }

    public function updateStatus($status, $ticketId)
    {
        try {
            $ticket = RepairTicket::findOrFail($ticketId);

            // Validate the status is one of the allowed values
            if (!in_array($status, [
                'RECEIVED',
                'IN_PROGRESS',
                'REPAIRED',
                'UNREPAIRABLE',
                'DELIVERED'
            ])) {
                throw new \Exception('Invalid status');
            }

            // Update status
            $ticket->update([
                'status' => $status
            ]);

            // Show success message
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Status updated successfully'
            ])->to(null);

        } catch (\Exception $e) {
            // Show error message
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error updating status'
            ])->to(null);
        }
    }

    public function render()
    {
        return view('livewire.tables.repair-ticket-table', [
            'tickets' => RepairTicket::with(['customer', 'product', 'technician', 'creator'])
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate($this->perPage)
        ]);
    }
}
