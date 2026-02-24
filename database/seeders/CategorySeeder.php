<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Laptops', 'slug' => 'laptops', 'user_id' => 1],
            ['id' => 2, 'name' => 'Hardware', 'slug' => 'hardware', 'user_id' => 1],
            ['id' => 3, 'name' => 'Smartphones', 'slug' => 'smartphones', 'user_id' => 1],
            ['id' => 4, 'name' => 'Speakers', 'slug' => 'speakers', 'user_id' => 1],
            ['id' => 5, 'name' => 'Software', 'slug' => 'software', 'user_id' => 1],
        ];

        Category::insert($categories);
    }
}
