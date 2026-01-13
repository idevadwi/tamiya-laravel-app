<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create deva account with ADMINISTRATOR role
        $deva = User::firstOrCreate(
            ['email' => 'deva@race-lane.com'],
            [
                'id' => Str::uuid(),
                'phone' => '085792440099',
                'password' => Hash::make('deva'),
            ]
        );

        // Assign ADMINISTRATOR role
        $adminRole = Role::where('role_name', 'ADMINISTRATOR')->first();
        if ($adminRole && !$deva->roles()->where('role_name', 'ADMINISTRATOR')->exists()) {
            $deva->roles()->attach($adminRole->id);
        }
    }
}
