<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_USERS), 403);
        $users = User::all();

        return view('users.index', [
            'users' => $users
        ]);
    }

    public function create()
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_USERS), 403);
        return view('users.create');
    }

    public function store(StoreUserRequest $request)
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_USERS), 403);
        $user = User::create($request->all());

        /**
         * Handle upload an image
         */
        if($request->hasFile('photo')){
            $file = $request->file('photo');
            $filename = hexdec(uniqid()).'.'.$file->getClientOriginalExtension();

            $file->storeAs('profile/', $filename, 'public');
            $user->update([
                'photo' => $filename
            ]);
        }

        if ($request->has('role_id')) {
            $user->assignRole(Role::find($request->role_id)->name);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'New User has been created!');
    }

    public function show(User $user)
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_USERS), 403);
        return view('users.show', [
           'user' => $user
        ]);
    }

    public function edit(User $user)
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_USERS), 403);
        return view('users.edit', [
            'user' => $user
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_USERS), 403);

//        if ($validatedData['email'] != $user->email) {
//            $validatedData['email_verified_at'] = null;
//        }

        $user->update($request->except('photo'));

        /**
         * Handle upload image with Storage.
         */
        if($request->hasFile('photo')){

            // Delete Old Photo
            if($user->photo){
                unlink(public_path('storage/profile/') . $user->photo);
            }

            // Prepare New Photo
            $file = $request->file('photo');
            $fileName = hexdec(uniqid()).'.'.$file->getClientOriginalExtension();

            // Store an image to Storage
            $file->storeAs('profile/', $fileName, 'public');

            // Save DB
            $user->update([
                'photo' => $fileName
            ]);
        }

        if ($request->has('role_id')) {
            $user->roles()->detach();
            $user->assignRole(Role::find($request->role_id)->name);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User has been updated!');
    }

    public function updatePassword(Request $request, String $username)
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_USERS), 403);
        # Validation
        $validated = $request->validate([
            'password' => 'required_with:password_confirmation|min:6',
            'password_confirmation' => 'same:password|min:6',
        ]);

        # Update the new Password
        User::where('username', $username)->update([
            'password' => Hash::make($validated['password'])
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User has been updated!');
    }

    public function destroy(User $user)
    {
        auth()->user()->can(PermissionEnum::DELETE_USERS);
        /**
         * Delete photo if exists.
         */
        if($user->photo){
            unlink(public_path('storage/profile/') . $user->photo);
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User has been deleted!');
    }

    public function activityLogs()
    {
        auth()->user()->can(PermissionEnum::ACTIVITY_LOGS);
        return view('users.activity_logs', [
            'activities' => Activity::with('causer')->latest()->paginate(50)
        ]);
    }
}
