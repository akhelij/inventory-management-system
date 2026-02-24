<?php

namespace Tests;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function createUser(): User
    {
        return User::where('email', 'admin@admin.com')->first()
            ?? User::factory()->create([
                'name' => 'admin',
                'email' => 'admin@admin.com',
            ]);
    }

    protected function createProduct(): Product
    {
        return Product::factory()->create([
            'name' => 'Test Product',
            'category_id' => $this->createCategory(),
            'unit_id' => $this->createUnit(),
        ]);
    }

    protected function createCategory(): Category
    {
        return Category::factory()->create([
            'name' => 'Speakers',
        ]);
    }

    protected function createUnit(): Unit
    {
        return Unit::factory()->create([
            'name' => 'piece',
        ]);
    }

    protected function createCustomer(?User $user = null): Customer
    {
        $user = $user ?? User::first() ?? $this->createUser();

        return Customer::factory()->create([
            'name' => 'Customer 1',
            'user_id' => $user->id,
        ]);
    }

    protected function createSupplier(): Supplier
    {
        return Supplier::create([
            'name' => 'Thomann',
        ]);
    }
}
