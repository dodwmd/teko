<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Orchid\Platform\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::where('slug', 'admin')->first();

        if (! $adminRole) {
            $adminRole = Role::create([
                'slug' => 'admin',
                'name' => 'Administrator',
                'permissions' => [
                    'platform.index' => true,
                    'platform.systems' => true,
                    'platform.systems.roles' => true,
                    'platform.systems.users' => true,
                    'platform.agents.dashboard' => true,
                    'platform.agents.repository' => true,
                    'platform.agents.task' => true,
                    'platform.agents.monitoring' => true,
                ],
            ]);
        }

        // Create admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@teko.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@teko.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role to user
        $admin->addRole($adminRole);

        $this->command->info('Admin user created: admin@teko.com / admin123');
    }
}
