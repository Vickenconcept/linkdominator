<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserActivity;

class UserActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserActivity::create([
            'module_name' => 'Profile viwed',
            'stats' => 3,
            'user_id' => 1
        ]);

        UserActivity::create([
            'module_name' => 'Profile viwed',
            'stats' => 4,
            'user_id' => 1
        ]);
    }
}
