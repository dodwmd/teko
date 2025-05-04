<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create various agent types
        Agent::factory()->count(2)->create([
            'type' => 'analyzer',
            'language' => 'php',
        ]);

        Agent::factory()->count(1)->create([
            'type' => 'generator',
            'language' => 'javascript',
        ]);

        Agent::factory()->count(2)->create([
            'type' => 'implementation',
            'language' => 'python',
        ]);

        // Create a specific agent for testing
        Agent::factory()->create([
            'name' => 'CodeAnalyzer',
            'type' => 'analyzer',
            'description' => 'Analyzes code for potential issues and improvements',
            'language' => 'PHP',
            'enabled' => true,
            'metadata' => [
                'version' => '1.0.0',
                'capabilities' => ['static_analysis', 'code_quality', 'security_scan'],
            ],
        ]);
    }
}
