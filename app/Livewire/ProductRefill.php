<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class ProductRefill extends Component
{
    public $product;

    public $refillQuantity = 0;

    protected $rules = [
        'refillQuantity' => 'required|numeric|min:1',
    ];

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    public function refillStock()
    {
        $this->validate();

        $this->product->quantity += $this->refillQuantity;
        $this->product->save();

        $this->js('
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById("refillModal"));
            modal.hide();
            
            // Remove all modal-backdrop fade show elements
            const modalBackdrops = document.querySelectorAll(".modal-backdrop.fade.show");
            modalBackdrops.forEach(backdrop => {
                backdrop.remove();
            });
            
            // Also remove any other modal backdrops that might exist
            const otherBackdrops = document.querySelectorAll(".modal-backdrop");
            otherBackdrops.forEach(backdrop => {
                backdrop.remove();
            });
            
            // Restore body scrolling
            document.body.classList.remove("modal-open");
            document.body.style.overflow = "";
            document.body.style.paddingRight = "";
            
            document.getElementById("quantity").value = "'.$this->product->quantity.'";

            // Add success message
            const alertHtml = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    '.__('Stock has been refilled successfully').'
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            document.getElementById("stock-message").innerHTML = alertHtml;

            // Remove alert after 5 seconds
            setTimeout(() => {
                const alert = document.getElementById("stock-message").querySelector(".alert");
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        ');

        $this->reset(['refillQuantity']);
    }

    public function render()
    {
        return view('livewire.product-refill');
    }
}
