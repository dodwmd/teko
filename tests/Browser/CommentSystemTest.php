<?php

namespace Tests\Browser;

use App\Models\Comment;
use App\Models\Repository;
use App\Models\Task;
use Laravel\Dusk\Browser;

class CommentSystemTest extends AdminTestCase
{
    /**
     * Simple test just to explore task UI structure for debugging
     */
    public function test_task_ui_structure(): void
    {
        try {
            // Create test data
            $admin = $this->createAdminUser();
            $repository = Repository::factory()->create(['name' => 'Test Repository']);
            $task = Task::factory()->create([
                'repository_id' => $repository->id,
                'title' => 'Task for UI Exploration',
                'status' => 'in_progress',
            ]);

            // Create a comment for the task
            Comment::factory()->create([
                'commentable_id' => $task->id,
                'commentable_type' => Task::class,
                'user_id' => $admin->id,
                'content' => 'Test comment for UI exploration',
            ]);

            $this->browse(function (Browser $browser) use ($task, $admin) {
                // Direct navigation to task page and check UI structure
                $browser->loginAs($admin->id)
                    ->visit('/admin/dashboard')
                    ->screenshot('dashboard-view');

                // Capture all available links for debugging
                $this->captureDebugState($browser, 'dashboard');

                // Go to tasks page
                $browser->visit('/admin/tasks')
                    ->screenshot('tasks-page');

                $this->captureDebugState($browser, 'tasks-page');

                // Go directly to the task
                $browser->visit('/admin/tasks/'.$task->id)
                    ->screenshot('task-detail');

                $this->captureDebugState($browser, 'task-detail');

                // Check page elements
                $browser->assertSee('Task for UI Exploration')
                    ->assertPresent('form');

                // Try to find tab navigation
                $browser->driver->executeScript("
                    // Log all possible tab navigation selectors
                    var tabSelectors = {
                        'a[href=\"#tab-comments\"]': !!document.querySelector('a[href=\"#tab-comments\"]'),
                        'a[href=\"#comments\"]': !!document.querySelector('a[href=\"#comments\"]'),
                        '.nav-tabs a': document.querySelectorAll('.nav-tabs a').length,
                        '.tab-content': document.querySelectorAll('.tab-content').length,
                        'all tabs': Array.from(document.querySelectorAll('.nav-tabs a')).map(el => el.getAttribute('href'))
                    };
                    
                    // Save to window for debugging
                    window.tabSelectors = tabSelectors;
                    console.log('Tab selectors:', tabSelectors);
                    
                    // Return for logging
                    return tabSelectors;
                ");

                // If we found tabs, try to click one
                $tabFound = $this->safeJsClick($browser, '.nav-link');
                $browser->pause(1000)->screenshot('after-tab-click');

                // Try to find comments
                $browser->driver->executeScript("
                    var commentSelectors = {
                        '.comment-list': !!document.querySelector('.comment-list'),
                        '.comment-item': document.querySelectorAll('.comment-item').length,
                        '.comment-form': !!document.querySelector('.comment-form'),
                        '.comments-container': !!document.querySelector('.comments-container'),
                        'form textarea': document.querySelectorAll('form textarea').length,
                        'all forms': document.querySelectorAll('form').length
                    };
                    
                    console.log('Comment selectors:', commentSelectors);
                    return commentSelectors;
                ");

                $this->captureDebugState($browser, 'post-exploration');
            });
        } catch (\Throwable $e) {
            // Log the error for debugging
            file_put_contents(
                'tests/Browser/output/ui-exploration-error.log',
                get_class($e).': '.$e->getMessage()."\n".$e->getTraceAsString()
            );
            throw $e;
        }
    }
}
