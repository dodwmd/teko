<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
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
