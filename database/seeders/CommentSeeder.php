<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tasks or create one if none exist
        $tasks = Task::all();

        if ($tasks->isEmpty()) {
            $this->command->info('No tasks found. Please run the TaskSeeder first.');

            return;
        }

        // Get a user for comment authorship
        $user = User::first();
        if (! $user) {
            $user = User::factory()->create();
        }

        // Create comments for each task
        foreach ($tasks as $task) {
            // Create parent comments
            $comments = Comment::factory()->count(rand(1, 5))->create([
                'commentable_id' => $task->id,
                'commentable_type' => Task::class,
                'user_id' => $user->id,
            ]);

            // Add some replies to existing comments
            foreach ($comments as $comment) {
                if (rand(0, 1)) { // 50% chance of having replies
                    Comment::factory()->count(rand(1, 3))->create([
                        'commentable_id' => $task->id,
                        'commentable_type' => Task::class,
                        'user_id' => $user->id,
                        'parent_id' => $comment->id,
                    ]);
                }
            }
        }

        // Create a specific comment for testing
        $completedTask = Task::where('status', 'completed')->first();
        if ($completedTask) {
            Comment::factory()->create([
                'commentable_id' => $completedTask->id,
                'commentable_type' => Task::class,
                'user_id' => $user->id,
                'content' => 'This task has been completed successfully. All acceptance criteria met.',
            ]);
        }
    }
}
