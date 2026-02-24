<?php

namespace App\Livewire\Tables;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductTable extends Component
{
    use WithPagination;

    public int $perPage = 25;

    public string $search = '';

    public string $sortField = 'id';

    public bool $sortAsc = false;

    public $warehouses;

    public ?int $warehouse_id = null;

    public function sortBy(string $field): void
    {
        $this->sortAsc = $this->sortField === $field ? ! $this->sortAsc : true;
        $this->sortField = $field;
    }

    public function filterByWarehouse(?int $warehouseId): void
    {
        $this->warehouse_id = $warehouseId;
    }

    public function render()
    {
        return view('livewire.tables.product-table', [
            'products' => Product::with(['category', 'warehouse', 'unit'])
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->when($this->warehouse_id, fn ($query) => $query->where('warehouse_id', $this->warehouse_id))
                ->when(auth()->user()->warehouse_id != null, fn ($q) => $q->where('warehouse_id', auth()->user()->warehouse_id))
                ->paginate($this->perPage),
        ]);
    }
}
