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
        //Create crud permission for every model [User, Role, Permission, Category, Product, Unit, Customer, Order]
        $models = ['User', 'Role', 'Permission', 'Category', 'Product', 'Unit', 'Customer', 'Order'];
        foreach ($models as $model) {
            Permission::insert([
                ['name' => 'create ' . strtolower($model), 'guard_name' => 'web'],
                ['name' => 'read ' . strtolower($model), 'guard_name' => 'web'],
                ['name' => 'update ' . strtolower($model), 'guard_name' => 'web'],
                ['name' => 'delete ' . strtolower($model), 'guard_name' => 'web'],
            ]);
        }
    }
}
