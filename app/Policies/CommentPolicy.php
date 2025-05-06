<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Add logic based on user roles/permissions if needed
        return true; // Or check permission: $user->can('view comments');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Comment $comment): bool
    {
        // Example: Allow if user owns the comment or is admin
        // return $user->id === $comment->user_id || $user->hasAccess('platform.systems.admin');
        return true; // Default allow for now
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Or check permission
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        // Example: Allow if user owns the comment or is admin
        // return $user->id === $comment->user_id || $user->hasAccess('platform.systems.admin');
        return true; // Default allow for now
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // Example: Allow if user owns the comment or is admin
        // return $user->id === $comment->user_id || $user->hasAccess('platform.systems.admin');
        return true; // Default allow for now
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comment $comment): bool
    {
        return true; // Or check permission
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        return true; // Or check permission
    }
}
