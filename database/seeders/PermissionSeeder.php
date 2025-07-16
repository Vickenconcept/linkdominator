<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = ['FE','OTO1','OTO2','OTO3','OTO4','OTO5','OTO6','OTO7','OTO8','Bundle','view_user_manager_menu','view_permissions_menu'];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate([
                'name' => $permission
            ],
            ['name' => $permission]);
        }
    }
}
