<?php

namespace App\Livewire\Tables;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrderTable extends Component
{
    use WithPagination;

    public $perPage = 25;

    public $search = '';

    public $sortField = 'invoice_no';

    public $sortAsc = false;

    public $startDate = null;
    public $endDate = null;

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

    public function render()
    {
        $query = Order::query();
        if(!auth()->user()->hasRole('admin'))
        {
            $query->where("user_id", auth()->id());
        }

        if($this->startDate && $this->endDate)
        {
            $query->whereBetween('order_date', [$this->startDate, $this->endDate]);
        }

        return view('livewire.tables.order-table', [
            'orders' => $query->with(['customer', 'details'])
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate($this->perPage)
        ]);
    }
}
