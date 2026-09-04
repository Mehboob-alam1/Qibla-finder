@php
    $adType = $type ?? 'banner';
    $preview = \App\Support\SiteSettings::adsensePreview();
    $client = \App\Support\SiteSettings::adsenseClient();
    $slot = \App\Support\SiteSettings::adsenseSlot($adType);
    $labels = [
        'banner' => 'Banner ad preview',
        'native' => 'Native in-article ad preview',
        'interstitial' => 'Full-screen ad preview',
    ];
@endphp
@if ($preview)
    <aside class="ad-unit ad-unit--{{ $adType }} ad-unit--preview" aria-label="{{ __('ui.Advertisement') }}">
        @if ($adType !== 'interstitial')
            <p class="ad-unit__label">{{ __('ui.Advertisement') }}</p>
        @endif
        <div class="ad-preview-box ad-preview-box--{{ $adType }}">{{ $labels[$adType] ?? 'Ad preview' }}</div>
    </aside>
@elseif (\App\Support\SiteSettings::adsenseEnabled() && $client !== '' && $slot !== '')
    <aside class="ad-unit ad-unit--{{ $adType }}" aria-label="{{ __('ui.Advertisement') }}">
        @if ($adType !== 'interstitial')
            <p class="ad-unit__label">{{ __('ui.Advertisement') }}</p>
        @endif
        @if ($adType === 'native')
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-client="{{ $client }}"
                 data-ad-slot="{{ $slot }}"
                 data-ad-format="fluid"
                 data-ad-layout="in-article"></ins>
        @else
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-client="{{ $client }}"
                 data-ad-slot="{{ $slot }}"
                 data-ad-format="{{ $adType === 'interstitial' ? 'rectangle' : 'horizontal' }}"
                 data-full-width-responsive="true"></ins>
        @endif
    </aside>
@endif
