@php($columns = ['2' => 'md:grid-cols-2', '3' => 'md:grid-cols-2 lg:grid-cols-3', '4' => 'md:grid-cols-2 xl:grid-cols-4'][(string) ($data['columns'] ?? 3)] ?? 'md:grid-cols-2 lg:grid-cols-3')
<section class="bg-[#f3f0e8] px-5 py-6 sm:px-8 sm:py-8">
    <div class="mx-auto max-w-7xl">
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
            @foreach (($data['members'] ?? []) as $member)
                @php($portrait = \App\Support\Media::url($member['image'] ?? null))
                <article class="border border-[#d3cbbb] bg-[#fffdfa] p-6 sm:p-8 lg:p-10">
                    @if ($portrait)
                        <img src="{{ $portrait }}" alt="{{ $member['name'] ?? '' }}" loading="lazy" class="mb-8 size-24 rounded-full border border-stone-200 object-cover">
                    @endif
                    @if (filled($member['name'] ?? null))
                        <h3 class="team-member-name text-3xl font-semibold leading-tight text-stone-950">{{ $member['name'] }}</h3>
                    @endif
                    @if (filled($member['role'] ?? null))
                        <p class="mt-2 text-sm font-semibold uppercase leading-6 tracking-widest text-brand-dark">{{ $member['role'] }}</p>
                    @endif
                    @if (filled($member['bio'] ?? null))
                        <p class="mt-6 whitespace-pre-line text-lg leading-9 text-stone-600">{{ $member['bio'] }}</p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
