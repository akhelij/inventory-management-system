<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        Unit::insert([
            ['name' => 'Meters', 'slug' => 'meters', 'short_code' => 'm', 'user_id' => 1],
            ['name' => 'Centimeters', 'slug' => 'centimeters', 'short_code' => 'cm', 'user_id' => 1],
            ['name' => 'Piece', 'slug' => 'piece', 'short_code' => 'pc', 'user_id' => 1],
        ]);
    }
}
