<?php

namespace App\Livewire\Tables;

use App\Models\RepairTicket;
use Livewire\Component;
use Livewire\WithPagination;

class RepairTicketTable extends Component
{
    use WithPagination;

    public int $perPage = 25;

    public string $search = '';

    public string $sortField = 'id';

    public bool $sortAsc = false;

    public string $statusComment = '';

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function sortBy(string $field): void
    {
        $this->sortAsc = $this->sortField === $field ? ! $this->sortAsc : true;
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
