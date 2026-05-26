<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@chronosphere.local'],
            [
                'name'       => 'Super Admin',
                'password'   => Hash::make('password'),
                'language'   => 'fr',
                'theme'      => 'light',
                'timezone'   => 'Europe/Paris',
                'is_active'  => true,
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('super_admin');

        $demo = User::firstOrCreate(
            ['email' => 'demo@chronosphere.local'],
            [
                'name'       => 'Utilisateur Démo',
                'password'   => Hash::make('password'),
                'language'   => 'fr',
                'theme'      => 'light',
                'timezone'   => 'Europe/Paris',
                'is_active'  => true,
                'email_verified_at' => now(),
            ]
        );

        $demo->assignRole(['uni_teacher', 'cal_user', 'proj_member']);
    }
}
