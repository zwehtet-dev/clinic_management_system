<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@t2i.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create staff users
        User::create([
            'name' => 'Dr. Sarah Johnson',
            'email' => 'sarah@clinic.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Nurse Mary Wilson',
            'email' => 'mary@clinic.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Pharmacist John Smith',
            'email' => 'john@clinic.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Receptionist Lisa Brown',
            'email' => 'lisa@clinic.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }
}