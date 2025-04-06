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
            $this->sortAsc = ! $this->sortAsc;
        } else {
            $this->sortAsc = true;
        }

        $this->sortField = $field;
    }

    public function render()
    {
        return view('livewire.tables.repair-ticket-table', [
            'tickets' => RepairTicket::with(['customer', 'product', 'technician', 'creator'])
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate($this->perPage),
        ]);
    }
}
