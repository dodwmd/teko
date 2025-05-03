<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Task;

use App\Models\Task;
use App\Orchid\Layouts\Task\TaskFiltersLayout;
use App\Orchid\Layouts\Task\TaskListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class TaskListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'tasks' => Task::with('repository')
                ->filters()
                ->defaultSort('-created_at')
                ->paginate(10),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Task Management';
    }

    /**
     * The description displayed in the header.
     */
    public function description(): ?string
    {
        return 'Track and manage agent tasks';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Create Task')
                ->icon('plus')
                ->route('platform.task.edit'),
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
            TaskFiltersLayout::class,
            TaskListLayout::class,
        ];
    }

    /**
     * Cancel the selected tasks.
     */
    public function cancelSelected(Request $request): void
    {
        $ids = $request->get('id', []);

        if (empty($ids)) {
            Toast::warning('No tasks selected');

            return;
        }

        $tasks = Task::whereIn('id', $ids)
            ->whereIn('status', ['pending', 'in_progress'])
            ->get();

        foreach ($tasks as $task) {
            $task->status = 'cancelled';
            $task->save();
        }

        Toast::info(count($tasks).' tasks have been cancelled');
    }
}
