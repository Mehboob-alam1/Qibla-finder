<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        try {
            if (Auth::check() && Auth::user()?->is_admin) {
                return redirect()->route('admin.dashboard');
            }
        } catch (Throwable) {
            // Continue to the login form if the database is still starting.
        }

        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            if (! Auth::attempt($credentials, $request->boolean('remember'))) {
                return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
            }
        } catch (Throwable) {
            return back()->withErrors([
                'email' => 'The database is not ready yet. Refresh this page and try again in a few seconds.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        if (! Auth::user()?->is_admin) {
            Auth::logout();

            return back()->withErrors(['email' => 'Administrator access is required.']);
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
