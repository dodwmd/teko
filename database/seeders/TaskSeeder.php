<?php

namespace Database\Seeders;

use App\Models\Repository;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all repository IDs or create one if none exist
        $repositoryIds = Repository::pluck('id')->toArray();

        if (empty($repositoryIds)) {
            // Create a repository if none exist
            $repository = Repository::factory()->create();
            $repositoryIds = [$repository->id];
        }

        // Create random tasks
        foreach ($repositoryIds as $repositoryId) {
            // Create tasks with different statuses
            Task::factory()->count(3)->create([
                'repository_id' => $repositoryId,
                'status' => 'pending',
            ]);

            Task::factory()->count(2)->create([
                'repository_id' => $repositoryId,
                'status' => 'in_progress',
                'started_at' => now()->subDays(rand(1, 10)),
            ]);

            Task::factory()->count(4)->create([
                'repository_id' => $repositoryId,
                'status' => 'completed',
                'started_at' => now()->subDays(rand(5, 15)),
                'completed_at' => now()->subDays(rand(1, 4)),
            ]);

            Task::factory()->count(1)->create([
                'repository_id' => $repositoryId,
                'status' => 'failed',
                'started_at' => now()->subDays(rand(1, 10)),
            ]);
        }

        // Create a specific task for testing
        Task::factory()->create([
            'title' => 'Implement Authentication System',
            'description' => 'Set up Laravel Breeze or Jetstream for user authentication',
            'repository_id' => Repository::where('name', 'Teko')->first()?->id ?? $repositoryIds[0],
            'status' => 'completed',
            'type' => 'feature',
            'started_at' => now()->subDays(7),
            'completed_at' => now()->subDays(2),
        ]);
    }
}
