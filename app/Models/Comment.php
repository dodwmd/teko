<?php

namespace App\Models;

use App\Traits\DocBlockHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $content
 * @property int $user_id
 * @property int|null $parent_id
 * @property int $commentable_id
 * @property string $commentable_type
 * @property string|null $external_id
 * @property string|null $external_url
 * @property string|null $status
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $synced_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $commentable
 * 
 * @uses \Illuminate\Database\Eloquent\Factories\HasFactory<\App\Models\Comment>
 */
class Comment extends Model
{
    use DocBlockHelpers;
    /** @use HasFactory<\App\Models\Comment> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content',
        'user_id',
        'parent_id',
        'commentable_id',
        'commentable_type',
        'external_id',
        'external_url',
        'status',
        'metadata',
        'synced_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'synced_at' => 'datetime',
    ];

    /**
     * Get the parent comment.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Get the user who wrote the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the commentable model.
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include top-level comments.
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to only include comments with a specific status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get the task this comment belongs to, if applicable.
     */
    public function task()
    {
        if ($this->commentable_type === Task::class) {
            return $this->commentable;
        }

        return null;
    }
}
