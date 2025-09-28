<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Create a test user for development
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'cpf' => '123.456.789-00',
        ]);

        // Create users with different authentication methods
        User::factory()->count(5)->create();

        // Create some Google users
        User::factory()->count(3)->create([
            'metodo_autenticacao' => 'google',
            'google_id' => fake()->uuid(),
        ]);
    }
}
