<section class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-10">
    <div class="flex flex-col gap-6 rounded-3xl bg-brand px-6 py-8 text-brand-fg sm:px-10 sm:py-10 lg:flex-row lg:items-center lg:justify-between">
        <div class="max-w-2xl">
            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ $data['heading'] ?? '' }}</h2>
            @if (filled($data['text'] ?? null))
                <p class="mt-3 text-lg leading-8 opacity-90">{{ $data['text'] }}</p>
            @endif
        </div>
        <div class="shrink-0">@include('partials.button', ['label' => $data['button_label'] ?? null, 'url' => $data['button_url'] ?? null, 'variant' => 'light'])</div>
    </div>
</section>
