<?php

namespace Tests\Browser;

use App\Models\Comment;
use App\Models\Repository;
use App\Models\Task;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\AdminLogin;

class CommentSystemTest extends AdminTestCase
{
    use DatabaseMigrations;

    /**
     * Test adding a comment to a task
     */
    public function test_adding_comment_to_task(): void
    {
        // Create test data
        $admin = $this->createAdminUser();
        $repository = Repository::factory()->create(['name' => 'Test Repository']);
        $task = Task::factory()->create([
            'repository_id' => $repository->id,
            'title' => 'Task with Comments',
            'status' => 'in_progress',
        ]);

        $this->browse(function (Browser $browser) use ($task) {
            $browser->visit(new AdminLogin)
                ->login()
                ->clickLink('Tasks')
                ->waitForLocation('/admin/tasks')
                ->clickLink('Task with Comments')
                ->waitForLocation('/admin/tasks/'.$task->id)
                    // Switch to comments tab
                ->click('a[href="#tab-comments"]')
                ->waitFor('#tab-comments')
                    // Add a new comment
                ->type('content', 'This is a test comment added via browser test')
                ->press('Add Comment')
                ->waitForText('This is a test comment added via browser test')
                ->assertSee('This is a test comment added via browser test')
                ->screenshot('comment-added');
        });
    }

    /**
     * Test viewing existing comments
     */
    public function test_viewing_existing_comments(): void
    {
        // Create test data
        $admin = $this->createAdminUser();
        $repository = Repository::factory()->create(['name' => 'Test Repository']);
        $task = Task::factory()->create([
            'repository_id' => $repository->id,
            'title' => 'Task with Existing Comments',
            'status' => 'in_progress',
        ]);

        // Create some comments
        Comment::factory()->count(3)->create([
            'commentable_id' => $task->id,
            'commentable_type' => Task::class,
            'user_id' => $admin->id,
        ]);

        $this->browse(function (Browser $browser) use ($task) {
            $browser->visit(new AdminLogin)
                ->login()
                ->clickLink('Tasks')
                ->waitForLocation('/admin/tasks')
                ->clickLink('Task with Existing Comments')
                ->waitForLocation('/admin/tasks/'.$task->id)
                    // Switch to comments tab
                ->click('a[href="#tab-comments"]')
                ->waitFor('#tab-comments')
                ->assertPresent('.comment-list')
                ->assertPresent('.comment-item')
                ->assertCountInElement('.comment-item', 3)
                ->screenshot('comment-list');
        });
    }

    /**
     * Test replying to a comment
     */
    public function test_replying_to_comment(): void
    {
        // Create test data
        $admin = $this->createAdminUser();
        $repository = Repository::factory()->create(['name' => 'Test Repository']);
        $task = Task::factory()->create([
            'repository_id' => $repository->id,
            'title' => 'Task for Reply Test',
            'status' => 'in_progress',
        ]);

        // Create a parent comment
        $comment = Comment::factory()->create([
            'commentable_id' => $task->id,
            'commentable_type' => Task::class,
            'user_id' => $admin->id,
            'content' => 'Parent comment',
        ]);

        $this->browse(function (Browser $browser) use ($task) {
            $browser->visit(new AdminLogin)
                ->login()
                ->clickLink('Tasks')
                ->waitForLocation('/admin/tasks')
                ->clickLink('Task for Reply Test')
                ->waitForLocation('/admin/tasks/'.$task->id)
                    // Switch to comments tab
                ->click('a[href="#tab-comments"]')
                ->waitFor('#tab-comments')
                ->assertSee('Parent comment')
                    // Click reply button
                ->click('.reply-button')
                ->waitFor('.reply-form')
                ->type('reply-content', 'This is a reply to the comment')
                ->press('Reply')
                ->waitForText('This is a reply to the comment')
                ->assertSee('This is a reply to the comment')
                ->screenshot('comment-reply');
        });
    }

    /**
     * Test comment syncing UI
     */
    public function test_comment_sync_ui(): void
    {
        // Create test data
        $admin = $this->createAdminUser();
        $repository = Repository::factory()->create([
            'name' => 'Test Repository',
            'provider' => 'github',
            'url' => 'https://github.com/test/repo',
        ]);
        $task = Task::factory()->create([
            'repository_id' => $repository->id,
            'title' => 'Task with External Integration',
            'status' => 'in_progress',
            'external_id' => '123',
            'external_url' => 'https://github.com/test/repo/issues/123',
        ]);

        // Create a comment with external info
        Comment::factory()->create([
            'commentable_id' => $task->id,
            'commentable_type' => Task::class,
            'user_id' => $admin->id,
            'content' => 'Comment with external integration',
            'external_id' => 'ext123',
            'external_url' => 'https://github.com/test/repo/issues/123#comment-ext123',
        ]);

        $this->browse(function (Browser $browser) use ($task) {
            $browser->visit(new AdminLogin)
                ->login()
                ->clickLink('Tasks')
                ->waitForLocation('/admin/tasks')
                ->clickLink('Task with External Integration')
                ->waitForLocation('/admin/tasks/'.$task->id)
                    // Switch to comments tab
                ->click('a[href="#tab-comments"]')
                ->waitFor('#tab-comments')
                ->assertSee('Comment with external integration')
                ->assertPresent('.external-comment-indicator')
                ->assertSee('GitHub')
                ->screenshot('comment-external-integration');
        });
    }
}
