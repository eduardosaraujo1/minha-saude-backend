<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users and documents
        $users = \App\Models\User::all();
        $documents = \App\Models\Document::all();

        if ($users->isEmpty() || $documents->isEmpty()) {
            $this->command->warn('Users or Documents not found. Please run UserSeeder and DocumentSeeder first.');
            return;
        }

        // Create shares for users
        $users->each(function (\App\Models\User $user) use ($documents) {
            // Each user gets 1-3 shares
            $shareCount = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $shareCount; $i++) {
                $share = \App\Models\Share::factory()->forUser($user)->create();

                // Attach random documents to each share (1-4 documents per share)
                $userDocuments = $documents->where('user_id', $user->id);
                if ($userDocuments->isNotEmpty()) {
                    $randomDocuments = $userDocuments->random(
                        min(fake()->numberBetween(1, 4), $userDocuments->count())
                    );
                    $share->documents()->attach($randomDocuments->pluck('id'));
                }
            }
        });

        // Create some expired and used shares
        \App\Models\Share::factory()
            ->count(3)
            ->expired()
            ->create();

        \App\Models\Share::factory()
            ->count(5)
            ->used()
            ->create();
    }
}
