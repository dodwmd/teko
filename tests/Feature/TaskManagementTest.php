<?php

namespace Tests\Feature;

use App\Models\Repository;
use App\Models\Task;
use App\Models\User;
use App\Orchid\Screens\Task\TaskEditScreen;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Orchid\Platform\Models\Role;
use Tests\TestCase;
use Tests\TestHelpers\OrchidScreenMock;

class TaskManagementTest extends TestCase
{
    use WithoutMiddleware;

    private function createAdminUser()
    {
        // Find the pre-existing admin role created by seeders
        $adminRole = Role::where('slug', 'admin')->firstOrFail();

        // Create a new user using the factory
        $user = User::factory()->create();

        // Assign the found admin role to the new user
        $user->addRole($adminRole);

        return $user;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_task_list_screen_can_be_rendered()
    {
        $this->withoutExceptionHandling();

        // Share errors with the view to avoid the undefined variable error
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);

        $user = $this->createAdminUser();

        $response = $this->actingAs($user)
            ->get(route('platform.task.list'));

        $response->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_task_can_be_created()
    {
        $this->withoutExceptionHandling();

        $user = $this->createAdminUser();
        $repository = Repository::factory()->create();

        $taskData = [
            'task' => [
                'title' => 'Test Task',
                'description' => 'This is a test task',
                'repository_id' => $repository->id,
                'external_id' => 'EXT-123',
                'external_url' => 'https://github.com/test/repo/issues/123',
                'provider' => 'github',
                'status' => 'pending',
                'type' => 'feature',
            ],
        ];

        // Mock the Orchid Screen form submission
        $result = OrchidScreenMock::mockFormSubmission(
            TaskEditScreen::class,
            'save',
            $taskData
        );

        // Check that a task was created in the database
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'repository_id' => $repository->id,
            'status' => 'pending',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_task_status_can_be_updated()
    {
        $this->withoutExceptionHandling();

        $user = $this->createAdminUser();
        $repository = Repository::factory()->create();
        $task = Task::factory()->create([
            'repository_id' => $repository->id,
            'title' => 'Original Title',
            'description' => 'Original description',
            'status' => 'pending',
            'type' => 'feature',
        ]);

        // Mock the Orchid Screen form submission with all required fields
        $result = OrchidScreenMock::mockFormSubmission(
            TaskEditScreen::class,
            'save',
            [
                'id' => $task->id,
                'task' => [
                    'id' => $task->id,
                    'title' => 'Original Title',  // Include title as it's required
                    'description' => 'Original description',  // Include description as it might be required
                    'repository_id' => $repository->id,  // Include repository_id as it might be required
                    'status' => 'in_progress',  // The field we want to update
                    'type' => 'feature',  // Required field
                ],
            ]
        );

        // Check that the task was updated in the database
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
        ]);
    }
}
