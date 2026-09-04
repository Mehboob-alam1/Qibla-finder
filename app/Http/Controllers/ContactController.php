<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('pages.contact', [
            'email' => SiteSettings::get('contact_email', 'hello@example.com'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'subject' => ['nullable', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        try {
            ContactMessage::query()->create($data);
        } catch (\Throwable) {
            return back()->withErrors(['message' => __('Unable to save your message right now. Please try again later.')]);
        }

        return back()->with('status', __('Thank you. Your message has been received.'));
    }
}
