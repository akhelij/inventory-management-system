<?php

namespace App\Livewire\Tables;

use App\Models\Order;
use App\Models\User;
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

    public $order_ids = [];

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

    public function selectOrder($orderId)
    {
        if(in_array($orderId, $this->order_ids))
        {
            $this->order_ids = array_diff($this->order_ids, [$orderId]);
        } else {
            $this->order_ids[] = $orderId;
        }
    }

    public function render()
    {
        $query = Order::query();
        if(!auth()->user()->hasRole('admin'))
        {
            $query->where("user_id", auth()->id())->orWhereIn("user_id", User::role('admin')->pluck('id'));
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
