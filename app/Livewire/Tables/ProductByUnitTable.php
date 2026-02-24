<?php

namespace App\Livewire\Tables;

use App\Models\Product;
use App\Models\Unit;
use Livewire\Component;
use Livewire\WithPagination;

class ProductByUnitTable extends Component
{
    use WithPagination;

    public int $perPage = 25;

    public string $search = '';

    public string $sortField = 'name';

    public bool $sortAsc = true;

    public Unit $unit;

    public function sortBy(string $field): void
    {
        $this->sortAsc = $this->sortField === $field ? ! $this->sortAsc : true;
        $this->sortField = $field;
    }

    public function mount(Unit $unit): void
    {
        $this->unit = $unit;
    }

    public function render()
    {
        return view('livewire.tables.product-by-unit-table', [
            'products' => Product::where('unit_id', $this->unit->id)
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate($this->perPage),
        ]);
    }
}
