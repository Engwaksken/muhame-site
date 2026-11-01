{{-- Event card. Expects $event. --}}
<article class="group relative flex h-full gap-5 rounded-2xl border border-stone-200 bg-white p-6 transition hover:-translate-y-1 hover:shadow-lg">
    <div class="flex h-20 w-18 shrink-0 flex-col items-center justify-center rounded-xl bg-brand text-brand-fg">
        <span class="text-xs font-bold uppercase tracking-wider">{{ $event->starts_at->format('M') }}</span>
        <span class="text-3xl font-bold leading-none">{{ $event->starts_at->format('j') }}</span>
    </div>
    <div class="flex min-w-0 flex-col">
        <h3 class="text-lg font-bold leading-snug text-stone-950">
            <a href="{{ route('events.show', $event->slug) }}" class="after:absolute after:inset-0 group-hover:text-brand-dark">{{ $event->title }}</a>
        </h3>
        <p class="mt-1 text-sm text-stone-600">
            {{ $event->starts_at->format('l j F Y · H:i') }}
            · {{ $event->location ?: 'Online' }}
        </p>
        @if ($event->summary)
            <p class="mt-2 text-sm leading-6 text-stone-600">{{ \Illuminate\Support\Str::limit($event->summary, 140) }}</p>
        @endif
    </div>
</article>
