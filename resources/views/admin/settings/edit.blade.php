@extends('layouts.admin')
@section('title', 'Settings')
@section('heading', 'Site settings')

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" class="stat-card rounded-3xl p-6 grid md:grid-cols-2 gap-4 max-w-5xl">
    @csrf
    @method('PUT')
    @foreach ([
        'site_name' => 'Site name',
        'tagline' => 'Tagline',
        'contact_email' => 'Contact email',
        'contact_phone' => 'Contact phone',
        'address' => 'Address',
        'footer_text' => 'Footer text',
        'meta_title' => 'Default SEO title',
        'meta_description' => 'Default SEO description',
        'facebook' => 'Facebook URL',
        'instagram' => 'Instagram URL',
        'twitter' => 'X / Twitter URL',
        'youtube' => 'YouTube URL',
        'ga_id' => 'Google Analytics ID',
        'announcement' => 'Announcement bar',
    ] as $key => $label)
        <label class="text-sm {{ in_array($key, ['footer_text', 'meta_description', 'announcement'], true) ? 'md:col-span-2' : '' }}">{{ $label }}
            <input name="{{ $key }}" value="{{ old($key, $settings[$key] ?? '') }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5">
        </label>
    @endforeach
    <label class="text-sm">Default prayer method
        <select name="default_calculation_method" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5">
            @foreach ($methods as $key => $method)
                <option value="{{ $key }}" @selected(($settings['default_calculation_method'] ?? 'MWL') === $key)>{{ $method['name'] }}</option>
            @endforeach
        </select>
    </label>
    <fieldset class="md:col-span-2 rounded-3xl border border-forest/10 p-5 space-y-4">
        <legend class="font-display text-2xl text-forest px-2">AdSense</legend>
        <p class="text-sm text-forest/60">Ads stay light: one banner, one native in-article unit, and a full-screen ad at most once every 12 hours — never over the compass. Create units in AdSense, then paste the IDs here.</p>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="adsense_enabled" value="1" @checked(old('adsense_enabled', $settings['adsense_enabled'] ?? '') == '1')>
            Enable ads on the public site
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="adsense_preview" value="1" @checked(old('adsense_preview', $settings['adsense_preview'] ?? '') == '1')>
            Preview placements with fake ads (no Google ads, good for testing)
        </label>
        <label class="text-sm block">Publisher ID (ca-pub-…)
            <input name="adsense_client" value="{{ old('adsense_client', $settings['adsense_client'] ?? '') }}" placeholder="ca-pub-0000000000000000" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5">
        </label>
        <div class="grid md:grid-cols-2 gap-4">
            <label class="text-sm">Banner / full-screen ad slot
                <input name="adsense_banner_slot" value="{{ old('adsense_banner_slot', $settings['adsense_banner_slot'] ?? '') }}" placeholder="Display unit ID" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5">
            </label>
            <label class="text-sm">Native / in-article ad slot
                <input name="adsense_native_slot" value="{{ old('adsense_native_slot', $settings['adsense_native_slot'] ?? '') }}" placeholder="In-article unit ID (optional)" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5">
            </label>
        </div>
    </fieldset>
    <fieldset class="md:col-span-2 rounded-3xl border border-forest/10 p-5 space-y-4">
        <legend class="font-display text-2xl text-forest px-2">Header &amp; footer HTML</legend>
        <p class="text-sm text-forest/60">Paste verification tags, pixels, or extra scripts here. Use Header HTML for <code>&lt;meta&gt;</code> and <code>&lt;script&gt;</code> that belong in <code>&lt;head&gt;</code>. Footer HTML is inserted just before <code>&lt;/body&gt;</code>.</p>
        <label class="text-sm block">Google site verification
            <input name="google_site_verification" value="{{ old('google_site_verification', $settings['google_site_verification'] ?? '') }}" placeholder="jtIemhVXRpmISnRTllfMePhuh4tKciCLrzFRDSVF6wc" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5 font-mono text-sm">
            <span class="block mt-1 text-forest/50">Paste the content value, or the whole <code>&lt;meta name="google-site-verification" …&gt;</code> tag.</span>
        </label>
        <label class="text-sm block">Header HTML
            <textarea name="head_html" rows="6" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5 font-mono text-sm" placeholder="&lt;meta name=&quot;msvalidate.01&quot; content=&quot;…&quot;&gt;">{{ old('head_html', $settings['head_html'] ?? '') }}</textarea>
        </label>
        <label class="text-sm block">Footer HTML
            <textarea name="footer_html" rows="6" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5 font-mono text-sm" placeholder="&lt;script&gt;…&lt;/script&gt;">{{ old('footer_html', $settings['footer_html'] ?? '') }}</textarea>
        </label>
    </fieldset>
    <div class="md:col-span-2">
        <button class="bg-forest text-cream rounded-full px-6 py-3 font-semibold">Save settings</button>
    </div>
</form>
@endsection
