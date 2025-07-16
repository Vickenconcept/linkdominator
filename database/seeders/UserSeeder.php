<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'John Doe',
            'email' => 'psource112@gmail.com',
            'password' => bcrypt('success'),
            'email_verified_at' => now(),
        ])->assignRole('User');
    }
}
