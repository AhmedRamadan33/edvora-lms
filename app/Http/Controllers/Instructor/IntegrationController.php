<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Services\GoogleMeetService;
use App\Services\ZoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(ZoomService $zoom, GoogleMeetService $googleMeet): View
    {
        $zoomConnection = $zoom->connectionFor(auth()->user());
        $googleConnection = $googleMeet->connectionFor(auth()->user());

        return view('instructor.integrations.index', [
            'zoomConfigured' => $zoom->isConfigured(),
            'googleConfigured' => $googleMeet->isConfigured(),
            'zoomConnection' => $zoomConnection,
            'googleConnection' => $googleConnection,
        ]);
    }

    public function redirectToZoom(ZoomService $zoom): RedirectResponse
    {
        abort_unless($zoom->isConfigured(), 404);

        return redirect()->away($zoom->authorizationUrl(auth()->user()));
    }

    public function zoomCallback(Request $request, ZoomService $zoom): RedirectResponse
    {
        if (! $this->stateIsValid($request, 'zoom_oauth_state')) {
            return redirect()->route('instructor.integrations.index')->with('error', __('Invalid or expired authorization request. Please try connecting again.'));
        }

        if (! $request->filled('code')) {
            return redirect()->route('instructor.integrations.index')->with('error', __('Zoom authorization was cancelled or failed.'));
        }

        try {
            $zoom->handleCallback(auth()->user(), $request->string('code')->toString());
        } catch (\Throwable $e) {
            return redirect()->route('instructor.integrations.index')->with('error', __('Unable to connect your Zoom account. Please try again.'));
        }

        return redirect()->route('instructor.integrations.index')->with('success', __('Zoom account connected.'));
    }

    public function disconnectZoom(ZoomService $zoom): RedirectResponse
    {
        $zoom->disconnect(auth()->user());

        return back()->with('success', __('Zoom account disconnected.'));
    }

    public function redirectToGoogle(GoogleMeetService $googleMeet): RedirectResponse
    {
        abort_unless($googleMeet->isConfigured(), 404);

        return redirect()->away($googleMeet->authorizationUrl(auth()->user()));
    }

    public function googleCallback(Request $request, GoogleMeetService $googleMeet): RedirectResponse
    {
        if (! $this->stateIsValid($request, 'google_oauth_state')) {
            return redirect()->route('instructor.integrations.index')->with('error', __('Invalid or expired authorization request. Please try connecting again.'));
        }

        if (! $request->filled('code')) {
            return redirect()->route('instructor.integrations.index')->with('error', __('Google authorization was cancelled or failed.'));
        }

        try {
            $googleMeet->handleCallback(auth()->user(), $request->string('code')->toString());
        } catch (\Throwable $e) {
            return redirect()->route('instructor.integrations.index')->with('error', __('Unable to connect your Google account. Please try again.'));
        }

        return redirect()->route('instructor.integrations.index')->with('success', __('Google account connected.'));
    }

    public function disconnectGoogle(GoogleMeetService $googleMeet): RedirectResponse
    {
        $googleMeet->disconnect(auth()->user());

        return back()->with('success', __('Google account disconnected.'));
    }

    protected function stateIsValid(Request $request, string $sessionKey): bool
    {
        $state = $request->query('state');
        $expected = $request->session()->pull($sessionKey);

        if (! $state || ! $expected || ! hash_equals($expected, $state)) {
            return false;
        }

        try {
            [$userId] = explode('|', Crypt::decryptString($state), 2);
        } catch (\Throwable $e) {
            return false;
        }

        return (int) $userId === auth()->id();
    }
}
