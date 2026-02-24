<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'created_at' => now(),
                'uuid' => Str::uuid(),
            ],
            [
                'name' => 'Electro Alami',
                'email' => 'electro@alami.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'created_at' => now(),
                'uuid' => Str::uuid(),
            ],
        ];

        foreach ($users as $user) {
            User::insert($user);
        }

        $roles = ['admin', 'magasinier', 'commercial'];
        foreach (User::all() as $index => $user) {
            $user->assignRole($roles[$index]);
        }
    }
}
