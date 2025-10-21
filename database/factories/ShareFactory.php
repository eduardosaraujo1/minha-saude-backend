<?php

namespace Database\Factories;

use App\Data\Models\Share;
use App\Data\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Data\Models\Share>
 */
class ShareFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Share::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => Str::random(8),
            'data_primeiro_uso' => fake()->optional(0.6)->dateTimeBetween('-1 month', 'now'),
            'expirado' => fake()->boolean(30), // 30% chance of being expired
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the share has been used.
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_primeiro_uso' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the share is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expirado' => true,
        ]);
    }

    /**
     * Indicate that the share is for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
