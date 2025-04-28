<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Repository extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'url',
        'provider',
        'default_branch',
        'language',
        'languages',
        'description',
        'metadata',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'languages' => 'array',
        'metadata' => 'array',
        'active' => 'boolean',
    ];

    /**
     * Get the tasks associated with the repository.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
