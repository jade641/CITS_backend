<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Support\AuthorizationMatrix;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin User
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@cyberincidentsystem.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'phone' => '+1234567890',
                'department' => 'Security Operations Center',
                'job_title' => 'Chief Information Security Officer',
                'status' => 'active',
                'role' => 'Admin',
                'email_verified_at' => now(),
            ]
        );
        $adminRole = Role::query()->where('slug', AuthorizationMatrix::ADMINISTRATOR)->firstOrFail();
        $admin->roles()->sync([$adminRole->id]);

        // 2. Create Security Analyst User
        $analyst = User::query()->updateOrCreate(
            ['email' => 'analyst@cyberincidentsystem.com'],
            [
                'name' => 'Analyst User',
                'password' => Hash::make('password'),
                'phone' => '+1234567891',
                'department' => 'Incident Response Team',
                'job_title' => 'Senior Incident Responder',
                'status' => 'active',
                'role' => 'Analyst',
                'email_verified_at' => now(),
            ]
        );
        $analystRole = Role::query()->where('slug', AuthorizationMatrix::SECURITY_ANALYST)->firstOrFail();
        $analyst->roles()->sync([$analystRole->id]);

        // 3. Create Regular User
        $user = User::query()->updateOrCreate(
            ['email' => 'user@cyberincidentsystem.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'),
                'phone' => '+1234567892',
                'department' => 'Human Resources',
                'job_title' => 'HR Specialist',
                'status' => 'active',
                'role' => 'Analyst', // Set to Analyst to allow incident reporting/handling or Analyst
                'email_verified_at' => now(),
            ]
        );
        $userRole = Role::query()->where('slug', AuthorizationMatrix::USER)->firstOrFail();
        $user->roles()->sync([$userRole->id]);
    }
}
