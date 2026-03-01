<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private array $permissions = [
        'read clients',
        'create clients',
        'update clients',
        'delete clients',
    ];

    public function up(): void
    {
        foreach ($this->permissions as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web']
            );
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($this->permissions);
        }
    }

    public function down(): void
    {
        foreach ($this->permissions as $name) {
            Permission::where('name', $name)->where('guard_name', 'web')->delete();
        }
    }
};
