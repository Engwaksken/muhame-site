@extends('layouts.public')

@section('title', $heading.' · '.$siteSettings->site_name)
@section('description', $description ?: 'Browse the latest stories from '.$siteSettings->site_name.'.')
@if (isset($searchQuery))
    @section('robots', 'noindex,follow')
@endif

@section('content')
    <section class="mb-10 max-w-3xl">
        <p class="mb-3 text-sm font-semibold uppercase tracking-[0.18em] text-amber-700">The journal</p>
        <h1 class="text-4xl font-bold tracking-tight text-stone-950 sm:text-5xl">{{ $heading }}</h1>
        @if ($description)
            <p class="mt-4 text-lg leading-8 text-stone-600">{{ $description }}</p>
        @endif
    </section>

    @if ($posts->isEmpty())
        <div class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <h2 class="text-xl font-semibold">No stories published yet</h2>
            <p class="mt-2 text-stone-600">Please check back soon.</p>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                    @if ($post->featured_image)
                        <a href="{{ route('posts.show', $post->slug) }}" class="block aspect-[16/10] overflow-hidden bg-stone-100">
                            <img src="{{ asset('storage/'.$post->featured_image) }}" alt="" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                        </a>
                    @endif
                    <div class="flex flex-1 flex-col p-6">
                        <div class="mb-3 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-stone-500">
                            @if ($post->category)
                                <a href="{{ route('categories.show', $post->category->slug) }}" class="text-amber-700 hover:text-amber-900">{{ $post->category->name }}</a>
                                <span aria-hidden="true">·</span>
                            @endif
                            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('M j, Y') }}</time>
                        </div>
                        <h2 class="text-xl font-semibold leading-snug text-stone-950">
                            <a href="{{ route('posts.show', $post->slug) }}" class="hover:text-amber-800">{{ $post->title }}</a>
                        </h2>
                        <p class="mt-3 flex-1 leading-7 text-stone-600">{{ \Illuminate\Support\Str::limit(strip_tags($post->body), 170) }}</p>
                        <div class="mt-5 flex items-center justify-between gap-3 border-t border-stone-100 pt-4 text-sm text-stone-500">
                            <span>{{ $post->author?->name ?? 'Editorial team' }}</span>
                            <a href="{{ route('posts.show', $post->slug) }}" class="font-semibold text-amber-700 hover:text-amber-900">Read story <span aria-hidden="true">&rarr;</span></a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-10">{{ $posts->links() }}</div>
    @endif
@endsection
