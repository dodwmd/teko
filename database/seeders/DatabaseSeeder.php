<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user is already created with the orchid:admin command
        $this->call(AdminUserSeeder::class);

        // Create a test user if it doesn't exist already
        if (! User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Seed with demo data in proper dependency order
        $this->call([
            RepositorySeeder::class,  // First create repositories
            TaskSeeder::class,        // Then create tasks that depend on repositories
            CommentSeeder::class,     // Then create comments that depend on tasks
            AgentSeeder::class,       // Create agents that depend on repositories
            LogSeeder::class,         // Finally create logs that depend on tasks
        ]);
    }
}
