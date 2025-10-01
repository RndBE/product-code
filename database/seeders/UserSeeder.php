<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat admin default
        User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('password123'),
                'is_admin' => true, // tanda user ini admin
            ]
        );

        // Bisa tambahkan user lain
        // User::factory()->count(10)->create();
    }
}
