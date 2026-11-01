<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ trim($__env->yieldContent('description', $siteSettings->tagline ?? 'Stories and updates')) }}">
    <meta name="robots" content="{{ trim($__env->yieldContent('robots', 'index,follow')) }}">
    <link rel="canonical" href="{{ trim($__env->yieldContent('canonical', url()->current())) }}">
    <meta property="og:type" content="{{ trim($__env->yieldContent('og_type', 'website')) }}">
    <meta property="og:title" content="{{ trim($__env->yieldContent('title', config('app.name'))) }}">
    <meta property="og:description" content="{{ trim($__env->yieldContent('description', $siteSettings->tagline ?? 'Stories and updates')) }}">
    <meta property="og:url" content="{{ trim($__env->yieldContent('canonical', url()->current())) }}">
    @hasSection('og_image')<meta property="og:image" content="{{ trim($__env->yieldContent('og_image')) }}">@endif
    <meta name="twitter:card" content="{{ trim($__env->yieldContent('og_image')) !== '' ? 'summary_large_image' : 'summary' }}">
    <title>{{ trim($__env->yieldContent('title', $siteSettings->site_name)) }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-5 px-5 py-5 sm:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-xl font-bold tracking-tight text-stone-950">
                @if ($siteSettings->logo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($siteSettings->logo_path) }}" alt="" class="max-h-10 max-w-40 object-contain">
                @endif
                <span>{{ $siteSettings->site_name }}</span>
            </a>
            <nav aria-label="Main navigation" class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm font-medium text-stone-600">
                <a href="{{ route('posts.index') }}" class="hover:text-amber-700">Stories</a>
                @foreach ($navigationPages as $navigationPage)
                    <a href="{{ route('pages.show', $navigationPage->slug) }}" class="hover:text-amber-700" @if (request()->is($navigationPage->slug)) aria-current="page" @endif>{{ $navigationPage->title }}</a>
                @endforeach
                @foreach ($siteSettings->publicNavigationLinks() as $label => $url)
                    <a href="{{ $url }}" class="hover:text-amber-700" @if (str_starts_with($url, 'http')) target="_blank" rel="noopener noreferrer" @endif>{{ $label }}</a>
                @endforeach
                @foreach ($navigationCategories as $navigationCategory)
                    <a href="{{ route('categories.show', $navigationCategory->slug) }}" class="hover:text-amber-700">
                        {{ $navigationCategory->name }}
                    </a>
                @endforeach
                <a href="{{ route('contact') }}" class="hover:text-amber-700" @if (request()->routeIs('contact')) aria-current="page" @endif>Contact</a>
                <a href="{{ url('/admin/login') }}" class="rounded-full border border-stone-300 px-4 py-2 text-stone-800 hover:border-amber-600 hover:text-amber-700">Admin</a>
            </nav>
            <form action="{{ route('search') }}" method="GET" role="search" class="flex w-full gap-2 sm:ml-auto sm:w-auto">
                <label for="site-search" class="sr-only">Search stories</label>
                <input id="site-search" type="search" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Search stories" required class="min-w-0 flex-1 rounded-full border border-stone-300 px-4 py-2 text-sm focus:border-amber-600 focus:outline-none sm:w-48">
                <button class="rounded-full bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-800">Search</button>
            </form>
        </div>
    </header>

    <main class="mx-auto min-h-[70vh] max-w-6xl px-5 py-12 sm:px-8 sm:py-16">
        @yield('content')
    </main>

    <footer class="border-t border-stone-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-2 px-5 py-8 text-sm text-stone-500 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <span>&copy; {{ now()->year }} {{ $siteSettings->site_name }}</span>
            <div class="flex flex-wrap items-center gap-4">
                @if ($siteSettings->contact_email)
                    <a href="mailto:{{ $siteSettings->contact_email }}" class="hover:text-amber-700">{{ $siteSettings->contact_email }}</a>
                @endif
                @foreach ($siteSettings->publicSocialLinks() as $network => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="hover:text-amber-700">{{ $network }}</a>
                @endforeach
                @if ($siteSettings->tagline)
                    <span>{{ $siteSettings->tagline }}</span>
                @endif
            </div>
        </div>
    </footer>
</body>
</html>
