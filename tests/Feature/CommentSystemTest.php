<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Laravel\Sanctum\Sanctum;
use Orchid\Platform\Models\Role;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentSystemTest extends TestCase
{
    use InteractsWithDatabase;

    /**
     * Helper method to get the admin user.
     */
    protected function getAdminUser(): User
    {
        $role = Role::where('slug', 'admin')->firstOrFail();
        $adminUser = User::where('email', 'admin@teko.com')->firstOrFail();

        // $adminUser->addRole($role); // Remove this: Role should already be attached by seeder
        return $adminUser;
    }

    /**
     * Test that comments can be created via the platform route.
     */
    #[Test]
    public function comments_can_be_created(): void
    {
        $adminUser = $this->getAdminUser();

        // Create a task to comment on
        $task = Task::factory()->create();

        $commentData = [
            'content' => 'This is a test comment via API.',
            'commentable_id' => $task->id,
            'commentable_type' => Task::class, // Or get_class($task)
        ];

        // Simulate acting as the authenticated user via Sanctum
        $response = $this->actingAs($adminUser, 'sanctum')
            ->postJson(route('api.comment.store'), $commentData);

        // Assert successful response
        $response->assertStatus(200); // Controller returns 200 OK
        $response->assertJsonStructure([
            'message',
            'comment' => [
                'id',
                'content',
                'user_id',
                'commentable_id',
                'commentable_type',
                // ... other expected fields
            ],
        ]);

        // Assert the comment exists in the database
        $this->assertDatabaseHas('comments', [
            'commentable_id' => $task->id,
            'commentable_type' => Task::class,
            'user_id' => $adminUser->id,
            'content' => $commentData['content'],
        ]);
    }

    /**
     * Test that comments for a specific task can be retrieved.
     */
    #[Test]
    public function comments_can_be_retrieved_for_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        Comment::factory()->count(3)->create([
            'commentable_id' => $task->id,
            'commentable_type' => Task::class,
            'user_id' => $user->id,
        ]);

        // Use the standard admin user for session authentication for this platform route (assuming)
        $adminUser = User::where('email', 'admin@teko.com')->firstOrFail();

        $response = $this->actingAs($adminUser)->getJson(route('comments.list', [
            'commentable_id' => $task->id,
            'commentable_type' => Task::class,
        ]));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'comments'); // Check count in the 'comments' array
    }

    /**
     * Test that comments can be updated via the API route.
     */
    #[Test]
    public function comments_can_be_updated(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        $comment = Comment::factory()->create([
            'commentable_id' => $task->id,
            'commentable_type' => Task::class,
            'user_id' => $user->id,
        ]);
        $updatedData = ['content' => 'Updated comment content.'];

        // Use Sanctum for API authentication for API routes
        Sanctum::actingAs(User::where('email', 'admin@teko.com')->firstOrFail());

        // Send PUT request to update the comment using the platform route
        $response = $this->withHeaders(['Accept' => 'application/json']) // Request JSON response
            ->putJson(route('api.comment.update', $comment->id), $updatedData); // Use new API route

        // Assert a successful response (200 OK for JSON response)
        $response->assertStatus(200);

        // Assert the updated content is present in the JSON response (checking top-level)
        $response->assertJson(['content' => $updatedData['content']]);

        // Optionally, assert the change in the database as well
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => $updatedData['content'],
        ]);
    }
}
