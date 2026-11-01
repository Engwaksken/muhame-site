@extends('layouts.public')

@section('title', ($showPast ? 'Past events' : 'Events').' · '.$siteSettings->site_name)
@section('description', 'Upcoming and past events from '.$siteSettings->site_name.'.')
@section('canonical', $showPast ? route('events.index', ['when' => 'past']) : route('events.index'))

@section('content')
    <header class="bg-brand-soft">
        <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20">
            <h1 class="text-4xl font-bold tracking-tight text-stone-950 sm:text-5xl">Events</h1>
            <p class="mt-4 max-w-2xl text-lg leading-8 text-stone-700">Workshops, talks and gatherings, in person and online.</p>
            <nav aria-label="Event filter" class="mt-8">
                <ul class="flex flex-wrap gap-2">
                    @foreach ([false => ['Upcoming', route('events.index')], true => ['Past events', route('events.index', ['when' => 'past'])]] as $isPast => [$label, $url])
                        <li>
                            <a href="{{ $url }}" @class([
                                'inline-block rounded-full border px-4 py-2 text-sm font-semibold transition',
                                'border-brand bg-brand text-brand-fg' => $showPast === (bool) $isPast,
                                'border-stone-300 bg-white text-stone-700 hover:border-brand' => $showPast !== (bool) $isPast,
                            ]) @if ($showPast === (bool) $isPast) aria-current="page" @endif>{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </header>

    <div class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-16">
        @if ($events->isEmpty())
            <div class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
                <h2 class="text-xl font-semibold">{{ $showPast ? 'No past events yet' : 'No upcoming events right now' }}</h2>
                <p class="mt-2 text-stone-600">
                    @if ($showPast)
                        Check back after our first event.
                    @else
                        Check back soon, or <a href="#newsletter" class="font-semibold text-brand-dark underline">sign up for updates</a>.
                    @endif
                </p>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-2">
                @foreach ($events as $event)
                    @include('partials.event-card', ['event' => $event])
                @endforeach
            </div>

            <div class="mt-12">{{ $events->links() }}</div>
        @endif
    </div>
@endsection
