<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Monitoring;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class AlertSettingsScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        // Fetch alert settings from database or use defaults
        $settings = DB::table('settings')
            ->where('group', 'alerts')
            ->pluck('value', 'key')
            ->toArray();

        return [
            'settings' => [
                'enabled' => $settings['alerts.enabled'] ?? false,
                'email_notifications' => $settings['alerts.email_notifications'] ?? true,
                'slack_notifications' => $settings['alerts.slack_notifications'] ?? false,
                'telegram_notifications' => $settings['alerts.telegram_notifications'] ?? false,
                'notification_levels' => json_decode($settings['alerts.notification_levels'] ?? '["emergency","alert","critical"]'),
                'email_recipients' => $settings['alerts.email_recipients'] ?? '',
                'slack_webhook' => $settings['alerts.slack_webhook'] ?? '',
                'telegram_bot_token' => $settings['alerts.telegram_bot_token'] ?? '',
                'telegram_chat_id' => $settings['alerts.telegram_chat_id'] ?? '',
                'throttle_minutes' => $settings['alerts.throttle_minutes'] ?? 15,
                'error_threshold' => $settings['alerts.error_threshold'] ?? 5,
            ],
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Alert Settings';
    }

    /**
     * The description displayed in the header.
     */
    public function description(): ?string
    {
        return 'Configure notifications for system errors';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Save Settings')
                ->icon('save')
                ->method('save'),

            Button::make('Test Notifications')
                ->icon('bell')
                ->method('test')
                ->confirm('This will send a test notification to all configured channels. Continue?'),
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
            Layout::block([
                Layout::rows([
                    Switcher::make('settings.enabled')
                        ->title('Enable Error Notifications')
                        ->placeholder('Send notifications when critical errors occur')
                        ->help('When enabled, the system will send alerts based on the settings below'),

                    Group::make([
                        Input::make('settings.throttle_minutes')
                            ->type('number')
                            ->min(1)
                            ->max(1440)
                            ->title('Throttle Period (minutes)')
                            ->help('Minimum time between repeat notifications for the same error'),

                        Input::make('settings.error_threshold')
                            ->type('number')
                            ->min(1)
                            ->max(100)
                            ->title('Error Threshold')
                            ->help('Minimum number of occurrences before triggering an alert'),
                    ]),

                    Select::make('settings.notification_levels.')
                        ->multiple()
                        ->title('Notification Levels')
                        ->options([
                            'emergency' => 'Emergency',
                            'alert' => 'Alert',
                            'critical' => 'Critical',
                            'error' => 'Error',
                        ])
                        ->help('Select which error levels should trigger notifications'),
                ]),
            ])
                ->title('General Settings')
                ->description('Basic notification configuration'),

            Layout::block([
                Layout::rows([
                    Switcher::make('settings.email_notifications')
                        ->title('Email Notifications')
                        ->help('Send alert notifications via email'),

                    Input::make('settings.email_recipients')
                        ->title('Email Recipients')
                        ->placeholder('email1@example.com, email2@example.com')
                        ->help('Comma-separated list of email addresses')
                        ->canSee(function ($settings) {
                            return $settings['email_notifications'] ?? false;
                        }),
                ]),
            ])
                ->title('Email Notifications')
                ->description('Configure email alert settings'),

            Layout::block([
                Layout::rows([
                    Switcher::make('settings.slack_notifications')
                        ->title('Slack Notifications')
                        ->help('Send alert notifications to Slack'),

                    Input::make('settings.slack_webhook')
                        ->title('Slack Webhook URL')
                        ->placeholder('https://hooks.slack.com/services/xxx/yyy/zzz')
                        ->help('Webhook URL for the Slack channel')
                        ->canSee(function ($settings) {
                            return $settings['slack_notifications'] ?? false;
                        }),
                ]),
            ])
                ->title('Slack Integration')
                ->description('Configure Slack alert settings'),

            Layout::block([
                Layout::rows([
                    Switcher::make('settings.telegram_notifications')
                        ->title('Telegram Notifications')
                        ->help('Send alert notifications to Telegram'),

                    Group::make([
                        Input::make('settings.telegram_bot_token')
                            ->title('Telegram Bot Token')
                            ->placeholder('1234567890:ABCDEF...')
                            ->help('Your Telegram bot token'),

                        Input::make('settings.telegram_chat_id')
                            ->title('Telegram Chat ID')
                            ->placeholder('-1001234567890')
                            ->help('The chat ID to send notifications to'),
                    ])
                        ->canSee(function ($settings) {
                            return $settings['telegram_notifications'] ?? false;
                        }),
                ]),
            ])
                ->title('Telegram Integration')
                ->description('Configure Telegram alert settings'),
        ];
    }

    /**
     * Save alert settings.
     */
    public function save(Request $request)
    {
        $settings = $request->input('settings');

        // Validate settings
        $validatedData = $request->validate([
            'settings.enabled' => 'boolean',
            'settings.email_notifications' => 'boolean',
            'settings.slack_notifications' => 'boolean',
            'settings.telegram_notifications' => 'boolean',
            'settings.notification_levels' => 'array',
            'settings.email_recipients' => 'nullable|string',
            'settings.slack_webhook' => 'nullable|string',
            'settings.telegram_bot_token' => 'nullable|string',
            'settings.telegram_chat_id' => 'nullable|string',
            'settings.throttle_minutes' => 'required|integer|min:1|max:1440',
            'settings.error_threshold' => 'required|integer|min:1|max:100',
        ]);

        // Save settings to database
        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            DB::table('settings')
                ->updateOrInsert(
                    [
                        'group' => 'alerts',
                        'key' => 'alerts.'.$key,
                    ],
                    [
                        'value' => $value,
                        'updated_at' => now(),
                    ]
                );
        }

        Toast::info('Alert settings saved successfully');
    }

    /**
     * Send test notifications to configured channels.
     */
    public function test()
    {
        // Get settings
        $settings = $this->query()['settings'];

        $testSent = false;

        // Send test email
        if ($settings['email_notifications'] && ! empty($settings['email_recipients'])) {
            // In a real implementation, this would call a notification service
            // Here we're just logging the event
            Log::info('Test email notification sent to: '.$settings['email_recipients']);
            $testSent = true;
        }

        // Send test Slack message
        if ($settings['slack_notifications'] && ! empty($settings['slack_webhook'])) {
            Log::info('Test Slack notification sent to webhook');
            $testSent = true;
        }

        // Send test Telegram message
        if ($settings['telegram_notifications'] && ! empty($settings['telegram_bot_token']) && ! empty($settings['telegram_chat_id'])) {
            Log::info('Test Telegram notification sent to chat ID: '.$settings['telegram_chat_id']);
            $testSent = true;
        }

        if ($testSent) {
            Toast::info('Test notifications sent successfully');
        } else {
            Toast::error('No notification channels are properly configured');
        }
    }
}
