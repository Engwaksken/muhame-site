@extends('layouts.public')

@php
    $phoneLink = $siteSettings->phoneLink();
    $mapEmbedUrl = $siteSettings->mapEmbedUrl();
    $socialLinks = $siteSettings->publicSocialLinks();

    // Contact details; only those filled in under Site settings → Contact & map are shown.
    $details = array_values(array_filter([
        $siteSettings->contact_email ? ['icon' => 'fas fa-envelope', 'label' => 'Email', 'value' => $siteSettings->contact_email, 'href' => 'mailto:'.$siteSettings->contact_email] : null,
        $siteSettings->contact_phone ? ['icon' => 'fas fa-phone', 'label' => 'Phone', 'value' => $siteSettings->contact_phone, 'href' => $phoneLink] : null,
        $siteSettings->contact_address ? ['icon' => 'fas fa-location-dot', 'label' => 'Address', 'value' => $siteSettings->contact_address, 'href' => $siteSettings->mapDirectionsUrl()] : null,
        $siteSettings->office_hours ? ['icon' => 'fas fa-clock', 'label' => 'Opening hours', 'value' => $siteSettings->office_hours, 'href' => null] : null,
    ]));
    $hasSidebar = $details || $mapEmbedUrl || $socialLinks;

    $labelClass = 'mb-1.5 block text-sm font-medium text-stone-700';
    $fieldClass = 'block w-full rounded-lg border bg-white px-3.5 text-[0.95rem] text-stone-900 placeholder:text-stone-400 shadow-sm transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/15';
@endphp

@section('title', 'Contact · '.$siteSettings->site_name)
@section('description', 'Get in touch with '.$siteSettings->site_name.'.')
@section('canonical', route('contact'))

