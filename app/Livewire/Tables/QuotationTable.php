<?php

namespace App\Livewire\Tables;

use App\Models\Quotation;
use Livewire\Component;
use Livewire\WithPagination;

class QuotationTable extends Component
{
    use WithPagination;

    public int $perPage = 25;

    public string $search = '';

    public string $sortField = 'reference';

    public bool $sortAsc = false;

    public function sortBy(string $field): void
    {
        $this->sortAsc = $this->sortField === $field ? ! $this->sortAsc : true;
        $this->sortField = $field;
    }

    public function render()
    {
        return view('livewire.tables.quotation-table', [
            'quotations' => Quotation::where('user_id', auth()->id())
                ->with(['quotationDetails', 'customer'])
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate(),
        ]);
    }
}
