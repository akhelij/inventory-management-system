<?php

namespace App\Livewire\Tables;

use App\Models\Purchase;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseTable extends Component
{
    use WithPagination;

    public int $perPage = 25;

    public string $search = '';

    public string $sortField = 'purchase_no';

    public bool $sortAsc = false;

    public function sortBy(string $field): void
    {
        $this->sortAsc = $this->sortField === $field ? ! $this->sortAsc : true;
        $this->sortField = $field;
    }

    public function render()
    {
        return view('livewire.tables.purchase-table', [
            'purchases' => Purchase::where('user_id', auth()->id())
                ->with('supplier')
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate($this->perPage),
        ]);
    }
}
