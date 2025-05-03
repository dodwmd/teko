<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Orchid\Platform\Dashboard;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Dashboard $dashboard): void
    {
        // Add Google Analytics to the Orchid dashboard
        $dashboard->registerResource('scripts', 'components/google-analytics');

        // Track custom events for agent activities
        $this->registerEventListeners();
    }

    /**
     * Register event listeners for analytics tracking
     */
    protected function registerEventListeners(): void
    {
        // Track when agents are enabled/disabled
        Event::listen('agent.status.changed', function ($agent, $status) {
            // In a real implementation, you would log this to your analytics system
            // This is just a placeholder for the event listener
            Log::info("Agent status changed: {$agent->name} is now {$status}");
        });

        // Track when tasks are created/completed
        Event::listen('task.status.changed', function ($task, $oldStatus, $newStatus) {
            Log::info("Task status changed: {$task->title} changed from {$oldStatus} to {$newStatus}");
        });
    }
}
