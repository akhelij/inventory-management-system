<?php

namespace Tests\Feature\Livewire\Tables;

use App\Livewire\Tables\CustomerTable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerTableTest extends TestCase
{
    #[Test]
    public function renders_successfully(): void
    {
        Livewire::test(CustomerTable::class)
            ->assertOk();
    }
}
