<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Repository;

use App\Models\Repository;
use App\Orchid\Layouts\Repository\RepositoryFiltersLayout;
use App\Orchid\Layouts\Repository\RepositoryListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class RepositoryListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'repositories' => Repository::filters()
                ->defaultSort('name')
                ->paginate(10),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Repository Management';
    }

    /**
     * The description displayed in the header.
     */
    public function description(): ?string
    {
        return 'Manage code repositories for agent operations';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Create Repository')
                ->icon('plus')
                ->route('platform.repository.edit'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return string[]
     *
     * @psalm-return list{RepositoryFiltersLayout::class, RepositoryListLayout::class}
     */
    public function layout(): array
    {
        return [
            RepositoryFiltersLayout::class,
            RepositoryListLayout::class,
        ];
    }

    /**
     * Toggle the repository active status.
     */
    public function toggleActive(Request $request): void
    {
        $repository = Repository::findOrFail($request->get('id'));
        $repository->active = ! $repository->active;
        $repository->save();

        Toast::info($repository->active
            ? 'Repository activated successfully'
            : 'Repository deactivated successfully');
    }

    /**
     * Remove the repository.
     */
    public function remove(Request $request): void
    {
        Repository::findOrFail($request->get('id'))->delete();

        Toast::info('Repository deleted successfully');
    }
}
