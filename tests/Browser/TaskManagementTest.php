<?php

namespace Tests\Browser;

use App\Models\Repository;
use App\Models\Task;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\AdminLogin;

class TaskManagementTest extends AdminTestCase
{
    use DatabaseMigrations;

    /**
     * Test the task list screen loads and displays tasks
     */
    public function test_task_list_displays_tasks(): void
    {
        // Create test data
        $adminUser = $this->createAdminUser();
        $repository = Repository::factory()->create();
        Task::factory()->count(3)->create(['repository_id' => $repository->id]);

        $this->browse(function (Browser $browser) {
            $browser->visit(new AdminLogin)
                ->login()
                ->pause(1500)
                ->waitForText('Dashboard', 10)
                ->safeClick('a:contains("Tasks")')
                ->pause(1500)
                ->waitForText('Tasks', 10)
                ->assertPresent('.table');
        });
    }

    /**
     * Test the task filtering works correctly
     */
    public function test_task_filtering(): void
    {
        // Create test data
        $adminUser = $this->createAdminUser();
        $repository = Repository::factory()->create();

        // Create tasks with different statuses
        Task::factory()->create([
            'repository_id' => $repository->id,
            'title' => 'Pending Task',
            'status' => 'pending',
        ]);

        Task::factory()->create([
            'repository_id' => $repository->id,
            'title' => 'In Progress Task',
            'status' => 'in_progress',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit(new AdminLogin)
                ->login()
                ->pause(1500)
                ->waitForText('Dashboard', 10)
                ->safeClick('a:contains("Tasks")')
                ->pause(1500)
                ->waitForText('Tasks', 10)
                ->assertSee('Pending Task')
                ->assertSee('In Progress Task')
                ->whenAvailable('select[name="filter[status]"]', function ($select) {
                    $select->select('pending');
                })
                ->whenAvailable('button:contains("Apply")', function ($button) {
                    $button->click();
                })
                ->pause(1500)
                ->assertSee('Pending Task')
                ->assertDontSee('In Progress Task');
        });
    }

    /**
     * Test the task creation form is displayed correctly
     */
    public function test_task_creation_form(): void
    {
        // Create test data
        $adminUser = $this->createAdminUser();
        Repository::factory()->create(['name' => 'Test Repository']);

        $this->browse(function (Browser $browser) {
            $browser->visit(new AdminLogin)
                ->login()
                ->pause(1500)
                ->waitForText('Dashboard', 10)
                ->safeClick('a:contains("Tasks")')
                ->pause(1500)
                ->waitForText('Tasks', 10)
                ->whenAvailable('button:contains("Add")', function ($button) {
                    $button->click();
                })
                ->pause(1500)
                ->waitForText('Create Task', 10)
                ->assertPresent('input[name="title"]')
                ->assertPresent('select[name="repository_id"]')
                ->assertPresent('select[name="status"]');
        });
    }

    /**
     * Test the task edit form is displayed correctly
     */
    public function test_task_edit_form(): void
    {
        // Create test data
        $adminUser = $this->createAdminUser();
        $repository = Repository::factory()->create();
        $task = Task::factory()->create([
            'repository_id' => $repository->id,
            'title' => 'Test Task',
            'status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit(new AdminLogin)
                ->login()
                ->pause(1500)
                ->waitForText('Dashboard', 10)
                ->safeClick('a:contains("Tasks")')
                ->pause(1500)
                ->waitForText('Tasks', 10)
                ->whenAvailable('a:contains("Test Task")', function ($link) {
                    $link->click();
                })
                ->pause(1500)
                ->waitForText('Edit Task', 10)
                ->assertInputValue('title', 'Test Task')
                ->assertSelected('status', 'pending');
        });
    }

    /**
     * Test the task comments tab functionality
     */
    public function test_task_comments_tab(): void
    {
        // Create test data
        $adminUser = $this->createAdminUser();
        $repository = Repository::factory()->create();
        $task = Task::factory()->create([
            'repository_id' => $repository->id,
            'title' => 'Test Task',
            'status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit(new AdminLogin)
                ->login()
                ->pause(1500)
                ->waitForText('Dashboard', 10)
                ->safeClick('a:contains("Tasks")')
                ->pause(1500)
                ->waitForText('Tasks', 10)
                ->whenAvailable('a:contains("Test Task")', function ($link) {
                    $link->click();
                })
                ->pause(1500)
                ->waitForText('Edit Task', 10)
                ->whenAvailable('a:contains("Comments")', function ($link) {
                    $link->click();
                })
                ->pause(1500)
                ->waitForText('Comments', 10)
                ->assertPresent('textarea[name="comment"]')
                ->assertPresent('button[type="submit"]');
        });
    }
}
