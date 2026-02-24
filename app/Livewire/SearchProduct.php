<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Support\Collection;
use Livewire\Component;

class SearchProduct extends Component
{
    public string $query = '';

    public Collection $search_results;

    public int $how_many = 5;

    public function mount(): void
    {
        $this->search_results = Collection::empty();
    }

    public function updatedQuery(): void
    {
        $this->search_results = Product::where('user_id', auth()->id())
            ->where('name', 'like', '%'.$this->query.'%')
            ->orWhere('code', 'like', '%'.$this->query.'%')
            ->take($this->how_many)
            ->get();
    }

    public function loadMore(): void
    {
        $this->how_many += 5;
        $this->updatedQuery();
    }

    public function resetQuery(): void
    {
        $this->query = '';
        $this->how_many = 5;
        $this->search_results = Collection::empty();
    }

    public function selectProduct(array $product): void
    {
        $this->dispatch('productSelected', $product);
    }

    public function render()
    {
        return view('livewire.search-product');
    }
}
