<?php

namespace Database\Factories;

use App\Models\Repository;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'repository_id' => Repository::factory(),
            'external_id' => $this->faker->regexify('[A-Z]{3}-[0-9]{3}'),
            'external_url' => $this->faker->url(),
            'provider' => $this->faker->randomElement(['github', 'jira']),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'review', 'completed']),
            'type' => $this->faker->randomElement(['feature', 'bug', 'chore', 'refactor']),
            'branch_name' => 'feature/'.$this->faker->slug(),
            'pull_request_url' => null,
            'metadata' => json_encode([
                'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
                'story_points' => $this->faker->numberBetween(1, 8),
                'complexity' => $this->faker->randomElement(['simple', 'medium', 'complex']),
            ]),
            'started_at' => $this->faker->optional(0.7)->dateTimeThisMonth(),
            'completed_at' => null,
        ];
    }

    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'completed_at' => $this->faker->dateTimeThisMonth(),
                'pull_request_url' => 'https://github.com/org/repo/pull/'.$this->faker->numberBetween(1, 500),
            ];
        });
    }

    public function inProgress(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'in_progress',
                'started_at' => $this->faker->dateTimeThisMonth(),
            ];
        });
    }
}
