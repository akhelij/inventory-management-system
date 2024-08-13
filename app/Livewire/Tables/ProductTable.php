<?php

namespace App\Livewire\Tables;

use Livewire\Component;
use App\Models\Product;
use Livewire\WithPagination;

class ProductTable extends Component
{
    use WithPagination;

    public $perPage = 25;

    public $search = '';

    public $sortField = 'id';

    public $sortAsc = false;

    public $warehouses;
    public $warehouse_id = null;

    public function sortBy($field): void
    {
        if($this->sortField === $field)
        {
            $this->sortAsc = ! $this->sortAsc;

        } else {
            $this->sortAsc = true;
        }

        $this->sortField = $field;
    }

    public function filterByWarehouse($warehouseId)
    {
        $this->warehouse_id = $warehouseId;
    }

    public function render()
    {
        return view('livewire.tables.product-table', [
            'products' => Product::with(['category', 'warehouse', 'unit'])
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->when($this->warehouse_id, function($query) {
                    return $query->where('warehouse_id', $this->warehouse_id);
                })
                ->paginate($this->perPage)
        ]);
    }
}