@section('content')
    {{-- Page header --}}
    <header class="bg-brand-soft">
        <div class="mx-auto max-w-7xl px-5 py-6 sm:px-8 sm:py-8">
            <p class="mb-3 text-sm font-semibold uppercase tracking-[0.18em] text-brand-dark">Get in touch</p>
            <h1 class="text-4xl font-bold tracking-tight text-stone-950 sm:text-5xl">Contact us</h1>
            <p class="mt-3 max-w-2xl text-lg leading-8 text-stone-700">
                Have a question or an idea for working together? Send us a message and we'll get back to you as soon as we can.
            </p>
        </div>
    </header>

    <section class="mx-auto max-w-7xl px-5 py-6 sm:px-8 sm:py-8">
        <div @class(['grid items-start gap-8 lg:gap-12', 'lg:grid-cols-12' => $hasSidebar])>

            {{-- Left: message form --}}
            <div @class(['rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8', 'lg:col-span-7' => $hasSidebar, 'mx-auto w-full max-w-2xl' => ! $hasSidebar])>
                <h2 class="text-2xl font-bold tracking-tight text-stone-950">Send us a message</h2>
                <p class="mt-1 text-sm text-stone-500">Fields marked <span class="text-red-600">*</span> are required.</p>

                @if (session('status'))
                    <div role="status" class="mt-6 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                        <i class="fas fa-circle-check mt-0.5" aria-hidden="true"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @elseif ($errors->any())
                    <div role="alert" class="mt-6 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                        <i class="fas fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                        <span>Please check the highlighted fields and try again.</span>
                    </div>
                @endif

                <form action="{{ route('contact.store') }}" method="POST" class="mt-6 space-y-5" novalidate>
                    @csrf

                    {{-- Honeypot: hidden from people, filled in by bots. --}}
                    <div class="absolute -left-[9999px]" aria-hidden="true">
                        <label for="website">Leave this field empty</label>
                        <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="{{ $labelClass }}">Full name <span class="text-red-600" aria-hidden="true">*</span></label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name"
                                placeholder="e.g. Jane Namukasa"
                                class="{{ $fieldClass }} h-11 @error('name') border-red-500 @else border-stone-300 @enderror"
                                @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                            @error('name')<p id="name-error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="{{ $labelClass }}">Email address <span class="text-red-600" aria-hidden="true">*</span></label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email"
                                placeholder="you@example.com"
                                class="{{ $fieldClass }} h-11 @error('email') border-red-500 @else border-stone-300 @enderror"
                                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                            @error('email')<p id="email-error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="subject" class="{{ $labelClass }}">Subject</label>
                        <input id="subject" type="text" name="subject" value="{{ old('subject') }}" maxlength="150"
                            placeholder="What is your message about?"
                            class="{{ $fieldClass }} h-11 @error('subject') border-red-500 @else border-stone-300 @enderror">
                        @error('subject')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="message" class="{{ $labelClass }}">Message <span class="text-red-600" aria-hidden="true">*</span></label>
                        <textarea id="message" name="message" rows="5" required minlength="10" maxlength="5000"
                            placeholder="Tell us how we can help…"
                            class="{{ $fieldClass }} min-h-32 resize-y py-2.5 @error('message') border-red-500 @else border-stone-300 @enderror"
                            @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ old('message') }}</textarea>
                        @error('message')<p id="message-error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="inline-flex h-11 items-center gap-2 rounded-lg bg-brand px-6 text-[0.95rem] font-semibold text-brand-fg shadow-sm transition hover:bg-brand-dark focus:outline-none focus:ring-3 focus:ring-brand/30">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i> Send message
                    </button>
                </form>
            </div>

            {{-- Right: contact details, social and map --}}
            @if ($hasSidebar)
                <aside class="flex flex-col gap-6 lg:col-span-5">
                    @if ($details || $socialLinks)
                        <div class="rounded-2xl bg-ink p-6 text-white sm:p-8">
                            <h2 class="text-xl font-bold">Contact information</h2>

                            @if ($details)
                                <ul aria-label="Contact details" class="mt-6 space-y-5">
                                    @foreach ($details as $detail)
                                        <li class="flex gap-4">
                                            <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-brand text-brand-fg">
                                                <i class="{{ $detail['icon'] }}" aria-hidden="true"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <h3 class="text-xs font-semibold uppercase tracking-wider opacity-70">{{ $detail['label'] }}</h3>
                                                @if ($detail['href'])
                                                     <a href="{{ $detail['href'] }}" class="mt-0.5 block whitespace-pre-line break-words font-medium"
                                                        @if (str_starts_with($detail['href'], 'http')) target="_blank" rel="noopener noreferrer" @endif>{{ $detail['value'] }}</a>
                                                @else
                                                    <p class="mt-0.5 whitespace-pre-line font-medium">{{ $detail['value'] }}</p>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($socialLinks)
                                <div @class(['mt-7 border-t border-current/15 pt-6' => $details])>
                                    <h3 class="text-xs font-semibold uppercase tracking-wider opacity-70">Follow us</h3>
                                    <ul class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($socialLinks as $network => $url)
                                            <li>
                                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" title="{{ $network }}"
                                                    class="flex size-10 items-center justify-center rounded-lg border border-current/25 transition hover:bg-white/10">
                                                    <i class="{{ \App\Models\SiteSetting::socialIcon($network) }}" aria-hidden="true"></i>
                                                    <span class="sr-only">{{ $network }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($mapEmbedUrl)
                        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                            <div class="flex items-center justify-between gap-4 px-5 py-4">
                                <h2 class="flex items-center gap-2.5 font-bold text-stone-950">
                                    <i class="fas fa-map-location-dot text-brand-dark" aria-hidden="true"></i> Our location
                                </h2>
                                @if ($directionsUrl = $siteSettings->mapDirectionsUrl())
                                    <a href="{{ $directionsUrl }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-dark">
                                        <i class="fas fa-diamond-turn-right" aria-hidden="true"></i> Get directions
                                    </a>
                                @endif
                            </div>
                            <iframe src="{{ $mapEmbedUrl }}" title="Map showing the location of {{ $siteSettings->site_name }}"
                                class="block h-72 w-full border-0" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                        </div>
                    @endif
                </aside>
            @endif
        </div>
    </section>
@endsection
