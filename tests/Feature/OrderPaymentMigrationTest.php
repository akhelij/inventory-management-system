<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderPaymentMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_payment_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('order_payment'));
    }

    public function test_order_payment_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('order_payment', [
            'id',
            'order_id',
            'payment_id',
            'allocated_amount',
            'user_id',
            'created_at',
            'updated_at',
        ]));
    }
}
