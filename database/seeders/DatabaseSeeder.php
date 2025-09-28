<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Prevent model events during seeding for performance
        $this->call([
            UserSeeder::class,
            AdminSeeder::class,
            DocumentSeeder::class,
            ShareSeeder::class,
        ]);
    }
}
