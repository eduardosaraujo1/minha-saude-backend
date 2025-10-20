<?php

namespace Database\Seeders;

use App\Domain\Models\Document;
use App\Domain\Models\User;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users or create some if they don't exist
        $users = User::all();

        if ($users->isEmpty()) {
            $users = User::factory()->count(5)->create();
        }

        // Create documents for each user
        $users->each(function (User $user) {
            // Each user gets 3-8 documents
            Document::factory()
                ->count(fake()->numberBetween(3, 8))
                ->forUser($user)
                ->create();

            // Some users get processing documents
            if (fake()->boolean(40)) {
                Document::factory()
                    ->forUser($user)
                    ->processing()
                    ->create();
            }
        });

        // Create some shared test documents
        Document::factory()
            ->count(5)
            ->create([
                'titulo' => fake()->randomElement([
                    'Exame de Sangue Completo',
                    'Receita Médica - Antibióticos',
                    'Laudo de Raio-X Tórax',
                    'Relatório de Consulta Cardiológica',
                    'Atestado Médico - 3 dias',
                ]),
            ]);
    }
}
