<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Monitoring;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Code;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ErrorDetailScreen extends Screen
{
    /**
     * The error record.
     *
     * @var object
     */
    public $error;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(int $id): iterable
    {
        $this->error = DB::table('logs')->where('id', $id)->first();

        if (! $this->error) {
            abort(404, 'Error record not found');
        }

        // Parse the JSON context
        $context = json_decode($this->error->context ?? '{}');

        // Get related errors
        $relatedErrors = DB::table('logs')
            ->where('id', '!=', $id)
            ->where('message', $this->error->message)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get stack trace if available
        $stackTrace = $context->exception->trace ?? [];

        return [
            'error' => $this->error,
            'relatedErrors' => $relatedErrors,
            'context' => $context,
            'stackTrace' => $stackTrace,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Error Details';
    }

    /**
     * The description displayed in the header.
     */
    public function description(): ?string
    {
        return 'View detailed information about this error';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        $context = json_decode($this->error->context ?? '{}');
        $isResolved = isset($context->resolved) && $context->resolved;

        return [
            Link::make('Back to List')
                ->icon('arrow-left')
                ->route('platform.monitoring.errors'),

            Button::make($isResolved ? 'Mark as Unresolved' : 'Mark as Resolved')
                ->icon($isResolved ? 'close' : 'check')
                ->method('toggleResolved', ['id' => $this->error->id]),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        $context = json_decode($this->error->context ?? '{}');

        return [
            Layout::block([
                Layout::view('platform.monitoring.error-header', [
                    'error' => $this->error,
                    'context' => $context,
                ]),
            ])
                ->title('Error Information')
                ->description('Basic details about this error'),

            Layout::tabs([
                'Details' => [
                    Layout::rows([
                        Group::make([
                            Input::make('error.level')
                                ->title('Level')
                                ->readonly(),

                            Input::make('error.created_at')
                                ->title('Timestamp')
                                ->readonly(),
                        ]),

                        TextArea::make('error.message')
                            ->title('Error Message')
                            ->rows(3)
                            ->readonly(),

                        Group::make([
                            Input::make('context.file')
                                ->title('File')
                                ->value($context->exception->file ?? 'Unknown')
                                ->readonly(),

                            Input::make('context.line')
                                ->title('Line')
                                ->value($context->exception->line ?? 'Unknown')
                                ->readonly(),
                        ]),

                        Input::make('context.url')
                            ->title('URL')
                            ->value($context->url ?? 'Unknown')
                            ->readonly(),

                        Group::make([
                            Input::make('context.agent_name')
                                ->title('Agent')
                                ->value($context->agent_name ?? 'System')
                                ->readonly(),

                            Input::make('context.error_type')
                                ->title('Error Type')
                                ->value($context->error_type ?? 'Unknown')
                                ->readonly(),
                        ]),
                    ]),
                ],

                'Stack Trace' => [
                    Layout::rows([
                        Code::make('stackTrace')
                            ->language('json')
                            ->lineNumbers(),
                    ]),
                ],

                'Context Data' => [
                    Layout::rows([
                        Code::make('error.context')
                            ->language('json')
                            ->lineNumbers(),
                    ]),
                ],

                'Related Errors' => [
                    Layout::view('platform.monitoring.related-errors', [
                        'relatedErrors' => $this->query($this->error->id)['relatedErrors'],
                    ]),
                ],
            ]),
        ];
    }

    /**
     * Toggle the resolved status of the error.
     */
    public function toggleResolved(Request $request)
    {
        $id = $request->input('id');
        $error = DB::table('logs')->where('id', $id)->first();

        if (! $error) {
            Toast::error('Error record not found');

            return redirect()->route('platform.monitoring.errors');
        }

        $context = json_decode($error->context ?? '{}');
        $isCurrentlyResolved = isset($context->resolved) && $context->resolved;

        // Toggle the resolved status
        $context->resolved = ! $isCurrentlyResolved;

        // Update the error record
        DB::table('logs')
            ->where('id', $id)
            ->update([
                'context' => json_encode($context),
            ]);

        Toast::info($isCurrentlyResolved
            ? 'Error marked as unresolved'
            : 'Error marked as resolved');

        return redirect()->route('platform.monitoring.error.view', $id);
    }
}
