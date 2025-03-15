<?php

namespace App\Livewire\Tables;

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

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
            $this->sortAsc = ! $this->sortAsc;
        } else {
            $this->sortAsc = true;
        }

        $this->sortField = $field;
    }

    public function render()
    {
        $query = Customer::ofAuth()
            ->with('orders', 'payments')
            ->search($this->search)
            ->get();

        if (request()->input('only_out_of_limit')) {
            $query = $query->where('is_out_of_limit', true);
        }

        if (request()->input('only_unpaid')) {
            $query = $query->where('have_unpaid_checks', true);
        }

        // Manually create a paginator for the filtered customers
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $query->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();
        $customers = new LengthAwarePaginator($currentItems, $query->count(), $this->perPage, $currentPage);

        return view('livewire.tables.customer-table', [
            'customers' => $customers,
        ]);
    }
}
