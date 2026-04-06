<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@printworks.lk');

        User::updateOrCreate(
            ['role' => 'admin'],
            [
                'name' => 'Admin',
                'email' => $adminEmail,
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
    }
}
