<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Models\User;
use App\Orchid\Layouts\Role\RolePermissionLayout;
use App\Orchid\Layouts\User\UserEditLayout;
use App\Orchid\Layouts\User\UserPasswordLayout;
use App\Orchid\Layouts\User\UserRoleLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Orchid\Access\Impersonation;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class UserEditScreen extends Screen
{
    /**
     * @var User
     */
    public $user;

    /**
     * Constructor to initialize properties.
     */
    public function __construct()
    {
        // Property will be properly initialized in the query method
        $this->user = null;
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(User $user): iterable
    {
        $user->load(['roles']);
        $this->user = $user;

        return [
            'user' => $user,
            'permission' => $user->getStatusPermission(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->user->exists ? 'Edit User' : 'Create User';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'User profile and privileges, including their associated role.';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.systems.users',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Impersonate user')
                ->icon('login')
                ->confirm('You can revert to your original state by logging out.')
                ->method('loginAs')
                ->canSee($this->user && $this->user->exists && \request()->user() && $this->user->id !== \request()->user()->id),

            Button::make('Remove')
                ->icon('trash')
                ->confirm('Once the account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.')
                ->method('remove')
                ->canSee($this->user && $this->user->exists),

            Button::make('Save')
                ->icon('check')
                ->method('save'),
        ];
    }

    /**
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [

            Layout::block(UserEditLayout::class)
                ->title('Profile Information')
                ->description('Update your account\'s profile information and email address.')
                ->commands(
                    Button::make('Save')
                        ->type(Color::DEFAULT())
                        ->icon('check')
                        ->canSee($this->user && $this->user->exists)
                        ->method('save')
                ),

            Layout::block(UserPasswordLayout::class)
                ->title('Password')
                ->description('Ensure your account is using a long, random password to stay secure.')
                ->commands(
                    Button::make('Save')
                        ->type(Color::DEFAULT())
                        ->icon('check')
                        ->canSee($this->user && $this->user->exists)
                        ->method('save')
                ),

            Layout::block(UserRoleLayout::class)
                ->title('Roles')
                ->description('A Role defines a set of tasks a user assigned the role is allowed to perform.')
                ->commands(
                    Button::make('Save')
                        ->type(Color::DEFAULT())
                        ->icon('check')
                        ->canSee($this->user && $this->user->exists)
                        ->method('save')
                ),

            Layout::block(RolePermissionLayout::class)
                ->title('Permissions')
                ->description('Allow the user to perform some actions that are not provided for by his roles')
                ->commands(
                    Button::make('Save')
                        ->type(Color::DEFAULT())
                        ->icon('check')
                        ->canSee($this->user && $this->user->exists)
                        ->method('save')
                ),

        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(User $user, Request $request)
    {
        $request->validate([
            'user.email' => [
                'required',
                Rule::unique(User::class, 'email')->ignore($user),
            ],
        ]);

        $permissions = collect($request->get('permissions'))
            ->map(fn ($value, $key) => [base64_decode($key) => $value])
            ->collapse()
            ->toArray();

        $user->when($request->filled('user.password'), function (Builder $builder) use ($request) {
            $builder->getModel()->password = Hash::make($request->input('user.password'));
        });

        $user
            ->fill($request->collect('user')->except(['password', 'permissions', 'roles'])->toArray())
            ->forceFill(['permissions' => $permissions])
            ->save();

        $user->replaceRoles($request->input('user.roles'));

        Toast::info('User was saved.');

        return redirect()->route('platform.systems.users');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Exception
     */
    public function remove(User $user)
    {
        $user->delete();

        Toast::info('User was removed');

        return redirect()->route('platform.systems.users');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginAs(User $user)
    {
        Impersonation::loginAs($user);

        Toast::info('You are now impersonating this user');

        return redirect()->route(config('platform.index'));
    }
}
