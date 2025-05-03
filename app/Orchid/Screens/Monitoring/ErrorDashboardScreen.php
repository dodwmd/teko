<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Monitoring;

use App\Orchid\Layouts\Monitoring\ErrorListLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ErrorDashboardScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        // Use direct DB query for simplicity and to avoid filter resolution issues
        $query = DB::table('logs')
            ->where(function ($q) {
                $q->where('level', 'error')
                    ->orWhere('level', 'critical')
                    ->orWhere('level', 'alert')
                    ->orWhere('level', 'emergency');
            });

        // Apply simple filtering based on request parameters
        if ($request->has('message') && ! empty($request->message)) {
            $query->where('message', 'like', '%'.$request->message.'%');
        }

        if ($request->has('level') && ! empty($request->level)) {
            $query->where('level', $request->level);
        }

        // Apply sorting and pagination
        $errors = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        // Group errors by type for the overview chart
        $errorsByType = DB::table('logs')
            ->select('level', DB::raw('count(*) as count'))
            ->where(function ($query) {
                $query->where('level', 'error')
                    ->orWhere('level', 'critical')
                    ->orWhere('level', 'alert')
                    ->orWhere('level', 'emergency');
            })
            ->groupBy('level')
            ->get()
            ->pluck('count', 'level')
            ->toArray();

        // Get error count trend for the last 7 days
        $trendData = DB::table('logs')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where(function ($query) {
                $query->where('level', 'error')
                    ->orWhere('level', 'critical')
                    ->orWhere('level', 'alert')
                    ->orWhere('level', 'emergency');
            })
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Format trend data for the chart
        $errorTrend = [];
        $labels = [];
        foreach ($trendData as $data) {
            $labels[] = $data->date;
            $errorTrend[] = $data->count;
        }

        return [
            'errors' => $errors,
            'errorsByType' => $errorsByType,
            'errorTrend' => $errorTrend,
            'trendLabels' => $labels,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Error Monitoring';
    }

    /**
     * The description displayed in the header.
     */
    public function description(): ?string
    {
        return 'Track and analyze system errors';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Clear All')
                ->icon('trash')
                ->method('clearAll')
                ->confirm('Are you sure you want to clear all error logs? This action cannot be undone.')
                ->canSee(DB::table('logs')->where('level', 'error')->exists()),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::tabs([
                'Error Overview' => Layout::columns([
                    Layout::view('vendor.platform.charts.error_type_chart'),
                    Layout::view('vendor.platform.charts.error_trend_chart'),
                ]),
                'Error List' => Layout::columns([
                    Layout::block([
                        Layout::rows([
                            \Orchid\Screen\Fields\Input::make('message')
                                ->title('Search by message')
                                ->placeholder('Enter error message'),

                            \Orchid\Screen\Fields\Select::make('level')
                                ->title('Error Level')
                                ->options([
                                    'emergency' => 'Emergency',
                                    'alert' => 'Alert',
                                    'critical' => 'Critical',
                                    'error' => 'Error',
                                    'warning' => 'Warning',
                                    'notice' => 'Notice',
                                    'info' => 'Info',
                                    'debug' => 'Debug',
                                ])
                                ->empty('All Levels'),

                            Button::make('Filter')
                                ->method('filter'),
                        ]),
                    ])
                        ->title('Filter Errors')
                        ->description('Filter error logs by various criteria'),
                    Layout::view('vendor.platform.metrics.error_counter'),
                    ErrorListLayout::class,
                ]),
            ]),
        ];
    }

    /**
     * Filter error logs based on form submission
     */
    public function filter(Request $request)
    {
        return redirect()->route('platform.monitoring.errors', [
            'message' => $request->get('message'),
            'level' => $request->get('level'),
        ]);
    }

    /**
     * Clear all error logs.
     */
    public function clearAll()
    {
        DB::table('logs')->where(function ($query) {
            $query->where('level', 'error')
                ->orWhere('level', 'critical')
                ->orWhere('level', 'alert')
                ->orWhere('level', 'emergency');
        })->delete();

        Toast::info('All error logs have been cleared.');

        return redirect()->route('platform.monitoring.errors');
    }
}
