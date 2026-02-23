<?php

namespace Tests;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use InteractsWithExceptionHandling, RefreshDatabase;

    public function createUser()
    {
        return User::where('email', 'admin@admin.com')->first()
            ?? User::factory()->create([
                'name' => 'admin',
                'email' => 'admin@admin.com',
            ]);
    }

    public function createProduct()
    {
        return Product::factory()->create([
            'name' => 'Test Product',
            'category_id' => $this->createCategory(),
            'unit_id' => $this->createUnit(),
        ]);
    }

    protected function createCategory()
    {
        return Category::factory()->create([
            'name' => 'Speakers',
        ]);
    }

    protected function createUnit()
    {
        return Unit::factory()->create([
            'name' => 'piece',
        ]);
    }

    public function createCustomer(?User $user = null)
    {
        $user = $user ?? User::first() ?? $this->createUser();

        return Customer::factory()->create([
            'name' => 'Customer 1',
            'user_id' => $user->id,
        ]);
    }

    public function createSupplier()
    {
        return Supplier::create([
            'name' => 'Thomann',
        ]);
    }
}
