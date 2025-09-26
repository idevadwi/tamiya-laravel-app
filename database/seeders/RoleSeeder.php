<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['racer', 'moderator', 'administrator'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['role_name' => $role]);
        }
    }
}
