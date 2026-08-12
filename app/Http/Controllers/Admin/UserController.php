<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('roles')
            ->when($request->role, fn ($q, $role) => $q->role($role))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search')->trim()->toString();
                $query->where(fn ($userQuery) => $userQuery
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function toggle(User $user): RedirectResponse
    {
        if ($user->hasRole('admin')) {
            return back()->with('error', __('Cannot deactivate admin.'));
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', __('User status updated.'));
    }
}
