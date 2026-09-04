<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => SiteSettings::all(),
            'methods' => config('qibla.calculation_methods'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:80'],
            'tagline' => ['nullable', 'string', 'max:180'],
            'contact_email' => ['nullable', 'email', 'max:180'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'twitter' => ['nullable', 'url', 'max:255'],
            'youtube' => ['nullable', 'url', 'max:255'],
            'ga_id' => ['nullable', 'string', 'max:40'],
            'adsense_enabled' => ['sometimes', 'boolean'],
            'adsense_preview' => ['sometimes', 'boolean'],
            'adsense_client' => ['nullable', 'string', 'max:40', 'regex:/^(ca-pub-\d+)?$/'],
            'adsense_banner_slot' => ['nullable', 'string', 'max:20'],
            'adsense_native_slot' => ['nullable', 'string', 'max:20'],
            'default_calculation_method' => ['required', 'string', 'max:32'],
            'announcement' => ['nullable', 'string', 'max:255'],
        ]);

        $data['adsense_enabled'] = $request->boolean('adsense_enabled') ? '1' : '0';
        $data['adsense_preview'] = $request->boolean('adsense_preview') ? '1' : '0';
        $data['adsense_client'] = trim((string) $request->input('adsense_client', ''));
        $data['adsense_banner_slot'] = preg_replace('/\D+/', '', (string) $request->input('adsense_banner_slot', '')) ?? '';
        $data['adsense_native_slot'] = preg_replace('/\D+/', '', (string) $request->input('adsense_native_slot', '')) ?? '';

        SiteSettings::put($data);

        return back()->with('status', 'Settings saved.');
    }
}
