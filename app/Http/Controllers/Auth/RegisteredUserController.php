<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\ActivityLog;
use App\Models\InstructorProfile;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'locale' => session('locale', config('app.locale', 'en')),
        ]);

        $user->syncRoles([$data['account_type']]);

        if ($data['account_type'] === 'instructor') {
            InstructorProfile::query()->create([
                'user_id' => $user->id,
                'status' => 'pending',
            ]);

            $admins = User::role('admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new GenericNotification(
                    __(':name applied to become an instructor.', ['name' => $user->name]),
                    route('admin.instructors.index'),
                    __('New instructor application')
                ));
            }
        }

        event(new Registered($user));
        Auth::login($user);

        ActivityLog::record('auth.registered', $user);

        return redirect(route('dashboard', absolute: false));
    }
}
