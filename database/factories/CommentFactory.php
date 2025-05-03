<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        $task = Task::factory()->create();

        return [
            'commentable_id' => $task->id,
            'commentable_type' => Task::class,
            'user_id' => User::factory(),
            'content' => $this->faker->paragraph(),
            'external_id' => $this->faker->optional(0.5)->uuid(),
            'synced_at' => $this->faker->optional(0.5)->dateTimeThisMonth(),
            'metadata' => json_encode([
                'ip_address' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
            ]),
        ];
    }

    public function forTask(?Task $task = null): self
    {
        return $this->state(function (array $attributes) use ($task) {
            if (! $task) {
                $task = Task::factory()->create();
            }

            return [
                'commentable_id' => $task->id,
                'commentable_type' => Task::class,
            ];
        });
    }

    public function syncedWithExternal(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'external_id' => $this->faker->uuid(),
                'synced_at' => $this->faker->dateTimeThisMonth(),
            ];
        });
    }
}
