<?php

namespace Tests\TestHelpers;

use App\Models\Repository;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Mockery;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layout;
use Orchid\Screen\Repository as ScreenRepository;
use Orchid\Screen\Screen;
use ReflectionClass;

class OrchidScreenMock
{
    /**
     * Create a mock for an Orchid Screen
     *
     * @return array [Screen $screen, \Mockery\MockInterface $mockScreen]
     */
    public static function createScreenMock(string $screenClass)
    {
        $screen = new $screenClass;
        $mockScreen = Mockery::mock($screen)->makePartial();

        $mockScreen->shouldReceive('render')
            ->andReturn(new TestResponse(response()->json(['success' => true])));

        $mockScreen->shouldReceive('commandBar')
            ->andReturn([
                Button::make('Save')->method('save'),
                Button::make('Remove')->method('remove'),
            ]);

        $mockScreen->shouldReceive('layout')
            ->andReturn([
                Layout::rows([
                    Input::make('title')->title('Title'),
                    TextArea::make('description')->title('Description'),
                    Select::make('status')->title('Status')
                        ->options([
                            'pending' => 'Pending',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                        ]),
                ]),
            ]);

        // Set errors variable needed for view
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);

        return [$screen, $mockScreen];
    }

    /**
     * Mock the query method of a screen to return test data
     *
     * @param  \Mockery\MockInterface  $mockScreen
     * @return \Mockery\MockInterface
     */
    public static function mockQuery($mockScreen, array $data = [])
    {
        $mockScreen->shouldReceive('query')
            ->andReturn(new ScreenRepository($data));

        return $mockScreen;
    }

    /**
     * Mock form submission for Orchid screens
     *
     * @return mixed
     */
    public static function mockFormSubmission(string $screenClass, string $method, array $data = [])
    {
        $screen = new $screenClass;

        // Create reflection to access protected/private methods
        $reflection = new ReflectionClass($screen);
        $methodReflection = $reflection->getMethod($method);
        $methodReflection->setAccessible(true);

        // Create a Request object with the data
        $request = Request::create('/', 'POST', $data);

        // Get the method parameters to properly call it
        $parameters = $methodReflection->getParameters();
        $args = [];

        foreach ($parameters as $parameter) {
            $paramType = $parameter->getType();
            $paramName = $parameter->getName();

            if ($paramType && ! $paramType->isBuiltin()) {
                $typeName = $paramType->getName();

                if ($typeName === Request::class) {
                    $args[] = $request;
                } elseif ($typeName === Task::class) {
                    // If parameter is Task, find or create one
                    if (isset($data['id'])) {
                        $args[] = Task::find($data['id']) ?? new Task;
                    } else {
                        $args[] = new Task;
                    }
                } elseif ($typeName === Repository::class) {
                    // If parameter is Repository, find or create one
                    if (isset($data['id'])) {
                        $args[] = Repository::find($data['id']) ?? new Repository;
                    } else {
                        $args[] = new Repository;
                    }
                } else {
                    // For other model types, create an instance
                    $args[] = new $typeName;
                }
            } else {
                // For built-in types, pass the appropriate data
                $args[] = $request;
            }
        }

        // Add view error bag used in Orchid views
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);

        // Call the method with the proper arguments
        return $methodReflection->invokeArgs($screen, $args);
    }
}
