@php($columns = ['2' => 'sm:grid-cols-2', '3' => 'sm:grid-cols-2 lg:grid-cols-3', '4' => 'sm:grid-cols-2 lg:grid-cols-4'][(string) ($data['columns'] ?? 3)] ?? 'sm:grid-cols-2 lg:grid-cols-3')
@php($cardStyle = ['cards' => 'rounded-[10px] border border-t-0 border-stone-200 bg-white', 'numbered' => 'border-l-2 border-brand bg-transparent', 'minimal' => 'border-t border-stone-200 bg-transparent'][(string) ($data['layout'] ?? 'cards')] ?? 'rounded-[10px] border border-t-0 border-stone-200 bg-white')
@php($imageRatio = ['landscape' => 'aspect-[16/10]', 'square' => 'aspect-square', 'portrait' => 'aspect-[4/5]'][(string) ($data['image_ratio'] ?? 'landscape')] ?? 'aspect-[16/10]')
<section class="mx-auto max-w-7xl px-5 py-6 sm:px-8 sm:py-8">
    @if (filled($data['heading'] ?? null) || filled($data['intro'] ?? null))
        <div class="mb-6 max-w-3xl">
            @if (filled($data['heading'] ?? null))
                <h2 class="text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">{{ $data['heading'] }}</h2>
            @endif
            @if (filled($data['intro'] ?? null))
                <p class="mt-2 text-lg leading-8 text-stone-600">{{ $data['intro'] }}</p>
            @endif
        </div>
    @endif
    <div class="grid gap-6 {{ $columns }}">
        @foreach (($data['cards'] ?? []) as $card)
            @php($cardImage = \App\Support\Media::url($card['image'] ?? null))
            @php($cardUrl = \App\Support\SafeUrl::clean($card['url'] ?? null))
            @php($cardIcon = is_string($card['icon'] ?? null) && preg_match('/^fa-[a-z0-9]+(?:-[a-z0-9]+)*$/', $card['icon']) ? $card['icon'] : null)
            @php($alignment = ['left' => 'text-left', 'center' => 'text-center', 'right' => 'text-right'][$card['alignment'] ?? 'left'] ?? 'text-left')
            @php($iconAlignment = ['left' => 'self-start', 'center' => 'self-center', 'right' => 'self-end'][$card['alignment'] ?? 'left'] ?? 'self-start')
            <article class="group relative flex h-full flex-col overflow-hidden {{ $cardStyle }} transition hover:-translate-y-1 hover:shadow-lg">
                @if ($cardImage)
                    <div class="{{ $imageRatio }} overflow-hidden bg-stone-100">
                        <img src="{{ $cardImage }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                    </div>
                @else
                    <div class="h-1.5 bg-brand"></div>
                @endif
                <div class="flex flex-1 flex-col p-5 {{ $alignment }}">
                    @if (! $cardImage && $cardIcon)
                        <i class="fas {{ $cardIcon }} {{ $iconAlignment }} mb-4 text-4xl text-brand-dark" aria-hidden="true"></i>
                    @endif
                    <h3 class="text-2xl font-bold leading-snug text-stone-950">
                        @if ($cardUrl)
                            <a href="{{ $cardUrl }}" class="after:absolute after:inset-0 group-hover:text-brand-dark" @if (\App\Support\SafeUrl::isExternal($cardUrl)) target="_blank" rel="noopener noreferrer" @endif>{{ $card['title'] ?? '' }}</a>
                        @else
                            {{ $card['title'] ?? '' }}
                        @endif
                    </h3>
                    @if (filled($card['text'] ?? null))
                        <p class="mt-2 text-lg leading-8 text-stone-600">{{ $card['text'] }}</p>
                    @endif
                    @if (filled($card['source'] ?? null))
                        <p class="mt-3 text-sm leading-6 text-stone-500">{{ $card['source'] }}</p>
                    @endif
                    @if ($cardUrl)
                        <span class="{{ $iconAlignment }} mt-auto inline-flex items-center gap-1 pt-5 font-semibold text-brand-dark">{{ filled($card['link_label'] ?? null) ? $card['link_label'] : 'Read more' }} <span aria-hidden="true">&rarr;</span></span>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
</section>
