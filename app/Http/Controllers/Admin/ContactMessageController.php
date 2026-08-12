<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request, ContactService $contacts): View
    {
        $messages = $contacts->paginate(search: $request->string('search')->trim()->toString() ?: null);

        return view('admin.contacts.index', compact('messages'));
    }

    public function show(ContactMessage $contact, ContactService $contacts): View
    {
        $contacts->markRead($contact);

        return view('admin.contacts.show', compact('contact'));
    }

    public function destroy(ContactMessage $contact, ContactService $contacts): RedirectResponse
    {
        $contacts->delete($contact);

        return redirect()->route('admin.contacts.index')->with('success', __('Message deleted.'));
    }
}
