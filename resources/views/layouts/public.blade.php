@php
    $brand = $siteSettings->primaryColor();
    $ink = $siteSettings->accentColor();
    $logoUrl = \App\Support\Media::url($siteSettings->logo_path);
    $menuLinks = $siteSettings->publicNavigationLinks();
    $navLink = 'inline-flex items-center gap-1 rounded-md px-3 py-2 text-[0.95rem] font-semibold text-stone-800 hover:text-brand-dark focus-visible:outline-2 focus-visible:outline-brand';
    $pageHeaderLinkUrl = isset($page) && $page instanceof \App\Models\Page
        ? \App\Support\SafeUrl::clean($page->header_link_url, allowRelative: false)
        : null;
    $pageHeaderLinkLabel = $pageHeaderLinkUrl && isset($page) ? trim((string) $page->header_link_label) : '';
    if ($pageHeaderLinkLabel === '') $pageHeaderLinkUrl = null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="--brand: {{ $brand }}; --brand-fg: {{ $siteSettings->primaryTextColor() }}; --ink: {{ $ink }}; --ink-fg: #ffffff; --font-body: {{ \App\Models\SiteSetting::fontStack($siteSettings->bodyFontKey()) }}; --font-heading: {{ \App\Models\SiteSetting::fontStack($siteSettings->headingFontKey()) }}; font-size: {{ $siteSettings->baseFontSize() }};">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ trim($__env->yieldContent('description', $siteSettings->tagline ?? '')) }}">
    <meta name="robots" content="{{ trim($__env->yieldContent('robots', 'index,follow')) }}">
    <link rel="canonical" href="{{ trim($__env->yieldContent('canonical', url()->current())) }}">
    <meta property="og:site_name" content="{{ $siteSettings->site_name }}">
    <meta property="og:type" content="{{ trim($__env->yieldContent('og_type', 'website')) }}">
    <meta property="og:title" content="{{ trim($__env->yieldContent('title', $siteSettings->site_name)) }}">
    <meta property="og:description" content="{{ trim($__env->yieldContent('description', $siteSettings->tagline ?? '')) }}">
    <meta property="og:url" content="{{ trim($__env->yieldContent('canonical', url()->current())) }}">
    @hasSection('og_image')<meta property="og:image" content="{{ trim($__env->yieldContent('og_image')) }}">@endif
    <meta name="twitter:card" content="{{ trim($__env->yieldContent('og_image')) !== '' ? 'summary_large_image' : 'summary' }}">
    <meta name="theme-color" content="{{ $brand }}">
    <title>{{ trim($__env->yieldContent('title', $siteSettings->site_name)) }}</title>
    <link rel="icon" href="{{ $siteSettings->faviconUrl() }}">
    <link rel="apple-touch-icon" href="{{ $siteSettings->faviconUrl() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="{{ $siteSettings->fontStylesheetUrl() }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-white text-stone-900 antialiased">
    <a href="#main" class="sr-only z-50 rounded-md bg-brand px-4 py-2 font-semibold text-brand-fg focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to content</a>

    {{-- Header --}}
    <header class="sticky top-0 z-40 border-b border-stone-200 bg-white/95 backdrop-blur">
        @if ($pageHeaderLinkUrl)
            <div class="border-b border-stone-200 bg-white">
                <div class="mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-4 px-5 py-2 sm:px-8">
                    <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3 text-xl font-bold tracking-tight text-stone-950">
                        @if ($logoUrl && $siteSettings->headerShowsLogo())
                            <img src="{{ $logoUrl }}" alt="" class="max-h-11 max-w-44 object-contain">
                        @endif
                        <span @class(['sr-only' => ! $siteSettings->headerShowsName(), 'truncate'])>{{ $siteSettings->site_name }}</span>
                    </a>
                    <a href="{{ $pageHeaderLinkUrl }}" class="inline-flex shrink-0 items-center justify-center rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-brand-fg hover:bg-brand-dark"
                        @if (\App\Support\SafeUrl::isExternal($pageHeaderLinkUrl)) target="_blank" rel="noopener noreferrer" @endif>
                        {{ $pageHeaderLinkLabel }}
                    </a>
                </div>
            </div>
        @endif
        <div class="mx-auto flex h-18 max-w-7xl items-center gap-6 px-5 sm:px-8">
            @unless ($pageHeaderLinkUrl)
                <a href="{{ route('home') }}" class="flex min-w-0 shrink-0 items-center gap-3 text-xl font-bold tracking-tight text-stone-950">
                    @if ($logoUrl && $siteSettings->headerShowsLogo())
                        <img src="{{ $logoUrl }}" alt="" class="max-h-11 max-w-44 object-contain">
                    @endif
                    <span @class(['sr-only' => ! $siteSettings->headerShowsName(), 'truncate'])>{{ $siteSettings->site_name }}</span>
                </a>
            @endunless

            {{-- Desktop navigation --}}
            <nav aria-label="Main" class="ml-auto hidden lg:block">
                <ul class="flex items-center gap-1">
                    @foreach ($navigationPages as $navPage)
                        @if ($navPage->children->isNotEmpty())
                            <li class="group relative" data-dropdown>
                                <button type="button" class="{{ $navLink }}" aria-expanded="false" aria-haspopup="true"
                                    data-toggle="menu-{{ $navPage->id }}" data-group="dropdown">
                                    {{ $navPage->title }}
                                    <svg class="size-4 transition group-hover:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                                </button>
                                <div id="menu-{{ $navPage->id }}" class="absolute left-1/2 top-full hidden w-[34rem] -translate-x-1/2 pt-3 group-hover:block group-focus-within:block">
                                    <div class="grid grid-cols-5 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-xl">
                                        <div class="col-span-2 bg-brand-soft p-6">
                                            <a href="{{ $navPage->url() }}" class="text-lg font-bold text-stone-950 hover:text-brand-dark">{{ $navPage->title }}</a>
                                            @if ($navPage->excerpt)
                                                <p class="mt-2 text-sm leading-6 text-stone-600">{{ \Illuminate\Support\Str::limit($navPage->excerpt, 110) }}</p>
                                            @endif
                                            <a href="{{ $navPage->url() }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-dark">Overview <span aria-hidden="true">&rarr;</span></a>
                                        </div>
                                        <ul class="col-span-3 space-y-1 p-4">
                                            @foreach ($navPage->children as $child)
                                                <li><a href="{{ $child->url() }}" class="block rounded-lg px-3 py-2 text-[0.95rem] font-medium text-stone-700 hover:bg-stone-100 hover:text-stone-950">{{ $child->title }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </li>
                        @else
                            <li><a href="{{ $navPage->url() }}" class="{{ $navLink }}" @if (url()->current() === $navPage->url()) aria-current="page" @endif>{{ $navPage->title }}</a></li>
                        @endif
                    @endforeach
                    @foreach ($menuLinks as $label => $url)
                        <li><a href="{{ $url }}" class="{{ $navLink }}" @if (\App\Support\SafeUrl::isExternal($url)) target="_blank" rel="noopener noreferrer" @endif>{{ $label }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <div class="ml-auto flex items-center gap-2 lg:ml-2">
                <button type="button" class="rounded-full p-2.5 text-stone-700 hover:bg-stone-100 hover:text-stone-950" aria-expanded="false" aria-controls="site-search-panel" data-toggle="site-search-panel">
                    <span class="sr-only">Search</span>
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/></svg>
                </button>
                <a href="{{ route('contact') }}" class="hidden rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-brand-fg hover:bg-brand-dark sm:inline-flex">Contact us</a>
                <button type="button" class="rounded-full p-2.5 text-stone-800 hover:bg-stone-100 lg:hidden" aria-expanded="false" aria-controls="mobile-menu" data-toggle="mobile-menu">
                    <span class="sr-only">Menu</span>
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
            </div>
        </div>

        {{-- Search panel --}}
        <div id="site-search-panel" class="hidden border-t border-stone-200 bg-stone-50" data-focus="input">
            <form action="{{ route('search') }}" method="GET" role="search" class="mx-auto flex max-w-3xl gap-3 px-5 py-5 sm:px-8">
                <label for="site-search" class="sr-only">Search insights</label>
                <input id="site-search" type="search" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Search insights…" required
                    class="min-w-0 flex-1 rounded-full border border-stone-300 bg-white px-5 py-3 focus:border-brand focus:outline-none">
                <button class="rounded-full bg-ink px-6 py-3 font-semibold text-white hover:opacity-90">Search</button>
            </form>
        </div>

        {{-- Mobile menu --}}
        <nav id="mobile-menu" aria-label="Main" class="hidden max-h-[calc(100vh-4.5rem)] overflow-y-auto border-t border-stone-200 bg-white lg:hidden">
            <ul class="space-y-1 px-5 py-4">
                @foreach ($navigationPages as $navPage)
                    <li>
                        <a href="{{ $navPage->url() }}" class="block rounded-lg px-3 py-3 text-lg font-semibold text-stone-900 hover:bg-stone-100">{{ $navPage->title }}</a>
                        @if ($navPage->children->isNotEmpty())
                            <ul class="mb-2 ml-3 border-l-2 border-brand-soft pl-3">
                                @foreach ($navPage->children as $child)
                                    <li><a href="{{ $child->url() }}" class="block rounded-lg px-3 py-2 text-stone-600 hover:bg-stone-100 hover:text-stone-900">{{ $child->title }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
                @foreach ($menuLinks as $label => $url)
                    <li><a href="{{ $url }}" class="block rounded-lg px-3 py-3 text-lg font-semibold text-stone-900 hover:bg-stone-100">{{ $label }}</a></li>
                @endforeach
                <li class="pt-3"><a href="{{ route('contact') }}" class="block rounded-full bg-brand px-5 py-3 text-center font-semibold text-brand-fg hover:bg-brand-dark">Contact us</a></li>
            </ul>
        </nav>
    </header>

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-ink text-white">
        <div class="mx-auto grid max-w-7xl gap-12 px-5 py-16 sm:px-8 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <a href="{{ route('home') }}" class="text-2xl font-bold tracking-tight">{{ $siteSettings->site_name }}</a>
                @if ($siteSettings->tagline)
                    <p class="mt-3 text-lg opacity-90">{{ $siteSettings->tagline }}</p>
                @endif
                @if ($siteSettings->footer_text)
                    <p class="mt-4 whitespace-pre-line text-sm leading-6 opacity-75">{{ $siteSettings->footer_text }}</p>
                @endif
                @if ($siteSettings->contact_email)
                    <a href="mailto:{{ $siteSettings->contact_email }}" class="mt-4 inline-block text-sm font-semibold opacity-90 hover:opacity-100">{{ $siteSettings->contact_email }}</a>
                @endif
            </div>

            <div class="grid gap-10 sm:grid-cols-2 lg:col-span-4">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.16em] opacity-70">Explore</h2>
                    <ul class="mt-4 space-y-2.5">
                        @foreach ($siteSettings->publicExplorePages() as $explorePage)
                            <li><a href="{{ $explorePage->url() }}" class="opacity-90 hover:opacity-100">{{ $explorePage->title }}</a></li>
                        @endforeach
                        @foreach ($siteSettings->publicExploreLinks() as $label => $url)
                            <li><a href="{{ $url }}" class="opacity-90 hover:opacity-100">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>
                @php($footerPages = $siteSettings->publicFooterPages())
                @php($footerLinks = $siteSettings->publicFooterLinks())
                @if ($footerPages->isNotEmpty() || $footerLinks)
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-[0.16em] opacity-70">Pages & links</h2>
                        <ul class="mt-4 space-y-2.5">
                            @foreach ($footerPages as $footerPage)
                                <li><a href="{{ $footerPage->url() }}" class="opacity-90 hover:opacity-100">{{ $footerPage->title }}</a></li>
                            @endforeach
                            @foreach ($footerLinks as $label => $url)
                                <li><a href="{{ $url }}" class="opacity-90 hover:opacity-100" @if (\App\Support\SafeUrl::isExternal($url)) target="_blank" rel="noopener noreferrer" @endif>{{ $label }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div id="newsletter" class="scroll-mt-28 lg:col-span-4">
                <h2 class="text-xl font-bold">{{ $siteSettings->newsletterHeading() }}</h2>
                <p class="mt-2 text-sm leading-6 opacity-80">{{ $siteSettings->newsletterText() }}</p>
                @include('partials.newsletter-form', ['formId' => 'footer', 'dark' => true])
            </div>
        </div>

        <div class="border-t border-current/15">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-5 py-6 text-sm sm:flex-row sm:items-center sm:justify-between sm:px-8">
                <span class="opacity-75">&copy; {{ now()->year }} {{ $siteSettings->site_name }}. All rights reserved.</span>
                @if ($socialLinks = $siteSettings->publicSocialLinks())
                    <ul class="flex flex-wrap gap-x-5 gap-y-2">
                        @foreach ($socialLinks as $network => $url)
                            <li><a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="font-medium opacity-85 hover:opacity-100">{{ $network }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </footer>
</body>
</html>
