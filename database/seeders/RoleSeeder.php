<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::insert([
            ['name' => 'admin', 'guard_name' => 'web'],
            ['name' => 'magasinier', 'guard_name' => 'web'],
            ['name' => 'commercial', 'guard_name' => 'web'],
        ]);
    }
}
