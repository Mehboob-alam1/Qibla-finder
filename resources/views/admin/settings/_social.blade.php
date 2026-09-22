<fieldset id="social-profiles" class="md:col-span-2 rounded-3xl border-2 border-gold/40 bg-gold/5 p-5 space-y-4">
    <legend class="font-display text-2xl text-forest px-2">Social profiles</legend>
    <div class="rounded-2xl border border-forest/15 bg-card p-4 space-y-3">
        <p class="text-sm font-semibold text-forest">Where to show social buttons</p>
        <p class="text-sm text-forest/60">Turn each location on or off. Profile URLs below only appear where enabled.</p>
        <div class="grid sm:grid-cols-2 gap-3">
            <label class="admin-check-card flex items-start gap-3 rounded-xl border border-forest/15 bg-cream/80 p-3 cursor-pointer">
                <input type="checkbox" class="admin-checkbox mt-0.5" name="social_show_header" value="1" @checked(old('social_show_header', $settings['social_show_header'] ?? '1') == '1')>
                <span>
                    <span class="block text-sm font-semibold text-forest">Header</span>
                    <span class="block text-xs text-forest/60 mt-0.5">Top bar (tablet/desktop) and mobile menu</span>
                </span>
            </label>
            <label class="admin-check-card flex items-start gap-3 rounded-xl border border-forest/15 bg-cream/80 p-3 cursor-pointer">
                <input type="checkbox" class="admin-checkbox mt-0.5" name="social_show_footer" value="1" @checked(old('social_show_footer', $settings['social_show_footer'] ?? '1') == '1')>
                <span>
                    <span class="block text-sm font-semibold text-forest">Footer</span>
                    <span class="block text-xs text-forest/60 mt-0.5">Site footer and Contact page</span>
                </span>
            </label>
        </div>
    </div>
    <p class="text-sm text-forest/60">Profile URLs — leave blank to hide that network.</p>
    <div class="grid sm:grid-cols-2 gap-4">
        @foreach ($socialNetworks as $key => $label)
            <label class="text-sm">{{ $label }}
                <input type="url" name="{{ $key }}" value="{{ old($key, $settings[$key] ?? '') }}" placeholder="https://…" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5">
            </label>
        @endforeach
    </div>
</fieldset>
