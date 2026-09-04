@php
    $shareUrl = $shareUrl ?? url()->current();
    $shareTitle = $shareTitle ?? ($siteName ?? config('app.name'));
    $shareText = $shareText ?? __('ui.share_text');
    $encodedUrl = rawurlencode($shareUrl);
    $encodedText = rawurlencode($shareText.' '.$shareUrl);
@endphp
<div data-share class="relative inline-flex" data-share-url="{{ $shareUrl }}" data-share-title="{{ $shareTitle }}" data-share-text="{{ $shareText }}">
    <button data-share-open type="button" class="{{ $shareClass ?? 'border border-white/20 px-5 py-2.5 rounded-full' }}" aria-haspopup="true" aria-expanded="false">
        {{ __('ui.Share') }}
    </button>
    <div data-share-menu hidden class="absolute {{ $shareMenuClass ?? ($shareAlign ?? 'end-0').' top-full mt-2' }} w-56 rounded-2xl bg-card text-ink shadow-xl border border-forest/10 p-2 z-50">
        <p class="px-3 py-2 text-[11px] uppercase tracking-widest text-forest/50">{{ __('ui.Share this site') }}</p>
        <button data-share-native type="button" class="w-full text-start px-3 py-2 rounded-xl hover:bg-sand text-sm">{{ __('ui.Share') }}…</button>
        <a data-share-network="whatsapp" class="block px-3 py-2 rounded-xl hover:bg-sand text-sm" href="https://wa.me/?text={{ $encodedText }}" target="_blank" rel="noopener noreferrer">{{ __('ui.WhatsApp') }}</a>
        <a data-share-network="telegram" class="block px-3 py-2 rounded-xl hover:bg-sand text-sm" href="https://t.me/share/url?url={{ $encodedUrl }}&text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener noreferrer">{{ __('ui.Telegram') }}</a>
        <a data-share-network="facebook" class="block px-3 py-2 rounded-xl hover:bg-sand text-sm" href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedUrl }}" target="_blank" rel="noopener noreferrer">{{ __('ui.Facebook') }}</a>
        <a data-share-network="x" class="block px-3 py-2 rounded-xl hover:bg-sand text-sm" href="https://twitter.com/intent/tweet?url={{ $encodedUrl }}&text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener noreferrer">{{ __('ui.X') }}</a>
        <a data-share-network="email" class="block px-3 py-2 rounded-xl hover:bg-sand text-sm" href="mailto:?subject={{ rawurlencode($shareTitle) }}&body={{ $encodedText }}">{{ __('ui.Email') }}</a>
        <button data-copy type="button" data-label="{{ __('ui.Copy link') }}" data-copied="{{ __('ui.Link copied') }}" class="w-full text-start px-3 py-2 rounded-xl hover:bg-sand text-sm">{{ __('ui.Copy link') }}</button>
    </div>
</div>
