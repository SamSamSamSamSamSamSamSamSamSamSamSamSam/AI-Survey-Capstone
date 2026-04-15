<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Mail\UserCredentialsMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    // -------------------------------------------------------------------------
    // Index — paginated list with search + role filter
    // -------------------------------------------------------------------------

    public function index(Request $request)
    {
        $query = User::with('roles')->withTrashed();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('user_id_number', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        if ($request->input('status') === 'deleted') {
            $query->onlyTrashed();
        } elseif ($request->input('status') !== 'all') {
            $query->whereNull('deleted_at');
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::orderBy('name')->get();

        // If it's an AJAX request, return only the table partial
        if ($request->ajax()) {
            return view('admin.users._table', compact('users'))->render();
        }

        return view('admin.users.index', compact('users', 'roles'));
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $plainPassword = $this->generatePassword();

        $user = User::create([
            'user_id_number' => $request->user_id_number,
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($plainPassword),
            'email_verified_at' => now(), // Admin-created accounts are pre-verified
        ]);

        // Assign roles
        $user->roles()->sync($request->input('roles', []));

        // Email credentials
        Mail::to($user->email)->send(new UserCredentialsMail($user, $plainPassword));

        return redirect()->route('admin.users.index')
                         ->with('success', "User {$user->name} created. Credentials sent to {$user->email}.");
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function show(User $user): View
    {
        $user->load('roles');
        return view('admin.users.show', compact('user'));
    }

    // -------------------------------------------------------------------------
    // Edit / Update
    // -------------------------------------------------------------------------

    public function edit(User $user): View
    {
        $user->load('roles');
        $roles = Role::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'user_id_number' => $request->user_id_number,
            'name'           => $request->name,
            'email'          => $request->email,
        ]);

        // Sync roles (replaces existing assignments)
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index')
                         ->with('success', "User {$user->name} updated successfully.");
    }

    // -------------------------------------------------------------------------
    // Reset Password
    // -------------------------------------------------------------------------

    public function resetPassword(User $user): RedirectResponse
    {
        $plainPassword = $this->generatePassword();

        $user->update(['password' => Hash::make($plainPassword)]);

        Mail::to($user->email)->send(new UserCredentialsMail($user, $plainPassword, isReset: true));

        return back()->with('success', "Password reset. New credentials sent to {$user->email}.");
    }

    // -------------------------------------------------------------------------
    // Soft delete / Restore / Force delete
    // -------------------------------------------------------------------------

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
                         ->with('success', "{$user->name} has been deactivated.");
    }

    public function restore(string $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.index')
                         ->with('success', "{$user->name} has been restored.");
    }

    public function forceDelete(string $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot permanently delete your own account.');
        }

        $user->roles()->detach();
        $user->forceDelete();

        return redirect()->route('admin.users.index')
                         ->with('success', 'User permanently deleted.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function generatePassword(int $length = 12): string
    {
        // Ensures at least one uppercase, lowercase, digit, special char
        $upper   = Str::upper(Str::random(2));
        $lower   = Str::lower(Str::random(4));
        $digits  = substr(str_shuffle('0123456789'), 0, 2);
        $special = substr(str_shuffle('!@#$%^&*'), 0, 2);
        $all     = str_shuffle($upper . $lower . $digits . $special);

        return substr($all, 0, $length);
    }
}
