<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssignPermissionToRoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = Permission::all();

        Role::whereIn('name', ['admin', 'magasinier', 'commercial'])
            ->get()
            ->each(fn (Role $role) => $role->syncPermissions($permissions));
    }
}
