<?php

namespace Tests\Feature\Livewire\Tables;

use App\Livewire\Tables\ProductTable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductTableTest extends TestCase
{
    #[Test]
    public function renders_successfully(): void
    {
        Livewire::test(ProductTable::class)
            ->assertOk();
    }
}
