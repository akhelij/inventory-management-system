<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class ProductRefill extends Component
{
    public Product $product;

    public int $refillQuantity = 0;

    protected array $rules = [
        'refillQuantity' => 'required|numeric|min:1',
    ];

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function refillStock(): void
    {
        $this->validate();

        $this->product->quantity += $this->refillQuantity;
        $this->product->save();

        $this->js('
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById("refillModal"));
            modal.hide();

            document.querySelectorAll(".modal-backdrop").forEach(backdrop => backdrop.remove());

            document.body.classList.remove("modal-open");
            document.body.style.overflow = "";
            document.body.style.paddingRight = "";

            document.getElementById("quantity").value = "'.$this->product->quantity.'";

            const alertHtml = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    '.__('Stock has been refilled successfully').'
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            document.getElementById("stock-message").innerHTML = alertHtml;

            setTimeout(() => {
                const alert = document.getElementById("stock-message").querySelector(".alert");
                if (alert) alert.remove();
            }, 5000);
        ');

        $this->reset(['refillQuantity']);
    }

    public function render()
    {
        return view('livewire.product-refill');
    }
}
