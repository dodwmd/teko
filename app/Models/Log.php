<?php

namespace App\Models;

use App\Traits\DocBlockHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

/**
 * @property int $id
 * @property string|null $channel
 * @property string $level
 * @property string $message
 * @property array|null $context
 * @property int|null $agent_id
 * @property int|null $repository_id
 * @property int|null $task_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Task|null $task
 * @property-read \App\Models\Repository|null $repository
 * @property-read \App\Models\Agent|null $agent
 *
 * @uses \Illuminate\Database\Eloquent\Factories\HasFactory<\App\Models\Log>
 */
class Log extends Model
{
    use AsSource, DocBlockHelpers, Filterable;

    /** @use HasFactory<\App\Models\Log> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'channel',
        'level',
        'message',
        'context',
        'agent_id',
        'repository_id',
        'task_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'context' => 'array',
    ];

    /**
     * Searchable columns for Orchid filtering
     */
    protected $allowedFilters = [
        'message',
        'level',
        'channel',
        'created_at',
    ];

    /**
     * Default sort options for Orchid
     */
    protected $allowedSorts = [
        'id',
        'level',
        'created_at',
    ];

    /**
     * Relationship with the Agent model
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Relationship with the Repository model
     */
    public function repository()
    {
        return $this->belongsTo(Repository::class);
    }

    /**
     * Relationship with the Task model
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
