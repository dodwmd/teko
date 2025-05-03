<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Monitoring;

use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ErrorListLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    protected $target = 'errors';

    /**
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('level', 'Level')
                ->sort()
                ->render(function ($error) {
                    $levelClasses = [
                        'emergency' => 'badge bg-danger',
                        'alert' => 'badge bg-danger',
                        'critical' => 'badge bg-danger',
                        'error' => 'badge bg-warning',
                    ];

                    $class = $levelClasses[$error->level] ?? 'badge bg-secondary';

                    return "<span class=\"{$class}\">".ucfirst($error->level).'</span>';
                }),

            TD::make('message', 'Message')
                ->sort()
                ->render(function ($error) {
                    return Str::limit($error->message, 100);
                }),

            TD::make('context->error_type', 'Type')
                ->sort()
                ->render(function ($error) {
                    $context = json_decode($error->context);

                    return $context->error_type ?? 'Unknown';
                }),

            TD::make('context->agent_name', 'Agent')
                ->sort()
                ->render(function ($error) {
                    $context = json_decode($error->context);

                    return $context->agent_name ?? 'System';
                }),

            TD::make('created_at', 'Timestamp')
                ->sort()
                ->render(function ($error) {
                    return date('Y-m-d H:i:s', strtotime($error->created_at));
                }),

            TD::make('context->resolved', 'Status')
                ->sort()
                ->render(function ($error) {
                    $context = json_decode($error->context);

                    return isset($context->resolved) && $context->resolved
                        ? '<span class="badge bg-success">Resolved</span>'
                        : '<span class="badge bg-secondary">Unresolved</span>';
                }),

            TD::make('actions', 'Actions')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function ($error) {
                    $context = json_decode($error->context);
                    $isUnresolved = ! isset($context->resolved) || ! $context->resolved;

                    $dropdown = DropDown::make()
                        ->icon('options-vertical')
                        ->list([
                            Link::make('View Details')
                                ->icon('eye')
                                ->route('platform.monitoring.error.view', $error->id),
                        ]);

                    // Only add the "Mark as Resolved" button if the error is unresolved
                    if ($isUnresolved) {
                        $dropdown->list[] = Button::make('Mark as Resolved')
                            ->icon('check')
                            ->method('markAsResolved', ['id' => $error->id]);
                    }

                    return $dropdown;
                }),
        ];
    }
}
