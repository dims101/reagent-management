<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'name' => 'Administrator',
                'nup' => 'administrator',
                'password' => Hash::make('admin123'),
                'role_id' => 1,
            ]
        );
    }
}
