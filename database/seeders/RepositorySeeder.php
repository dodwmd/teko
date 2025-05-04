<?php

namespace Database\Seeders;

use App\Models\Repository;
use Illuminate\Database\Seeder;

class RepositorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample repositories
        Repository::factory()->count(5)->create();

        // Create a specific GitHub repository
        Repository::factory()->create([
            'name' => 'Teko',
            'url' => 'https://github.com/dodwmd/teko',
            'provider' => 'github',
            'default_branch' => 'main',
            'language' => 'PHP',
            'languages' => ['PHP' => 65, 'JavaScript' => 25, 'CSS' => 10],
            'description' => 'Teko is a Laravel-based application for task management and monitoring',
            'active' => true,
        ]);
    }
}
