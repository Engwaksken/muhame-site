<section class="bg-[#002d22] text-[#f7f3e8]">
    <div class="mx-auto max-w-6xl px-5 py-6 sm:px-8 sm:py-8">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#f4a340]">{{ $data['eyebrow'] ?? 'THE METHODOLOGY' }}</p>
        <div class="border-b border-white/20 pb-4 pt-3 text-center">
            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ $data['heading'] ?? '' }}</h2>
            @if (filled($data['intro'] ?? null))
                <p class="mx-auto mt-3 max-w-3xl text-base leading-7 text-white/75">{{ $data['intro'] }}</p>
            @endif
        </div>

        @if (filled($data['about_content'] ?? null))
            <div class="grid gap-4 border-b border-white/20 py-4 md:grid-cols-[8rem_1fr] md:gap-8">
                <h3 class="text-xs font-bold uppercase tracking-[0.18em] text-[#f4a340]">{{ $data['about_heading'] ?? 'ABOUT NeLP®' }}</h3>
                <p class="max-w-4xl text-sm leading-7 text-white/80">{{ $data['about_content'] }}</p>
            </div>
        @endif

        @if (count($data['highlights'] ?? []))
            <div class="grid border-b border-white/20 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($data['highlights'] as $highlight)
                    <article class="border-b border-white/20 px-5 py-4 first:pl-0 sm:border-r sm:odd:pl-0 sm:even:pr-0 lg:border-b-0 lg:even:px-5 lg:last:border-r-0">
                        <span class="mb-3 block text-lg text-[#f4a340]" aria-hidden="true">◎</span>
                        <h3 class="text-base font-bold leading-snug">{{ $highlight['title'] ?? '' }}</h3>
                        @if (filled($highlight['text'] ?? null))
                            <p class="mt-2 text-xs leading-5 text-white/70">{{ $highlight['text'] }}</p>
                        @endif
                        @if (filled($highlight['source'] ?? null))
                            <p class="mt-3 text-[11px] leading-4 text-[#f4a340]">{{ $highlight['source'] }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif

        @if (count($data['experiences'] ?? []))
            <div class="mt-5 border-y border-white/20">
                @foreach ($data['experiences'] as $experience)
                    <article class="grid gap-3 border-b border-white/20 py-4 last:border-0 sm:grid-cols-[3.5rem_10rem_1fr] sm:items-start sm:gap-5">
                        <span class="text-3xl font-bold leading-none text-[#f4a340]">{{ $experience['number'] ?? '' }}</span>
                        <h3 class="font-bold leading-snug">{{ $experience['title'] ?? '' }}</h3>
                        <div>
                            <p class="text-sm leading-6 text-white/80">{{ $experience['text'] ?? '' }}</p>
                            @if (filled($experience['evidence'] ?? null))
                                <p class="mt-2 text-[11px] leading-4 text-[#f4a340]">Evidence: {{ $experience['evidence'] }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if (count($data['journey'] ?? []))
            <div class="mt-5 border border-white/20 p-5 sm:p-6">
                <h3 class="text-xs font-bold uppercase tracking-[0.18em] text-[#f4a340]">The learner journey</h3>
                <ol class="mt-4 grid grid-cols-2 gap-y-6 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach ($data['journey'] as $index => $step)
                        <li class="relative border-t border-white/25 pt-4 text-sm font-semibold">
                            <span class="absolute -top-[5px] left-0 size-[9px] rounded-full bg-[#f4a340] ring-4 ring-[#002d22]" aria-hidden="true"></span>
                            <span class="mb-1 block text-[10px] tracking-[0.15em] text-white/50">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            {{ $step['label'] ?? '' }}
                        </li>
                    @endforeach
                </ol>
                @if (filled($data['journey_note'] ?? null))
                    <p class="mt-4 text-xs leading-5 text-white/70">{{ $data['journey_note'] }}</p>
                @endif
            </div>
        @endif
    </div>
</section>
