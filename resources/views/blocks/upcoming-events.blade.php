@php($events = $data['events'])
<section class="bg-stone-50">
    <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20">
        <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
            <h2 class="text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">{{ filled($data['heading'] ?? null) ? $data['heading'] : 'Upcoming events' }}</h2>
            <a href="{{ route('events.index') }}" class="font-semibold text-brand-dark hover:underline">All events <span aria-hidden="true">&rarr;</span></a>
        </div>
        @if ($events->isEmpty())
            <p class="text-lg text-stone-600">No upcoming events right now. Check back soon, or <a href="#newsletter" class="font-semibold text-brand-dark underline">sign up for updates</a>.</p>
        @else
            <div class="grid gap-6 lg:grid-cols-3">
                @foreach ($events as $event)
                    @include('partials.event-card', ['event' => $event])
                @endforeach
            </div>
        @endif
    </div>
</section>
