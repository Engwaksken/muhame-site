@php($statColumns = ['1' => 'lg:grid-cols-1', '2' => 'lg:grid-cols-2', '3' => 'lg:grid-cols-3', '4' => 'lg:grid-cols-4'][(string) max(1, min(4, count($data['items'] ?? [])))])
<section class="bg-ink text-white">
    <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20">
        @if (filled($data['heading'] ?? null))
            <h2 class="mb-10 max-w-3xl text-3xl font-bold tracking-tight sm:text-4xl">{{ $data['heading'] }}</h2>
        @endif
        <dl class="grid gap-10 sm:grid-cols-2 {{ $statColumns }}">
            @foreach (($data['items'] ?? []) as $item)
                <div class="flex flex-col border-t-4 border-brand pt-6">
                    <dt class="order-2 mt-2 text-lg leading-7 opacity-85">{{ $item['label'] ?? '' }}</dt>
                    <dd class="order-1 text-5xl font-bold tracking-tight sm:text-6xl">{{ $item['value'] ?? '' }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>
