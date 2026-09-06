<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\InstructorApplicationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(private InstructorApplicationService $instructorApplication)
    {
    }

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

        $this->instructorApplication->applyAccountType($user, $data['account_type']);

        event(new Registered($user));
        Auth::login($user);

        ActivityLog::record('auth.registered', $user);

        return redirect(route('dashboard', absolute: false));
    }
}
