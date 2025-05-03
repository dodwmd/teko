<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CommentService
{
    /**
     * Create a new comment and sync with external system if needed.
     *
     * @param  array  $data  Comment data
     */
    public function create(array $data): Comment
    {
        // Create the local comment
        $comment = Comment::create($data);

        // Sync with external system if applicable
        if ($this->shouldSync($comment)) {
            $this->syncWithExternalSystem($comment, 'create');
        }

        return $comment;
    }

    /**
     * Update an existing comment and sync changes.
     */
    public function update(Comment $comment, array $data): Comment
    {
        $comment->update($data);

        // Sync with external system if applicable
        if ($this->shouldSync($comment)) {
            $this->syncWithExternalSystem($comment, 'update');
        }

        return $comment;
    }

    /**
     * Delete a comment and remove from external system if needed.
     */
    public function delete(Comment $comment): bool
    {
        // Sync deletion with external system if applicable
        if ($this->shouldSync($comment)) {
            $this->syncWithExternalSystem($comment, 'delete');
        }

        return $comment->delete();
    }

    /**
     * Determine if a comment should be synced with an external system.
     */
    protected function shouldSync(Comment $comment): bool
    {
        // If the comment is on a task with external info, sync it
        if ($comment->commentable_type === Task::class) {
            $task = $comment->commentable;

            return ! empty($task->external_id) && ! empty($task->external_url) && ! empty($task->provider);
        }

        return false;
    }

    /**
     * Sync a comment with the appropriate external system.
     *
     * @param  string  $action  create|update|delete
     */
    protected function syncWithExternalSystem(Comment $comment, string $action): bool
    {
        try {
            $task = $comment->commentable;

            // Handle different providers
            switch ($task->provider) {
                case 'github':
                    return $this->syncWithGitHub($comment, $task, $action);
                case 'jira':
                    return $this->syncWithJira($comment, $task, $action);
                default:
                    Log::warning("Comment sync attempted with unsupported provider: {$task->provider}");

                    return false;
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync comment with external system: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Sync a comment with GitHub.
     */
    protected function syncWithGitHub(Comment $comment, Task $task, string $action): bool
    {
        // Extract repo and issue number from the external URL
        preg_match('/github\.com\/([^\/]+)\/([^\/]+)\/issues\/(\d+)/', $task->external_url, $matches);

        if (count($matches) !== 4) {
            Log::error("Invalid GitHub URL format: {$task->external_url}");

            return false;
        }

        [$_, $owner, $repo, $issueNumber] = $matches;

        // Get GitHub API token from config
        $token = config('services.github.token');

        if (empty($token)) {
            Log::error('GitHub API token not configured');

            return false;
        }

        // Perform the appropriate action
        $apiUrl = "https://api.github.com/repos/{$owner}/{$repo}/issues/{$issueNumber}/comments";

        switch ($action) {
            case 'create':
                $response = Http::withToken($token)
                    ->post($apiUrl, [
                        'body' => $comment->content,
                    ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    $comment->update([
                        'external_id' => (string) $responseData['id'],
                        'external_url' => $responseData['html_url'],
                        'synced_at' => now(),
                    ]);

                    return true;
                }
                break;

            case 'update':
                if (empty($comment->external_id)) {
                    return $this->syncWithGitHub($comment, $task, 'create');
                }

                $response = Http::withToken($token)
                    ->patch("{$apiUrl}/{$comment->external_id}", [
                        'body' => $comment->content,
                    ]);

                if ($response->successful()) {
                    $comment->update(['synced_at' => now()]);

                    return true;
                }
                break;

            case 'delete':
                if (empty($comment->external_id)) {
                    return true; // Nothing to delete externally
                }

                $response = Http::withToken($token)
                    ->delete("{$apiUrl}/{$comment->external_id}");

                return $response->successful();
        }

        return false;
    }

    /**
     * Sync a comment with Jira.
     */
    protected function syncWithJira(Comment $comment, Task $task, string $action): bool
    {
        // Extract domain and issue key from the external URL
        preg_match('/https:\/\/([^\/]+)\/browse\/([^\/]+)/', $task->external_url, $matches);

        if (count($matches) !== 3) {
            Log::error("Invalid Jira URL format: {$task->external_url}");

            return false;
        }

        [$_, $domain, $issueKey] = $matches;

        // Get Jira API credentials from config
        $email = config('services.jira.email');
        $token = config('services.jira.token');

        if (empty($email) || empty($token)) {
            Log::error('Jira API credentials not configured');

            return false;
        }

        // Perform the appropriate action
        $apiUrl = "https://{$domain}/rest/api/3/issue/{$issueKey}/comment";

        switch ($action) {
            case 'create':
                $response = Http::withBasicAuth($email, $token)
                    ->post($apiUrl, [
                        'body' => [
                            'type' => 'doc',
                            'version' => 1,
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        [
                                            'type' => 'text',
                                            'text' => $comment->content,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    $comment->update([
                        'external_id' => (string) $responseData['id'],
                        'external_url' => "{$task->external_url}?focusedCommentId={$responseData['id']}",
                        'synced_at' => now(),
                    ]);

                    return true;
                }
                break;

            case 'update':
                if (empty($comment->external_id)) {
                    return $this->syncWithJira($comment, $task, 'create');
                }

                $response = Http::withBasicAuth($email, $token)
                    ->put("{$apiUrl}/{$comment->external_id}", [
                        'body' => [
                            'type' => 'doc',
                            'version' => 1,
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        [
                                            'type' => 'text',
                                            'text' => $comment->content,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]);

                if ($response->successful()) {
                    $comment->update(['synced_at' => now()]);

                    return true;
                }
                break;

            case 'delete':
                if (empty($comment->external_id)) {
                    return true; // Nothing to delete externally
                }

                $response = Http::withBasicAuth($email, $token)
                    ->delete("{$apiUrl}/{$comment->external_id}");

                return $response->successful();
        }

        return false;
    }
}
