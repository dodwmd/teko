<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Task;

use App\Models\Repository;
use App\Models\Task;
use App\Orchid\Layouts\Task\TaskEditLayout;
use App\Orchid\Layouts\Task\TaskMetadataLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TaskEditScreen extends Screen
{
    /**
     * @var Task
     */
    public $task;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Task $task): iterable
    {
        return [
            'task' => $task,
            'repositories' => Repository::pluck('name', 'id'),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->task->exists ? 'Edit Task: '.$this->task->title : 'Create Task';
    }

    /**
     * The description displayed in the header.
     */
    public function description(): ?string
    {
        return $this->task->exists
            ? 'View and edit task details'
            : 'Create a new task for an agent to work on';
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

            Button::make('Cancel Task')
                ->icon('close')
                ->method('cancel')
                ->canSee($this->task->exists && in_array($this->task->status, ['pending', 'in_progress'])),

            Button::make('Delete')
                ->icon('trash')
                ->method('remove')
                ->canSee($this->task->exists),
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
                    TaskEditLayout::class,
                ],
                'Metadata' => [
                    TaskMetadataLayout::class,
                ],
                'Comments' => [
                    Layout::view('partials.comments.section', [
                        'commentableId' => $this->task->exists ? $this->task->id : null,
                        'commentableType' => Task::class,
                        'externalUrl' => $this->task->external_url ?? null,
                        'externalId' => $this->task->external_id ?? null,
                    ])->canSee($this->task->exists),
                ],
            ]),
        ];
    }

    /**
     * Save the task.
     *
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Task $task, Request $request)
    {
        $data = $request->validate([
            'task.title' => 'required|string|max:255',
            'task.description' => 'nullable|string',
            'task.repository_id' => 'nullable|exists:repositories,id',
            'task.external_id' => 'nullable|string|max:255',
            'task.external_url' => 'nullable|url|max:255',
            'task.provider' => 'nullable|string|max:255',
            'task.status' => 'required|string|max:255',
            'task.type' => 'required|string|max:255',
            'task.branch_name' => 'nullable|string|max:255',
            'task.pull_request_url' => 'nullable|url|max:255',
            'task.metadata' => 'nullable|array',
        ]);

        $task->fill($data['task']);

        // Set started_at timestamp if status is changing to in_progress
        if ($task->isDirty('status') && $task->status === 'in_progress' && ! $task->started_at) {
            $task->started_at = now();
        }

        // Set completed_at timestamp if status is changing to completed
        if ($task->isDirty('status') && $task->status === 'completed' && ! $task->completed_at) {
            $task->completed_at = now();
        }

        $task->save();

        Toast::info($task->wasRecentlyCreated ? 'Task created successfully' : 'Task updated successfully');

        return redirect()->route('platform.task.list');
    }

    /**
     * Cancel the task.
     *
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Task $task)
    {
        if (in_array($task->status, ['pending', 'in_progress'])) {
            $task->status = 'cancelled';
            $task->save();
            Toast::info('Task cancelled successfully');
        } else {
            Toast::error('Task cannot be cancelled in its current state');
        }

        return redirect()->route('platform.task.list');
    }

    /**
     * Remove the task.
     *
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Task $task)
    {
        $task->delete();

        Toast::info('Task deleted successfully');

        return redirect()->route('platform.task.list');
    }
}
