<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the UserSeeder, AdminSeeder, and EmployeeSeeder to safely seed users and employees from JSON data
        $this->call([
            UserSeeder::class,
            AdminSeeder::class,
            EmployeeSeeder::class,
        ]);
        
        // Keep existing factory creation but make it safe too
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'first_name' => 'Test',
                'middle_name' => '',
                'last_name' => 'User',
                'user_name' => 'testuser',
                'password' => bcrypt('password'),
                'phone_number' => '1234567890',
                'profile_photo' => null,
                'user_type' => 'user',
            ]
        );
    }
}
