<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = ['Users', 'Roles & Permissions', 'Products', 'Orders', 'Categories',  'Customers'];
        foreach ($models as $model) {
            Permission::insert([
                ['name' => 'read ' . strtolower($model), 'guard_name' => 'web'],
                ['name' => 'create ' . strtolower($model), 'guard_name' => 'web'],
                ['name' => 'update ' . strtolower($model), 'guard_name' => 'web'],
                ['name' => 'delete ' . strtolower($model), 'guard_name' => 'web'],
            ]);
        }
        Permission::insert([
            ['name' => 'activity logs', 'guard_name' => 'web'],
            ['name' => 'update orders status', 'guard_name' => 'web'],
        ]);
    }
}
