<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Repository;

use App\Models\Repository;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class RepositoryListLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    protected $target = 'repositories';

    /**
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('name', 'Name')
                ->sort()
                ->filter()
                ->render(function (Repository $repository) {
                    return Link::make($repository->name)
                        ->route('platform.repository.edit', $repository->id);
                }),

            TD::make('provider', 'Provider')
                ->sort()
                ->render(function (Repository $repository) {
                    $providers = [
                        'github' => 'GitHub',
                        'gitlab' => 'GitLab',
                        'bitbucket' => 'Bitbucket',
                        'azure' => 'Azure DevOps',
                    ];

                    return $providers[$repository->provider] ?? ucfirst($repository->provider);
                }),

            TD::make('language', 'Primary Language')
                ->sort()
                ->render(function (Repository $repository) {
                    return ucfirst($repository->language ?? 'Unknown');
                }),

            TD::make('languages', 'Languages')
                ->render(function (Repository $repository) {
                    if (empty($repository->languages)) {
                        return '—';
                    }

                    // Ensure languages is an array before iterating
                    if (! is_array($repository->languages)) {
                        return '<span class="text-danger">Invalid Data</span>';
                    }

                    $output = '';
                    $count = 0;

                    foreach ($repository->languages as $language => $percentage) {
                        if ($count < 3) {
                            $output .= '<span class="badge bg-light text-dark me-1">'
                                .ucfirst($language).'</span>';
                        }
                        $count++;
                    }

                    if ($count > 3) {
                        $output .= '<span class="badge bg-light text-dark">+'.($count - 3).' more</span>';
                    }

                    return $output;
                }),

            TD::make('active', 'Status')
                ->sort()
                ->render(function (Repository $repository) {
                    return $repository->active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>';
                }),

            TD::make('url', 'URL')
                ->render(function (Repository $repository) {
                    return '<a href="'.$repository->url.'" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;">'
                        .$repository->url.'</a>';
                }),

            TD::make('actions', 'Actions')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function (Repository $repository) {
                    return DropDown::make()
                        ->icon('options-vertical')
                        ->list([
                            Link::make('Edit')
                                ->icon('pencil')
                                ->route('platform.repository.edit', $repository->id),

                            Button::make($repository->active ? 'Deactivate' : 'Activate')
                                ->icon($repository->active ? 'minus' : 'check')
                                ->method('toggleActive', ['id' => $repository->id]),

                            Button::make('Delete')
                                ->icon('trash')
                                ->method('remove', ['id' => $repository->id])
                                ->confirm('Are you sure you want to delete this repository?'),
                        ]);
                }),
        ];
    }
}
