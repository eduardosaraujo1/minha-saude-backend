<?php

namespace Database\Factories;

use App\Data\Models\Document;
use App\Data\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Data\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tiposDocumento = ['Receita', 'Exame', 'Laudo', 'Atestado', 'Relatório Médico', 'Prescrição'];
        $titulos = [
            'Exame de Sangue Completo',
            'Receita Médica - Antibióticos',
            'Laudo de Raio-X Tórax',
            'Relatório de Consulta Cardiológica',
            'Atestado Médico - 3 dias',
        ];

        return [
            'titulo' => fake()->randomElement($titulos),
            'nome_paciente' => fake()->name(),
            'nome_medico' => 'Dr. '.fake()->name(),
            'tipo_documento' => fake()->randomElement($tiposDocumento),
            'data_documento' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'is_processing' => fake()->boolean(20), // 20% chance of being processed
            'caminho_arquivo' => fake()->filePath(),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the document is currently being processed.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_processing' => true,
        ]);
    }

    /**
     * Indicate that the document is for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
