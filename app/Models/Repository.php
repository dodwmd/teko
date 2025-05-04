<?php

namespace App\Models;

use App\Traits\DocBlockHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

/**
 * @property int $id
 * @property string $name
 * @property string $url
 * @property string $provider
 * @property string $default_branch
 * @property string|null $language
 * @property array|null $languages
 * @property string|null $description
 * @property array|null $metadata
 * @property bool $active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $tasks
 * 
 * @uses \Illuminate\Database\Eloquent\Factories\HasFactory<\App\Models\Repository>
 */
class Repository extends Model
{
    use AsSource, Filterable, DocBlockHelpers;
    /** @use HasFactory<\App\Models\Repository> */
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
