<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssignPermissionToRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = Permission::all();
        $roles = ['admin', 'magasinier', 'commercial'];

        foreach ($roles as $role) {
            $role = Role::where('name', $role)->first();
            $role->syncPermissions($permissions);
        }
    }
}
