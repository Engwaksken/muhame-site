@extends('layouts.public')

@section('title', 'Contact · '.$siteSettings->site_name)
@section('description', 'Get in touch with '.$siteSettings->site_name.'.')
@section('canonical', route('contact'))

@section('content')
    <section class="mx-auto max-w-2xl">
        <p class="mb-3 text-sm font-semibold uppercase tracking-[0.18em] text-amber-700">Get in touch</p>
        <h1 class="text-4xl font-bold tracking-tight text-stone-950 sm:text-5xl">Contact us</h1>
        <p class="mt-4 text-lg leading-8 text-stone-600">
            Send us a message and we'll get back to you as soon as we can.
            @if ($siteSettings->contact_email)
                You can also email <a href="mailto:{{ $siteSettings->contact_email }}" class="font-medium text-amber-700 underline underline-offset-4">{{ $siteSettings->contact_email }}</a>.
            @endif
        </p>

        @if (session('status'))
            <div role="status" class="mt-8 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('contact.store') }}" method="POST" class="mt-10 space-y-6" novalidate>
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
                        class="mt-2 w-full rounded-xl border border-stone-300 bg-white px-4 py-3 focus:border-amber-600 focus:outline-none @error('name') border-red-500 @enderror"
                        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                    @error('name')<p id="name-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold text-stone-800">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email"
                        class="mt-2 w-full rounded-xl border border-stone-300 bg-white px-4 py-3 focus:border-amber-600 focus:outline-none @error('email') border-red-500 @enderror"
                        @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email')<p id="email-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="subject" class="block text-sm font-semibold text-stone-800">Subject <span class="font-normal text-stone-500">(optional)</span></label>
                <input id="subject" type="text" name="subject" value="{{ old('subject') }}" maxlength="150"
                    class="mt-2 w-full rounded-xl border border-stone-300 bg-white px-4 py-3 focus:border-amber-600 focus:outline-none @error('subject') border-red-500 @enderror">
                @error('subject')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="message" class="block text-sm font-semibold text-stone-800">Message</label>
                <textarea id="message" name="message" rows="7" required minlength="10" maxlength="5000"
                    class="mt-2 w-full rounded-xl border border-stone-300 bg-white px-4 py-3 focus:border-amber-600 focus:outline-none @error('message') border-red-500 @enderror"
                    @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ old('message') }}</textarea>
                @error('message')<p id="message-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="rounded-full bg-stone-900 px-6 py-3 font-semibold text-white hover:bg-amber-800">Send message</button>
        </form>
    </section>
@endsection
