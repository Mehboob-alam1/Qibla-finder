<section id="setup" class="mx-auto max-w-6xl px-4 {{ $helpClass ?? 'mt-20' }}">
    <p class="text-gold uppercase tracking-[0.2em] text-xs">{{ __('ui.help_kicker') }}</p>
    <h2 class="font-display text-5xl mt-2 text-forest">{{ __('ui.help_title') }}</h2>
    <p class="mt-4 text-forest/75 max-w-3xl">{{ __('ui.help_intro') }}</p>

    <ol class="mt-8 grid md:grid-cols-2 gap-4 list-none">
        @foreach ([
            ['01', __('ui.help_step_1')],
            ['02', __('ui.help_step_2')],
            ['03', __('ui.help_step_3')],
            ['04', __('ui.help_step_4')],
            ['05', __('ui.help_step_5')],
        ] as [$n, $text])
            <li class="stat-card rounded-3xl p-5 flex gap-4">
                <span class="font-display text-3xl text-gold">{{ $n }}</span>
                <p class="text-forest/80">{{ $text }}</p>
            </li>
        @endforeach
    </ol>

    <div class="mt-10 grid lg:grid-cols-2 gap-4">
        <article class="stat-card rounded-3xl p-6">
            <h3 class="font-semibold text-forest text-lg">{{ __('ui.help_devices_title') }}</h3>
            <p class="mt-3 text-forest/70 leading-relaxed">{{ __('ui.help_devices_body') }}</p>
        </article>
        <article class="stat-card rounded-3xl p-6">
            <h3 class="font-semibold text-forest text-lg">{{ __('ui.help_broken_title') }}</h3>
            <p class="mt-3 text-forest/70 leading-relaxed">{{ __('ui.help_broken_body') }}</p>
        </article>
        <article class="stat-card rounded-3xl p-6">
            <h3 class="font-semibold text-forest text-lg">{{ __('ui.help_chrome_title') }}</h3>
            <p class="mt-3 text-forest/70">{{ __('ui.help_chrome_1') }}</p>
            <p class="mt-2"><code class="block break-all rounded-2xl bg-sand px-4 py-3 text-sm text-forest">{{ __('ui.help_chrome_flag') }}</code></p>
            <p class="mt-3 text-forest/70 leading-relaxed">{{ __('ui.help_chrome_2') }}</p>
        </article>
        <article class="stat-card rounded-3xl p-6">
            <h3 class="font-semibold text-forest text-lg">{{ __('ui.help_edge_title') }}</h3>
            <p class="mt-3 text-forest/70">{{ __('ui.help_edge_1') }}</p>
            <p class="mt-2"><code class="block break-all rounded-2xl bg-sand px-4 py-3 text-sm text-forest">{{ __('ui.help_edge_flag') }}</code></p>
            <p class="mt-3 text-forest/70 leading-relaxed">{{ __('ui.help_edge_2') }}</p>
        </article>
        <article class="stat-card rounded-3xl p-6">
            <h3 class="font-semibold text-forest text-lg">{{ __('ui.help_safari_title') }}</h3>
            <p class="mt-3 text-forest/70 leading-relaxed">{{ __('ui.help_safari_intro') }}</p>
            <ul class="mt-3 space-y-2 text-forest/70 leading-relaxed list-disc ps-5">
                <li>{{ __('ui.help_safari_ios') }}</li>
                <li>{{ __('ui.help_safari_mac') }}</li>
            </ul>
        </article>
        <article class="stat-card rounded-3xl p-6">
            <h3 class="font-semibold text-forest text-lg">{{ __('ui.help_camera_title') }}</h3>
            <p class="mt-3 text-forest/70 leading-relaxed">{{ __('ui.help_camera_body') }}</p>
        </article>
        <article class="stat-card rounded-3xl p-6">
            <h3 class="font-semibold text-forest text-lg">{{ __('ui.help_accuracy_title') }}</h3>
            <p class="mt-3 text-forest/70 leading-relaxed">{{ __('ui.help_accuracy_body') }}</p>
        </article>
    </div>
</section>
