@extends('layouts.public')

@php
    $phoneLink = $siteSettings->phoneLink();
    $mapEmbedUrl = $siteSettings->mapEmbedUrl();
    $socialLinks = $siteSettings->publicSocialLinks();

    // Contact detail cards; only the ones filled in under Site settings → Contact & map are shown.
    $details = array_values(array_filter([
        $siteSettings->contact_email ? ['icon' => 'fas fa-envelope', 'label' => 'Email us', 'value' => $siteSettings->contact_email, 'href' => 'mailto:'.$siteSettings->contact_email] : null,
        $siteSettings->contact_phone ? ['icon' => 'fas fa-phone', 'label' => 'Call us', 'value' => $siteSettings->contact_phone, 'href' => $phoneLink] : null,
        $siteSettings->contact_address ? ['icon' => 'fas fa-location-dot', 'label' => 'Visit us', 'value' => $siteSettings->contact_address, 'href' => $siteSettings->mapDirectionsUrl()] : null,
        $siteSettings->office_hours ? ['icon' => 'fas fa-clock', 'label' => 'Opening hours', 'value' => $siteSettings->office_hours, 'href' => null] : null,
    ]));
    $detailColumns = [1 => 'sm:grid-cols-1', 2 => 'sm:grid-cols-2', 3 => 'sm:grid-cols-2 lg:grid-cols-3', 4 => 'sm:grid-cols-2 lg:grid-cols-4'][count($details)] ?? '';
    $hasSidebar = $mapEmbedUrl || $socialLinks;

    $inputClass = 'mt-2 w-full rounded-xl border bg-white px-4 py-3 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20';
@endphp

@section('title', 'Contact · '.$siteSettings->site_name)
@section('description', 'Get in touch with '.$siteSettings->site_name.'.')
@section('canonical', route('contact'))

@section('content')
    {{-- Page header --}}
    <header class="bg-brand-soft">
        <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20">
            <p class="mb-3 text-sm font-semibold uppercase tracking-[0.18em] text-brand-dark">Get in touch</p>
            <h1 class="text-4xl font-bold tracking-tight text-stone-950 sm:text-5xl">Contact us</h1>
            <p class="mt-4 max-w-2xl text-lg leading-8 text-stone-700">
                Have a question or an idea for working together? Send us a message and we'll get back to you as soon as we can.
            </p>
        </div>
    </header>

    {{-- Contact details grid --}}
    @if ($details)
        <section aria-label="Contact details" class="mx-auto max-w-7xl px-5 sm:px-8">
            <ul class="-mt-8 grid gap-4 {{ $detailColumns }}">
                @foreach ($details as $detail)
                    <li class="flex gap-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand text-lg text-brand-fg">
                            <i class="{{ $detail['icon'] }}" aria-hidden="true"></i>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-sm font-semibold uppercase tracking-wider text-stone-500">{{ $detail['label'] }}</h2>
                            @if ($detail['href'])
                                <a href="{{ $detail['href'] }}" class="mt-1 block whitespace-pre-line break-words font-semibold text-stone-950 hover:text-brand-dark"
                                    @if (str_starts_with($detail['href'], 'http')) target="_blank" rel="noopener noreferrer" @endif>{{ $detail['value'] }}</a>
                            @else
                                <p class="mt-1 whitespace-pre-line font-semibold text-stone-950">{{ $detail['value'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Form + map grid --}}
    <section class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20">
        <div @class(['grid gap-8', 'lg:grid-cols-5' => $hasSidebar])>
            {{-- Message form --}}
            <div @class(['rounded-3xl border border-stone-200 bg-white p-6 sm:p-10', 'lg:col-span-3' => $hasSidebar, 'mx-auto w-full max-w-3xl' => ! $hasSidebar])>
                <h2 class="flex items-center gap-3 text-2xl font-bold tracking-tight text-stone-950">
                    <i class="fas fa-paper-plane text-brand" aria-hidden="true"></i> Send us a message
                </h2>

                @if (session('status'))
                    <div role="status" class="mt-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-medium text-emerald-800">
                        <i class="fas fa-circle-check mt-1" aria-hidden="true"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @elseif ($errors->any())
                    <div role="alert" class="mt-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 font-medium text-red-800">
                        <i class="fas fa-circle-exclamation mt-1" aria-hidden="true"></i>
                        <span>Please check the highlighted fields and try again.</span>
                    </div>
                @endif

                <form action="{{ route('contact.store') }}" method="POST" class="mt-8 space-y-6" novalidate>
                    @csrf

                    {{-- Honeypot: hidden from people, filled in by bots. --}}
                    <div class="absolute -left-[9999px]" aria-hidden="true">
                        <label for="website">Leave this field empty</label>
                        <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-stone-800">Name</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name"
                                class="{{ $inputClass }} @error('name') border-red-500 @else border-stone-300 @enderror"
                                @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                            @error('name')<p id="name-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-stone-800">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email"
                                class="{{ $inputClass }} @error('email') border-red-500 @else border-stone-300 @enderror"
                                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                            @error('email')<p id="email-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-semibold text-stone-800">Subject <span class="font-normal text-stone-500">(optional)</span></label>
                        <input id="subject" type="text" name="subject" value="{{ old('subject') }}" maxlength="150"
                            class="{{ $inputClass }} @error('subject') border-red-500 @else border-stone-300 @enderror">
                        @error('subject')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-semibold text-stone-800">Message</label>
                        <textarea id="message" name="message" rows="7" required minlength="10" maxlength="5000"
                            class="{{ $inputClass }} @error('message') border-red-500 @else border-stone-300 @enderror"
                            @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ old('message') }}</textarea>
                        @error('message')<p id="message-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-brand px-7 py-3 font-semibold text-brand-fg hover:bg-brand-dark">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i> Send message
                    </button>
                </form>
            </div>

            {{-- Map and social --}}
            @if ($hasSidebar)
                <div class="flex flex-col gap-8 lg:col-span-2">
                    @if ($mapEmbedUrl)
                        <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white">
                            <div class="flex items-center justify-between gap-4 px-6 py-5">
                                <h2 class="flex items-center gap-3 text-xl font-bold text-stone-950">
                                    <i class="fas fa-map-location-dot text-brand" aria-hidden="true"></i> Find us
                                </h2>
                                <a href="{{ $siteSettings->mapDirectionsUrl() }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 text-sm font-semibold text-brand-dark hover:underline">
                                    <i class="fas fa-diamond-turn-right" aria-hidden="true"></i> Get directions
                                </a>
                            </div>
                            <iframe src="{{ $mapEmbedUrl }}" title="Map showing the location of {{ $siteSettings->site_name }}"
                                class="block aspect-[4/3] w-full border-0 lg:aspect-auto lg:h-96" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                        </div>
                    @endif

                    @if ($socialLinks)
                        <div class="rounded-3xl bg-ink p-6 text-ink-fg sm:p-8">
                            <h2 class="flex items-center gap-3 text-xl font-bold">
                                <i class="fas fa-share-nodes" aria-hidden="true"></i> Follow us
                            </h2>
                            <ul class="mt-5 flex flex-wrap gap-3">
                                @foreach ($socialLinks as $network => $url)
                                    <li>
                                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-2 rounded-full border border-current/25 px-4 py-2 text-sm font-semibold hover:bg-white/10">
                                            <i class="{{ \App\Models\SiteSetting::socialIcon($network) }}" aria-hidden="true"></i> {{ $network }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
