<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MinistatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Ministat::create([
            'connections' => 938,
            'pending_invites' => 3,
            'profile_views' => 0,
            'search_appearance' => 9,
            'user_id' => 1
        ]);
    }
}
