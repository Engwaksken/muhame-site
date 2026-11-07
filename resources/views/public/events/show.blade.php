@extends('layouts.public')

@php($eventImage = \App\Support\Media::url($event->image))
@php($registrationUrl = \App\Support\SafeUrl::clean($event->registration_url, allowRelative: false))
@php($onlineUrl = \App\Support\SafeUrl::clean($event->online_url, allowRelative: false))
@php($isPast = ($event->ends_at ?? $event->starts_at)->isPast())

@section('title', $event->title.' · '.$siteSettings->site_name)
@section('description', $description)
@section('canonical', route('events.show', $event->slug))
@if ($eventImage)
    @section('og_image', $eventImage)
@endif

@section('content')
    <header class="bg-brand-soft">
        <div class="mx-auto max-w-5xl px-5 py-10 sm:px-8 sm:py-14">
            <nav aria-label="Breadcrumb" class="text-sm font-semibold">
                <a href="{{ route('events.index') }}" class="text-brand-dark">Events</a>
            </nav>
            @if ($isPast)
                <p class="mt-5 inline-block rounded-full bg-stone-200 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-700">Past event</p>
            @endif
            <h1 class="mt-4 text-4xl font-bold leading-tight tracking-tight text-stone-950 sm:text-5xl">{{ $event->title }}</h1>
            @if ($event->summary)
                <p class="mt-5 max-w-3xl text-lg leading-8 text-stone-700 sm:text-xl">{{ $event->summary }}</p>
            @endif
        </div>
    </header>

    <div class="mx-auto grid max-w-5xl gap-12 px-5 py-12 sm:px-8 sm:py-16 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if ($eventImage)
                <img src="{{ $eventImage }}" alt="" class="mb-10 w-full rounded-3xl object-cover">
            @endif
            @if ($safeBody !== '')
                <div class="rich-content text-lg leading-8 text-stone-800">{!! $safeBody !!}</div>
            @endif
        </div>

        <aside class="lg:col-span-1">
            <dl class="space-y-5 rounded-2xl border border-stone-200 p-6">
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-stone-500">Date</dt>
                    <dd class="mt-1 font-semibold text-stone-900">
                        <time datetime="{{ $event->starts_at->toIso8601String() }}">{{ $event->starts_at->format('l j F Y') }}</time>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-stone-500">Time</dt>
                    <dd class="mt-1 font-semibold text-stone-900">
                        {{ $event->starts_at->format('H:i') }}@if ($event->ends_at) – {{ $event->ends_at->isSameDay($event->starts_at) ? $event->ends_at->format('H:i') : $event->ends_at->format('j M Y, H:i') }}@endif
                        <span class="block text-sm font-normal text-stone-500">{{ config('app.timezone') }}</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-stone-500">Where</dt>
                    <dd class="mt-1 font-semibold text-stone-900">{{ $event->location ?: 'Online' }}</dd>
                </div>
                @if (! $isPast && ($registrationUrl || $onlineUrl))
                    <div class="space-y-3 pt-2">
                        @if ($registrationUrl)
                            <a href="{{ $registrationUrl }}" target="_blank" rel="noopener noreferrer" class="block rounded-full bg-brand px-5 py-3 text-center font-semibold text-brand-fg hover:bg-brand-dark">Register</a>
                        @endif
                        @if ($onlineUrl)
                            <a href="{{ $onlineUrl }}" target="_blank" rel="noopener noreferrer" class="block rounded-full border-2 border-stone-900 px-5 py-3 text-center font-semibold text-stone-900 hover:bg-stone-100">Join online</a>
                        @endif
                    </div>
                @endif
            </dl>
        </aside>
    </div>
@endsection
