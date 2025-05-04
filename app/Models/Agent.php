<?php

namespace App\Models;

use App\Traits\DocBlockHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $language
 * @property bool $enabled
 * @property array|null $configuration
 * @property string|null $description
 * @property array|null $capabilities
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $last_active_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * 
 * @uses \Illuminate\Database\Eloquent\Factories\HasFactory<\App\Models\Agent>
 */
class Agent extends Model
{
    use AsSource, Filterable, DocBlockHelpers;
    /** @use HasFactory<\App\Models\Agent> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'language',
        'enabled',
        'configuration',
        'description',
        'capabilities',
        'metadata',
        'last_active_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'configuration' => 'array',
        'capabilities' => 'array',
        'metadata' => 'array',
        'enabled' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    /**
     * Scope a query to only include enabled agents.
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope a query to only include agents of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include language-specific agents.
     */
    public function scopeForLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Check if the agent is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enable the agent.
     */
    public function enable(): self
    {
        $this->enabled = true;
        $this->save();

        return $this;
    }

    /**
     * Disable the agent.
     */
    public function disable(): self
    {
        $this->enabled = false;
        $this->save();

        return $this;
    }

    /**
     * Update the last active timestamp.
     */
    public function updateLastActive(): self
    {
        $this->last_active_at = now();
        $this->save();

        return $this;
    }
}
