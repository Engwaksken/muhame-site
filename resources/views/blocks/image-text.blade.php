@php($image = \App\Support\Media::url($data['image'] ?? null))
@php($imageRight = ($data['image_position'] ?? 'left') === 'right')
<section class="mx-auto max-w-7xl px-5 py-10 sm:px-8 sm:py-14">
    <div class="grid items-center gap-8 lg:grid-cols-2 lg:gap-12">
        @if ($image)
            <img src="{{ $image }}" alt="" loading="lazy" @class(['aspect-[4/3] w-full rounded-3xl object-cover', 'lg:order-last' => $imageRight])>
        @endif
        <div>
            <h2 class="text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">{{ $data['heading'] ?? '' }}</h2>
            @if (filled($data['content'] ?? null))
                <div class="rich-content mt-5 text-lg leading-8 text-stone-700">{!! \App\Support\SafeHtml::clean($data['content']) !!}</div>
            @endif
            @if (filled($data['button_label'] ?? null) && filled($data['button_url'] ?? null))
                <div class="mt-6">@include('partials.button', ['label' => $data['button_label'] ?? null, 'url' => $data['button_url'] ?? null])</div>
            @endif
        </div>
    </div>
</section>
