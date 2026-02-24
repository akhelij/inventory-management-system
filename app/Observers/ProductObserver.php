<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function updating(Product $product): void {}
}
