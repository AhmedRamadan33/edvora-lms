<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\StoreContactMessageRequest;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('contact.show');
    }

    public function store(StoreContactMessageRequest $request, ContactService $contacts): RedirectResponse
    {
        $contacts->submit($request->validated());

        return back()->with('success', __('Your message has been sent successfully.'));
    }
}
