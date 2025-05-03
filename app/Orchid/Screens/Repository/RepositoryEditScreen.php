<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Repository;

use App\Models\Repository;
use App\Orchid\Layouts\Repository\RepositoryEditLayout;
use App\Orchid\Layouts\Repository\RepositoryMetadataLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class RepositoryEditScreen extends Screen
{
    /**
     * @var Repository
     */
    public $repository;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Repository $repository): iterable
    {
        return [
            'repository' => $repository,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->repository->exists
            ? 'Edit Repository: '.$this->repository->name
            : 'Add Repository';
    }

    /**
     * The description displayed in the header.
     */
    public function description(): ?string
    {
        return 'Configure repository settings for agent operations';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Save')
                ->icon('save')
                ->method('save'),

            Button::make($this->repository->active ? 'Deactivate' : 'Activate')
                ->icon($this->repository->active ? 'minus' : 'check')
                ->method('toggleActive')
                ->canSee($this->repository->exists),

            Button::make('Delete')
                ->icon('trash')
                ->method('remove')
                ->canSee($this->repository->exists),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::tabs([
                'General' => [
                    RepositoryEditLayout::class,
                ],
                'Metadata' => [
                    RepositoryMetadataLayout::class,
                ],
            ]),
        ];
    }

    /**
     * Save the repository.
     *
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Repository $repository, Request $request)
    {
        $data = $request->validate([
            'repository.name' => 'required|string|max:255',
            'repository.url' => 'required|url|max:255',
            'repository.provider' => 'required|string|max:255',
            'repository.default_branch' => 'required|string|max:255',
            'repository.language' => 'nullable|string|max:255',
            'repository.languages' => 'nullable|array',
            'repository.description' => 'nullable|string',
            'repository.metadata' => 'nullable|array',
            'repository.active' => 'boolean',
        ]);

        $repository->fill($data['repository'])->save();

        Toast::info($repository->wasRecentlyCreated
            ? 'Repository added successfully'
            : 'Repository updated successfully');

        return redirect()->route('platform.repository.list');
    }

    /**
     * Toggle the repository active status.
     *
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleActive(Repository $repository)
    {
        $repository->active = ! $repository->active;
        $repository->save();

        Toast::info($repository->active
            ? 'Repository activated successfully'
            : 'Repository deactivated successfully');

        return redirect()->route('platform.repository.edit', $repository);
    }

    /**
     * Remove the repository.
     *
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Repository $repository)
    {
        $repository->delete();

        Toast::info('Repository deleted successfully');

        return redirect()->route('platform.repository.list');
    }
}
