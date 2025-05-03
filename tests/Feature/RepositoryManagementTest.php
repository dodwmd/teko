<?php

namespace Tests\Feature;

use App\Models\Repository;
use App\Models\User;
use App\Orchid\Screens\Repository\RepositoryEditScreen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Orchid\Platform\Models\Role;
use Tests\TestCase;
use Tests\TestHelpers\OrchidScreenMock;

class RepositoryManagementTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    private function createAdminUser()
    {
        $user = User::factory()->create();

        // Create and assign admin role using Orchid's system
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'permissions' => [
                'platform.index' => true,
                'platform.systems' => true,
                'platform.repositories' => true,
            ],
        ]);

        $user->addRole($adminRole);

        return $user;
    }

    public function test_repository_list_screen_can_be_rendered()
    {
        $this->withoutExceptionHandling();

        // Share errors with the view to avoid the undefined variable error
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);

        $user = $this->createAdminUser();

        $response = $this->actingAs($user)
            ->get(route('platform.repository.list'));

        $response->assertOk();
    }

    public function test_repository_can_be_created()
    {
        $this->withoutExceptionHandling();

        $user = $this->createAdminUser();

        $repositoryData = [
            'repository' => [
                'name' => 'Test Repository',
                'url' => 'https://github.com/test/repo',
                'provider' => 'github',
                'default_branch' => 'main',
                'language' => 'php',
                'description' => 'Test repository description',
                'active' => true,
            ],
        ];

        // Mock the Orchid Screen form submission
        $result = OrchidScreenMock::mockFormSubmission(
            RepositoryEditScreen::class,
            'save',
            $repositoryData
        );

        // Check that a repository was created in the database
        $this->assertDatabaseHas('repositories', [
            'name' => 'Test Repository',
            'url' => 'https://github.com/test/repo',
            'provider' => 'github',
        ]);
    }

    public function test_repository_can_be_updated()
    {
        $this->withoutExceptionHandling();

        $user = $this->createAdminUser();

        $repository = Repository::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original description',
            'url' => 'https://github.com/original/repo',
            'provider' => 'github',
            'default_branch' => 'main',
            'language' => 'php',
        ]);

        // Mock the Orchid Screen form submission with all required fields
        $result = OrchidScreenMock::mockFormSubmission(
            RepositoryEditScreen::class,
            'save',
            [
                'id' => $repository->id,
                'repository' => [
                    'id' => $repository->id,
                    'name' => 'Updated Name',
                    'url' => 'https://github.com/original/repo', // Include required fields
                    'provider' => 'github',
                    'default_branch' => 'main',
                    'language' => 'php',
                    'description' => 'Updated description',
                    'active' => true,
                ],
            ]
        );

        // Check that the repository was updated in the database
        $this->assertDatabaseHas('repositories', [
            'id' => $repository->id,
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);
    }
}
