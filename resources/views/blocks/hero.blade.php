@php($image = \App\Support\Media::url($data['image'] ?? null))
<section class="relative overflow-hidden bg-brand-soft">
    <div @class(['mx-auto grid max-w-7xl items-center gap-8 px-5 py-12 sm:px-8 sm:py-16', 'lg:grid-cols-2' => $image])>
        <div @class(['max-w-3xl' => ! $image])>
            <h1 class="text-4xl font-bold leading-[1.08] tracking-tight text-stone-950 sm:text-6xl">{{ $data['heading'] ?? '' }}</h1>
            @if (filled($data['text'] ?? null))
                <p class="mt-5 max-w-2xl text-lg leading-8 text-stone-700 sm:text-xl">{{ $data['text'] }}</p>
            @endif
            @if (filled($data['button_label'] ?? null) && filled($data['button_url'] ?? null))
                <div class="mt-6">@include('partials.button', ['label' => $data['button_label'] ?? null, 'url' => $data['button_url'] ?? null])</div>
            @endif
        </div>
        @if ($image)
            <img src="{{ $image }}" alt="" class="w-full rounded-3xl object-cover lg:max-h-[30rem]">
        @endif
    </div>
</section>
