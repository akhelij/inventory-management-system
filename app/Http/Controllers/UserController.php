<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_USERS), 403);

        return view('users.index', [
            'users' => User::all(),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_USERS), 403);

        return view('users.create', [
            'roles' => Role::all(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_USERS), 403);

        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();

        $user = User::create($data);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = hexdec(uniqid()).'.'.$file->getClientOriginalExtension();
            $file->storeAs('profile/', $filename, 'public');
            $user->update(['photo' => $filename]);
        }

        if ($request->has('role_id')) {
            $user->assignRole(Role::find($request->role_id)->name);
        }

        return to_route('users.index')->with('success', 'New User has been created!');
    }

    public function show(User $user): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_USERS), 403);

        return view('users.show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_USERS), 403);

        return view('users.edit', [
            'user' => $user,
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_USERS), 403);

        $user->update(array_merge($request->except('photo'), [
            'warehouse_id' => $request->warehouse_id,
        ]));

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                unlink(public_path('storage/profile/').$user->photo);
            }

            $file = $request->file('photo');
            $fileName = hexdec(uniqid()).'.'.$file->getClientOriginalExtension();
            $file->storeAs('profile/', $fileName, 'public');

            $user->update(['photo' => $fileName]);
        }

        if ($request->has('role_id')) {
            $user->roles()->detach();
            $user->assignRole(Role::find($request->role_id)->name);
        }

        return to_route('users.index')->with('success', 'User has been updated!');
    }

    public function updatePassword(Request $request, string $username): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_USERS), 403);

        $validated = $request->validate([
            'password' => 'required_with:password_confirmation|min:6',
            'password_confirmation' => 'same:password|min:6',
        ]);

        User::where('username', $username)->update([
            'password' => Hash::make($validated['password']),
        ]);

        return to_route('users.index')->with('success', 'User has been updated!');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::DELETE_USERS), 403);

        if ($user->photo) {
            unlink(public_path('storage/profile/').$user->photo);
        }

        $user->delete();

        return to_route('users.index')->with('success', 'User has been deleted!');
    }

    public function activityLogs(): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::ACTIVITY_LOGS), 403);

        return view('users.activity_logs', [
            'activities' => Activity::with('causer')->latest()->paginate(50),
        ]);
    }
}
