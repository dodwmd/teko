<?php

namespace App\Models;

use App\Traits\DocBlockHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property int $repository_id
 * @property string|null $external_id
 * @property string|null $external_url
 * @property string|null $provider
 * @property string $status
 * @property string $type
 * @property string|null $branch_name
 * @property string|null $pull_request_url
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property-read \App\Models\Repository|null $repository
 *
 * @method static \Illuminate\Database\Eloquent\Builder filters()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Task whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static int count($columns = '*')
 * @method static \Illuminate\Database\Eloquent\Model|static create(array $attributes = [])
 * @method static \Illuminate\Database\Eloquent\Collection get($columns = ['*'])
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
 *
 * @uses \Illuminate\Database\Eloquent\Factories\HasFactory<\App\Models\Task>
 */
class Task extends Model
{
    use AsSource, DocBlockHelpers, Filterable;

    /** @use HasFactory<\App\Models\Task> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'repository_id',
        'external_id',
        'external_url',
        'provider',
        'status',
        'type',
        'branch_name',
        'pull_request_url',
        'metadata',
        'started_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Name of columns to which HTTP sorting can be applied
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'title',
        'status',
        'type',
        'created_at',
        'updated_at',
    ];

    /**
     * Name of columns to which HTTP filtering can be applied
     * Note: Relation filtering (like repository) needs custom filters.
     *
     * @var array
     */
    protected $allowedFilters = [
        // 'title', // Removed to rely on TaskFiltersLayout
        // 'status', // Removed to rely on TaskFiltersLayout
        // 'type', // Removed to rely on TaskFiltersLayout
    ];

    /**
     * Get the repository that owns the task.
     */
    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }

    /**
     * Scope a query to only include tasks of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include tasks with a specific status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if the task is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the task is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if the task is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the task has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }
}
