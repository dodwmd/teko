<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    use AuthorizesRequests;

    /**
     * The comment service instance.
     *
     * @var CommentService
     */
    protected $commentService;

    /**
     * Create a new controller instance.
     */
    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Store a newly created comment.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'content' => 'required|string',
            'commentable_id' => 'required|integer',
            'commentable_type' => 'required|string',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);

        $commentableType = $validatedData['commentable_type'];
        $commentableId = $validatedData['commentable_id'];

        // Ensure the commentable type is valid and the user can comment on it
        if (! class_exists($commentableType)) {
            return response()->json(['message' => 'Invalid commentable type'], 422);
        }

        // Find the commentable model
        $commentable = $commentableType::find($commentableId);

        if (! $commentable) {
            return response()->json(['message' => 'Commentable not found'], 404);
        }

        // Create the comment
        $comment = $this->commentService->create([
            'content' => $validatedData['content'],
            'user_id' => Auth::id(),
            'parent_id' => $validatedData['parent_id'] ?? null,
            'commentable_id' => $commentableId,
            'commentable_type' => $commentableType,
            'status' => 'active',
        ]);

        // Load relations for the response
        $comment->load('user');

        return response()->json([
            'message' => 'Comment created successfully',
            'comment' => $comment,
        ]);
    }

    /**
     * Update the specified comment.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Comment $comment)
    {
        // Log the IDs for debugging
        // Log::debug('Comment Update Auth Check:', [
        //     'auth_id' => Auth::id(),
        //     'comment_user_id' => $comment->user_id,
        // ]);

        // Ensure the user owns this comment -- Replaced with Policy Check
        // if ($comment->user_id !== Auth::id()) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        // Use the CommentPolicy to authorize the update action
        $this->authorize('update', $comment);

        $validatedData = $request->validate([
            'content' => 'required|string',
        ]);

        $comment = $this->commentService->update($comment, [
            'content' => $validatedData['content'],
        ]);

        $comment->refresh(); // Re-add refresh to ensure the model has the latest data

        // Comment out the debug log
        // Log::debug('Comment before JSON response:', $comment->toArray());

        return response()->json($comment->toArray()); // Return explicit array
    }

    /**
     * Remove the specified comment.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Comment $comment)
    {
        // Ensure the user owns this comment
        if ($comment->user_id !== Auth::id() && ! Auth::user()->hasPermission('comment.delete.any')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->commentService->delete($comment);

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Get comments for a commentable entity.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComments(Request $request)
    {
        $validatedData = $request->validate([
            'commentable_id' => 'required|integer',
            'commentable_type' => 'required|string',
        ]);

        $commentableType = $validatedData['commentable_type'];
        $commentableId = $validatedData['commentable_id'];

        // Ensure the commentable type is valid
        if (! class_exists($commentableType)) {
            return response()->json(['message' => 'Invalid commentable type'], 422);
        }

        // Find the commentable model
        $commentable = $commentableType::find($commentableId);

        if (! $commentable) {
            return response()->json(['message' => 'Commentable not found'], 404);
        }

        // Get all top-level comments with their replies
        $comments = Comment::where('commentable_type', $commentableType)
            ->where('commentable_id', $commentableId)
            ->where('status', 'active')
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'comments' => $comments,
        ]);
    }
}
