<?php

namespace Tests\Browser;

use App\Models\Repository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\AdminLogin;

class RepositoryManagementTest extends AdminTestCase
{
    use DatabaseMigrations;

    /**
     * Test the repository list screen loads and displays repositories
     */
    public function test_repository_list_displays_repositories(): void
    {
        // Create test data
        $this->createAdminUser();
        Repository::factory()->count(3)->create();

        $this->browse(function (Browser $browser) {
            $browser->visit(new AdminLogin)
                ->login()
                ->clickLink('Repositories')
                ->waitForLocation('/admin/repositories')
                ->assertSee('Repositories')
                ->assertPresent('.table')
                ->screenshot('repository-list');
        });
    }

    /**
     * Test repository filtering works
     */
    public function test_repository_filtering(): void
    {
        // Create test data with specific languages
        $this->createAdminUser();
        Repository::factory()->create(['name' => 'PHP Project', 'language' => 'php']);
        Repository::factory()->create(['name' => 'Python Project', 'language' => 'python']);
        Repository::factory()->create(['name' => 'JavaScript Project', 'language' => 'javascript']);

        $this->browse(function (Browser $browser) {
            $browser->visit(new AdminLogin)
                ->login()
                ->clickLink('Repositories')
                ->waitForLocation('/admin/repositories')
                    // Test the filter
                ->select('language', 'php')
                ->press('Filter')
                ->waitForText('PHP Project')
                ->assertSee('PHP Project')
                ->assertDontSee('Python Project')
                ->screenshot('repository-filter');
        });
    }

    /**
     * Test repository creation form
     */
    public function test_repository_creation_form(): void
    {
        $this->createAdminUser();

        $this->browse(function (Browser $browser) {
            $browser->visit(new AdminLogin)
                ->login()
                ->clickLink('Repositories')
                ->waitForLocation('/admin/repositories')
                    // Click create button and check form
                ->press('Add')
                ->waitFor('.modal')
                ->assertSee('Create Repository')
                ->assertPresent('input[name="repository[name]"]')
                ->assertPresent('input[name="repository[url]"]')
                ->assertPresent('select[name="repository[provider]"]')
                ->screenshot('repository-create-form');
        });
    }

    /**
     * Test repository edit form loads
     */
    public function test_repository_edit_form(): void
    {
        // Create test data
        $this->createAdminUser();
        $repository = Repository::factory()->create([
            'name' => 'Test Repository',
            'url' => 'https://github.com/test/repo',
            'provider' => 'github',
        ]);

        $this->browse(function (Browser $browser) use ($repository) {
            $browser->visit(new AdminLogin)
                ->login()
                ->clickLink('Repositories')
                ->waitForLocation('/admin/repositories')
                    // Click on the repository name to edit
                ->clickLink('Test Repository')
                ->waitForLocation('/admin/repositories/'.$repository->id)
                ->assertSee('Edit Repository')
                ->assertInputValue('repository[name]', 'Test Repository')
                ->assertInputValue('repository[url]', 'https://github.com/test/repo')
                ->screenshot('repository-edit-form');
        });
    }
}
