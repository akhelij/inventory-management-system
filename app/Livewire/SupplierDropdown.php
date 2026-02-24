<?php

namespace App\Livewire;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class SupplierDropdown extends Component
{
    public Collection $suppliers;

    public ?int $selectedSupplier = null;

    public function mount(): void
    {
        $this->suppliers = Supplier::all()->map(fn (Supplier $supplier) => [
            'label' => $supplier->name,
            'value' => $supplier->id,
        ]);
    }

    public function render(): View
    {
        return view('livewire.supplier-dropdown');
    }
}
