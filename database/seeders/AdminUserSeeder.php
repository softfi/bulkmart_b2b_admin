<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
      User::UpdateOrCreate(
        ['email' => 'admin@admin.com'],
        [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ] 
      );
    }
}
