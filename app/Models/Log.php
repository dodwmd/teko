<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Log extends Model
{
    use AsSource, Filterable, HasFactory;

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
