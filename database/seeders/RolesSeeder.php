<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'caisse@pizzapich.fr'],
            [
                'name' => 'Caisse',
                'password' => Hash::make(Str::random(32)),
                'role' => 'Caisse',
            ]
        );

        User::updateOrCreate(
            ['email' => 'cuisine@pizzapich.fr'],
            [
                'name' => 'Cuisine',
                'password' => Hash::make(Str::random(32)),
                'role' => 'Cuisine',
            ]
        );
    }
}
