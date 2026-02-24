<?php

namespace Tests\Feature\Livewire\Tables;

use App\Livewire\Tables\QuotationTable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuotationTableTest extends TestCase
{
    #[Test]
    public function renders_successfully(): void
    {
        Livewire::test(QuotationTable::class)
            ->assertOk();
    }
}
