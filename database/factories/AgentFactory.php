<?php

namespace Database\Factories;

use App\Models\Agent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent>
 */
class AgentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Agent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lastRun = $this->faker->boolean(70) ? $this->faker->dateTimeThisMonth()->format('Y-m-d H:i:s') : null;

        return [
            'name' => $this->faker->unique()->word.' Agent',
            'type' => $this->faker->randomElement(['implementation', 'review', 'codebase_analysis', 'scheduler']),
            'language' => $this->faker->randomElement(['php', 'python', 'javascript', 'typescript', 'java', 'go']),
            'enabled' => $this->faker->boolean(80),
            'configuration' => json_encode([
                'model' => $this->faker->randomElement(['gpt-4', 'claude-3', 'gemini-pro']),
                'temperature' => $this->faker->randomFloat(1, 0, 1),
                'max_tokens' => $this->faker->numberBetween(1000, 4000),
            ]),
            'metadata' => json_encode([
                'version' => $this->faker->semver(),
                'created_at' => $this->faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
                'last_run' => $lastRun,
            ]),
        ];
    }

    /**
     * Indicate that the agent is enabled.
     *
     * @return $this
     */
    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled' => true,
        ]);
    }

    /**
     * Indicate that the agent is disabled.
     *
     * @return $this
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled' => false,
        ]);
    }
}
