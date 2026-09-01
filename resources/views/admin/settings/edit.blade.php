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
    <div class="md:col-span-2">
        <button class="bg-forest text-cream rounded-full px-6 py-3 font-semibold">Save settings</button>
    </div>
</form>
@endsection
