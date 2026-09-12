<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\QiblaDisplay;
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
            'google_site_verification' => ['nullable', 'string', 'max:400'],
            'bing_site_verification' => ['nullable', 'string', 'max:400'],
            'head_html' => ['nullable', 'string', 'max:20000'],
            'footer_html' => ['nullable', 'string', 'max:20000'],
            'qibla_vibration' => ['sometimes', 'boolean'],
            'qibla_audio' => ['sometimes', 'boolean'],
            'qibla_update_interval' => ['required', 'integer', 'min:5', 'max:3600'],
            'qibla_display_mode' => ['required', 'in:compass,arrow,camera'],
        ]);

        $data['adsense_enabled'] = $request->boolean('adsense_enabled') ? '1' : '0';
        $data['adsense_preview'] = $request->boolean('adsense_preview') ? '1' : '0';
        $data['adsense_client'] = trim((string) $request->input('adsense_client', ''));
        $data['adsense_banner_slot'] = preg_replace('/\D+/', '', (string) $request->input('adsense_banner_slot', '')) ?? '';
        $data['adsense_native_slot'] = preg_replace('/\D+/', '', (string) $request->input('adsense_native_slot', '')) ?? '';
        $data['google_site_verification'] = SiteSettings::normalizeVerificationToken(
            (string) $request->input('google_site_verification', ''),
        );
        $data['bing_site_verification'] = SiteSettings::normalizeVerificationToken(
            (string) $request->input('bing_site_verification', ''),
        ) ?: SiteSettings::bingSiteVerification();
        $data['head_html'] = (string) $request->input('head_html', '');
        $data['footer_html'] = (string) $request->input('footer_html', '');
        $data['qibla_vibration'] = $request->boolean('qibla_vibration') ? '1' : '0';
        $data['qibla_audio'] = $request->boolean('qibla_audio') ? '1' : '0';
        $data['qibla_update_interval'] = (string) max(5, min(3600, (int) $request->input('qibla_update_interval', 300)));
        $data['qibla_display_mode'] = QiblaDisplay::mode($request->input('qibla_display_mode'));

        SiteSettings::put($data);

        return back()->with('status', 'Settings saved.');
    }
}
