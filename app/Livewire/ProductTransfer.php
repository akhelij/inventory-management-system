<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Support\Collection;
use Livewire\Component;

class ProductTransfer extends Component
{
    public Product $product;

    public int $transferQuantity = 0;

    public ?int $destinationWarehouseId = null;

    public ?int $destinationProductId = null;

    public string $productSearch = '';

    public Collection $productMatches;

    public Collection $warehouses;

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->warehouses = Warehouse::query()
            ->where('id', '!=', $product->warehouse_id)
            ->orderBy('name')
            ->get(['id', 'name']);
        $this->productMatches = collect();
    }

    public function updatedDestinationWarehouseId(): void
    {
        $this->destinationProductId = null;
        $this->productSearch = '';
        $this->refreshMatches(initial: true);
    }

    public function updatedProductSearch(): void
    {
        $this->refreshMatches();
    }

    private function refreshMatches(bool $initial = false): void
    {
        if (! $this->destinationWarehouseId) {
            $this->productMatches = collect();

            return;
        }

        $query = Product::query()
            ->where('warehouse_id', $this->destinationWarehouseId)
            ->where('id', '!=', $this->product->id);

        if (filled($this->productSearch)) {
            $term = $this->productSearch;
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', '%'.$term.'%')
                    ->orWhere('name', 'like', '%'.$term.'%');
            });
        }

        $candidates = $query->limit(50)->get(['id', 'name', 'code', 'quantity']);

        $sourceCode = (string) $this->product->code;
        $ranked = $candidates
            ->map(function (Product $p) use ($sourceCode) {
                $p->similarity = $sourceCode === '' ? PHP_INT_MAX : levenshtein($sourceCode, (string) $p->code);

                return $p;
            })
            ->sortBy('similarity')
            ->values()
            ->take(10);

        $this->productMatches = $ranked;

        if ($initial && $ranked->isNotEmpty()) {
            $this->destinationProductId = $ranked->first()->id;
        }
    }

    public function transfer(): void
    {
        $this->validate([
            'transferQuantity' => 'required|integer|min:1|max:'.$this->product->quantity,
            'destinationWarehouseId' => 'required|exists:warehouses,id|different:'.$this->product->warehouse_id,
            'destinationProductId' => 'required|integer|exists:products,id|different:'.$this->product->id,
        ]);

        $destination = Product::where('id', $this->destinationProductId)
            ->where('warehouse_id', $this->destinationWarehouseId)
            ->firstOrFail();

        $reason = sprintf(
            'Transfer to %s → %s',
            $destination->warehouse?->name ?? '#'.$destination->warehouse_id,
            $destination->code
        );

        app(StockService::class)->transferStock($this->product, $destination, $this->transferQuantity, $reason);

        $this->product->refresh();
        $newQty = $this->product->quantity;

        $this->reset(['transferQuantity', 'destinationWarehouseId', 'destinationProductId', 'productSearch']);
        $this->productMatches = collect();

        $this->js('
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById("transferModal"));
            modal.hide();

            document.querySelectorAll(".modal-backdrop").forEach(backdrop => backdrop.remove());

            document.body.classList.remove("modal-open");
            document.body.style.overflow = "";
            document.body.style.paddingRight = "";

            const qtyEl = document.getElementById("quantity");
            if (qtyEl) qtyEl.value = "'.$newQty.'";

            const alertHtml = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    '.__('Stock transferred successfully').'
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            const target = document.getElementById("stock-message");
            if (target) {
                target.innerHTML = alertHtml;
                setTimeout(() => {
                    const alert = target.querySelector(".alert");
                    if (alert) alert.remove();
                }, 5000);
            }
        ');
    }

    public function render()
    {
        return view('livewire.product-transfer');
    }
}
