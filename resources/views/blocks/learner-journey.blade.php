<section class="bg-[#002d22] px-5 py-6 text-white sm:px-8 sm:py-8">
    <div class="mx-auto max-w-6xl border border-white/20 px-5 py-5 sm:px-8 sm:py-6">
        <h2 class="text-xs font-bold uppercase tracking-[0.18em] text-[#f4a340]">{{ $data['heading'] ?? 'THE LEARNER JOURNEY' }}</h2>
        @php($steps = array_values($data['steps'] ?? []))
        @php($stepColumns = [2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4', 5 => 'lg:grid-cols-5', 6 => 'lg:grid-cols-6'][min(count($steps), 6)] ?? 'lg:grid-cols-6')
        <ol class="relative mt-7 grid grid-cols-2 gap-y-7 sm:grid-cols-3 {{ $stepColumns }}">
            <span class="absolute left-2 right-2 top-[4px] hidden h-px bg-white/25 lg:block" aria-hidden="true"></span>
            @foreach ($steps as $index => $step)
                <li class="relative z-10">
                    <span class="mb-5 block size-3 rounded-full bg-[#f4a340] ring-4 ring-[#002d22]" aria-hidden="true"></span>
                    <span class="block text-[11px] font-semibold tracking-[0.14em] text-[#f4a340]">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="mt-1 block text-sm font-semibold leading-5">{{ $step['label'] ?? '' }}</span>
                </li>
            @endforeach
        </ol>
        @if (filled($data['note'] ?? null))
            <p class="mt-7 text-sm leading-6 text-white/75">{{ $data['note'] }}</p>
        @endif
    </div>
</section>
