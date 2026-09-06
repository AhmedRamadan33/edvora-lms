<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChooseAccountTypeRequest;
use App\Services\InstructorApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChooseAccountTypeController extends Controller
{
    public function __construct(private InstructorApplicationService $instructorApplication)
    {
    }

    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()->roles()->exists()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return view('auth.choose-account-type');
    }

    public function store(ChooseAccountTypeRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->roles()->exists()) {
            $this->instructorApplication->applyAccountType($user, $request->validated()['account_type']);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
