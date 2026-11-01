{{-- Newsletter signup. Expects $formId (unique per page) and optional $dark (light text on dark background). --}}
@php($dark = $dark ?? false)
@php($errorBag = $errors->getBag('newsletter'))

@if (session('newsletter_status'))
    <p role="status" @class([
        'mt-5 rounded-xl px-4 py-3 text-sm font-semibold',
        'bg-white/10' => $dark,
        'bg-emerald-50 text-emerald-800' => ! $dark,
    ])>{{ session('newsletter_status') }}</p>
@else
    <form action="{{ route('newsletter.store') }}" method="POST" class="mt-5" novalidate>
        @csrf
        <div class="absolute -left-[9999px]" aria-hidden="true">
            <label for="{{ $formId }}-website">Leave this field empty</label>
            <input id="{{ $formId }}-website" type="text" name="website" tabindex="-1" autocomplete="off">
        </div>
        <label for="{{ $formId }}-email" class="sr-only">Email address</label>
        <div class="flex flex-col gap-3 sm:flex-row">
            <input id="{{ $formId }}-email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" placeholder="Your email address"
                @if ($errorBag->has('email')) aria-invalid="true" aria-describedby="{{ $formId }}-email-error" @endif
                @class([
                    'min-w-0 flex-1 rounded-full border px-5 py-3 focus:outline-none',
                    'border-white/30 bg-white/10 text-current placeholder:text-current placeholder:opacity-60 focus:border-white' => $dark,
                    'border-stone-300 bg-white text-stone-900 focus:border-brand' => ! $dark,
                ])>
            <button type="submit" class="shrink-0 rounded-full bg-brand px-6 py-3 font-semibold text-brand-fg hover:bg-brand-dark">Subscribe</button>
        </div>
        @if ($errorBag->has('email'))
            <p id="{{ $formId }}-email-error" @class(['mt-2 text-sm', 'font-semibold' => $dark, 'text-red-700' => ! $dark])>{{ $errorBag->first('email') }}</p>
        @endif
        <p class="mt-3 text-xs opacity-70">We'll only use your email to send you updates. You can ask us to remove you at any time.</p>
    </form>
@endif
