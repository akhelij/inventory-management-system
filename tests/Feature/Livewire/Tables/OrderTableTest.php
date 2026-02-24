<?php

namespace Tests\Feature\Livewire\Tables;

use App\Livewire\Tables\OrderTable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderTableTest extends TestCase
{
    #[Test]
    public function renders_successfully(): void
    {
        Livewire::test(OrderTable::class)
            ->assertOk();
    }
}
