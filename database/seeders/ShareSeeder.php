<?php

namespace Database\Seeders;

use App\Domain\Models\Document;
use App\Domain\Models\Share;
use App\Domain\Models\User;
use Illuminate\Database\Seeder;

class ShareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users and documents
        $users = User::all();
        $documents = Document::all();

        if ($users->isEmpty() || $documents->isEmpty()) {
            $this->command->warn('Users or Documents not found. Please run UserSeeder and DocumentSeeder first.');

            return;
        }

        // Create shares for users
        $users->each(function (User $user) use ($documents) {
            // Each user gets 1-3 shares
            $shareCount = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $shareCount; $i++) {
                $share = Share::factory()->forUser($user)->create();

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
        Share::factory()
            ->count(3)
            ->expired()
            ->create();

        Share::factory()
            ->count(5)
            ->used()
            ->create();
    }
}
