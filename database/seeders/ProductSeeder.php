<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'iPhone 14 Pro', 'slug' => 'iphone-14-pro', 'code' => 1, 'category_id' => 3],
            ['name' => 'ASUS Laptop', 'slug' => 'asus-laptop', 'code' => 2, 'category_id' => 1],
            ['name' => 'Logitech Keyboard', 'slug' => 'logitech-keyboard', 'code' => 3, 'category_id' => 2],
            ['name' => 'Logitech Speakers', 'slug' => 'logitech-speakers', 'code' => 4, 'category_id' => 4],
            ['name' => 'AutoCAD v7.0', 'slug' => 'autocad-v7.0', 'code' => 5, 'category_id' => 5],
        ];

        $defaults = [
            'quantity' => 10,
            'buying_price' => 900,
            'selling_price' => 1400,
            'quantity_alert' => 10,
            'tax' => 24,
            'tax_type' => 1,
            'notes' => null,
            'unit_id' => 3,
            'user_id' => 1,
        ];

        foreach ($products as $product) {
            Product::create(array_merge($defaults, $product, ['uuid' => Str::uuid()]));
        }
    }
}
