<?php

namespace Database\Factories;

use App\Models\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;

class RepositoryFactory extends Factory
{
    protected $model = Repository::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company().' Repo',
            'url' => 'https://github.com/'.$this->faker->userName().'/'.$this->faker->slug(),
            'provider' => $this->faker->randomElement(['github', 'gitlab', 'bitbucket']),
            'default_branch' => 'main',
            'language' => $this->faker->randomElement(['php', 'python', 'javascript']),
            'languages' => json_encode(['php' => 45, 'javascript' => 35, 'css' => 20]),
            'description' => $this->faker->paragraph(),
            'metadata' => json_encode([
                'last_analyzed' => $this->faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
                'star_count' => $this->faker->numberBetween(0, 1000),
                'fork_count' => $this->faker->numberBetween(0, 200),
            ]),
            'active' => true,
        ];
    }
}
