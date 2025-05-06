<?php

namespace Tests\Feature;

use App\Models\Repository;
use App\Models\User;
use App\Orchid\Screens\Repository\RepositoryEditScreen;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Orchid\Platform\Models\Role;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\TestHelpers\OrchidScreenMock;

class RepositoryManagementTest extends TestCase
{
    use WithoutMiddleware;

    private function createAdminUser()
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();

        $user = User::factory()->create();

        $user->addRole($adminRole);

        return $user;
    }

    #[Test]
    public function test_repository_list_screen_can_be_rendered()
    {
        $this->withoutExceptionHandling();

        view()->share('errors', new \Illuminate\Support\ViewErrorBag);

        $user = $this->createAdminUser();

        $response = $this->actingAs($user)
            ->get(route('platform.repository.list'));

        $response->assertOk();
    }

    #[Test]
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

        $result = OrchidScreenMock::mockFormSubmission(
            RepositoryEditScreen::class,
            'save',
            $repositoryData
        );

        $this->assertDatabaseHas('repositories', [
            'name' => 'Test Repository',
            'url' => 'https://github.com/test/repo',
            'provider' => 'github',
        ]);
    }

    #[Test]
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

        $result = OrchidScreenMock::mockFormSubmission(
            RepositoryEditScreen::class,
            'save',
            [
                'id' => $repository->id,
                'repository' => [
                    'id' => $repository->id,
                    'name' => 'Updated Name',
                    'url' => 'https://github.com/original/repo',
                    'provider' => 'github',
                    'default_branch' => 'main',
                    'language' => 'php',
                    'description' => 'Updated description',
                    'active' => true,
                ],
            ]
        );

        $this->assertDatabaseHas('repositories', [
            'id' => $repository->id,
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);
    }
}
