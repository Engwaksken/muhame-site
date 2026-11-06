@php($textWidth = ['standard' => 'max-w-3xl', 'wide' => 'max-w-6xl', 'centered' => 'mx-auto max-w-3xl text-center', 'split' => 'max-w-6xl columns-1 gap-12 lg:columns-2'][(string) ($data['layout'] ?? 'standard')] ?? 'max-w-3xl')
<section class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20">
    <div class="{{ $textWidth }}">
        @if (filled($data['heading'] ?? null))
            <h2 class="text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">{{ $data['heading'] }}</h2>
        @endif
        <div class="rich-content mt-6 text-lg leading-8 text-stone-700">{!! \App\Support\SafeHtml::clean($data['content'] ?? '') !!}</div>
        <div class="mt-8">@include('partials.button', ['label' => $data['button_label'] ?? null, 'url' => $data['button_url'] ?? null])</div>
    </div>
</section>
