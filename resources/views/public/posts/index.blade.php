@extends('layouts.public')

@section('title', $heading.' · '.$siteSettings->site_name)
@section('description', $description ?: 'Browse the latest insights from '.$siteSettings->site_name.'.')
@if (isset($searchQuery))
    @section('robots', 'noindex,follow')
@endif

@section('content')
    <header class="bg-brand-soft">
        <div class="mx-auto max-w-7xl px-5 py-6 sm:px-8 sm:py-8">
            <h1 class="text-4xl font-bold tracking-tight text-stone-950 sm:text-5xl">{{ $heading }}</h1>
            @if ($description)
                <p class="mt-3 max-w-2xl text-lg leading-8 text-stone-700">{{ $description }}</p>
            @endif

            @if ($navigationCategories->isNotEmpty() && ! isset($searchQuery))
                <nav aria-label="Filter by category" class="mt-6">
                    <ul class="flex flex-wrap gap-2">
                        <li>
                            <a href="{{ route('posts.index') }}" @class([
                                'inline-block rounded-full border px-4 py-2 text-sm font-semibold transition',
                                'border-brand bg-brand text-brand-fg' => ! isset($activeCategory),
                                'border-stone-300 bg-white text-stone-700 hover:border-brand' => isset($activeCategory),
                            ]) @if (! isset($activeCategory)) aria-current="page" @endif>All</a>
                        </li>
                        @foreach ($navigationCategories as $filterCategory)
                            @php($isActive = ($activeCategory ?? null) === $filterCategory->slug)
                            <li>
                                <a href="{{ route('categories.show', $filterCategory->slug) }}" @class([
                                    'inline-block rounded-full border px-4 py-2 text-sm font-semibold transition',
                                    'border-brand bg-brand text-brand-fg' => $isActive,
                                    'border-stone-300 bg-white text-stone-700 hover:border-brand' => ! $isActive,
                                ]) @if ($isActive) aria-current="page" @endif>{{ $filterCategory->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif
        </div>
    </header>

    <div class="mx-auto max-w-7xl px-5 py-6 sm:px-8 sm:py-8">
        @if ($posts->isEmpty())
            <div class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
                <h2 class="text-xl font-semibold">{{ isset($searchQuery) ? 'No results found' : 'Nothing published yet' }}</h2>
                <p class="mt-2 text-stone-600">{{ isset($searchQuery) ? 'Try a different search term.' : 'Please check back soon.' }}</p>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @endforeach
            </div>

            <div class="mt-12">{{ $posts->links() }}</div>
        @endif
    </div>
@endsection
