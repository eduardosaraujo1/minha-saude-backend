<?php

namespace Database\Seeders;

use App\Models\Export;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Export::factory()->count(10)->create();
    }
}
