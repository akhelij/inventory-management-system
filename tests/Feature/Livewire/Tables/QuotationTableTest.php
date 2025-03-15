<?php

namespace Tests\Feature\Livewire\Tables;

use App\Livewire\Tables\QuotationTable;
use Livewire\Livewire;
use Tests\TestCase;

class QuotationTableTest extends TestCase
{
    /** @test */
    public function renders_successfully(): void
    {
        Livewire::test(QuotationTable::class)
            ->assertStatus(200);
    }
}
