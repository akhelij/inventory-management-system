<?php

namespace App\Livewire\Tables;

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerTable extends Component
{
    use WithPagination;

    public int $perPage = 15;

    public string $search = '';

    public string $sortField = 'name';

    public bool $sortAsc = false;

    public string $category = '';

    public function sortBy(string $field): void
    {
        $this->sortAsc = $this->sortField === $field ? ! $this->sortAsc : true;
        $this->sortField = $field;
    }

    public function render()
    {
        $query = Customer::ofAuth()
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->with('orders', 'payments', 'user')
            ->search($this->search)
            ->get();

        if (request()->input('only_out_of_limit')) {
            $query = $query->where('is_out_of_limit', true);
        }

        if (request()->input('only_unpaid')) {
            $query = $query->where('have_unpaid_checks', true);
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $query->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();
        $customers = new LengthAwarePaginator($currentItems, $query->count(), $this->perPage, $currentPage);

        return view('livewire.tables.customer-table', [
            'customers' => $customers,
        ]);
    }
}
