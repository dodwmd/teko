<?php

namespace Database\Seeders;

use App\Models\Log;
use App\Models\Task;
use Illuminate\Database\Seeder;

class LogSeeder extends Seeder
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

        // Create log entries for completed and failed tasks
        foreach ($tasks as $task) {
            if ($task->status === 'completed' || $task->status === 'failed') {
                // Create 3-5 log entries for each task
                for ($i = 0; $i < rand(3, 5); $i++) {
                    Log::create([
                        'task_id' => $task->id,
                        'level' => $this->getRandomLogLevel($task->status),
                        'message' => $this->getRandomLogMessage($task->status, $i),
                        'context' => json_encode([
                            'timestamp' => now()->subMinutes(rand(5, 500))->toIso8601String(),
                            'request_id' => 'req_'.uniqid(),
                            'metadata' => [
                                'repository' => $task->repository->name ?? 'unknown',
                                'task_type' => $task->type,
                            ],
                        ]),
                        'created_at' => now()->subMinutes(rand(5, 500)),
                    ]);
                }
            }
        }
    }

    /**
     * Get a random log level based on task status
     */
    private function getRandomLogLevel(string $status): string
    {
        if ($status === 'failed') {
            return collect(['error', 'warning', 'critical'])->random();
        }

        return collect(['info', 'debug', 'notice'])->random();
    }

    /**
     * Get a random log message based on task status and position
     */
    private function getRandomLogMessage(string $status, int $position): string
    {
        $completedMessages = [
            'Task started processing',
            'Task dependencies installed successfully',
            'Task execution completed',
            'Task validation passed',
            'Task results uploaded successfully',
        ];

        $failedMessages = [
            'Task started processing',
            'Error encountered during execution',
            'Failed to validate task outputs',
            'Exception thrown during processing',
            'Task execution aborted',
        ];

        $messages = ($status === 'completed') ? $completedMessages : $failedMessages;

        // If position exists in array, return that message, otherwise return a random one
        return $messages[$position] ?? collect($messages)->random();
    }
}
