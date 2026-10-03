<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Staff Underwriting
        User::updateOrCreate(
            ['email' => 'staff123@gmail.com'],
            [
                'name' => 'Staff Underwriting',
                'password' => Hash::make('123abcde'),
                'role' => 'staff', 
            ]
        );

        // 2. Akun Kepala Staff (Supervisor - Default 1 Akun)
        User::updateOrCreate(
            ['email' => 'kepstaff123@gmail.com'],
            [
                'name' => 'Kepala Staff',
                'password' => Hash::make('123abcde'),
                'role' => 'kepala_staff', 
            ]
        );
    }
}