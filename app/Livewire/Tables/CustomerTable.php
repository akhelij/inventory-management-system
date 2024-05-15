<?php

namespace App\Livewire\Tables;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerTable extends Component
{
    use WithPagination;

    public $perPage = 15;

    public $search = '';

    public $sortField = 'name';

    public $sortAsc = false;

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }

        $this->sortField = $field;
    }

    public function render()
    {
        $customers = Customer::where("user_id", auth()->id())
            ->with('orders', 'payments')
            ->search($this->search)
            ->get();

        if(request()->input('only_unpaid')) {
            $customers = $customers->where('is_out_of_limit', true);
        }

        // Manually create a paginator for the filtered customers
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $customers->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();
        $customers = new LengthAwarePaginator($currentItems, $customers->count(), $this->perPage, $currentPage);

        return view('livewire.tables.customer-table', [
            'customers' => $customers
        ]);
    }
}
