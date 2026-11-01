{{-- Admin-entered button. Renders nothing unless the URL is safe and a label is set. --}}
@php($buttonUrl = \App\Support\SafeUrl::clean($url ?? null))
@if ($buttonUrl && filled($label ?? null))
    <a href="{{ $buttonUrl }}" @if (\App\Support\SafeUrl::isExternal($buttonUrl)) target="_blank" rel="noopener noreferrer" @endif
        @class([
            'inline-flex items-center gap-2 rounded-full px-6 py-3 font-semibold transition',
            'bg-brand text-brand-fg hover:bg-brand-dark' => ($variant ?? 'primary') === 'primary',
            'bg-white text-stone-950 hover:bg-stone-100' => ($variant ?? 'primary') === 'light',
            'border-2 border-current hover:bg-white/10' => ($variant ?? 'primary') === 'outline',
        ])>{{ $label }} <span aria-hidden="true">&rarr;</span></a>
@endif
