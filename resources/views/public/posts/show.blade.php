@extends('layouts.public')

@section('title', $seoTitle.' · '.$siteSettings->site_name)
@section('description', $post->seo_description ?: $description)
@section('canonical', route('posts.show', $post->slug))
@section('og_type', 'article')
@if ($post->noindex)
    @section('robots', 'noindex,follow')
@endif
@if ($post->featured_image)
    @section('og_image', asset('storage/'.$post->featured_image))
@endif

@section('content')
    <article class="mx-auto max-w-3xl">
        <a href="{{ route('posts.index') }}" class="text-sm font-semibold text-amber-700 hover:text-amber-900">&larr; All stories</a>
        <header class="mt-8">
            @if ($post->category)
                <a href="{{ route('categories.show', $post->category->slug) }}" class="text-sm font-semibold uppercase tracking-[0.16em] text-amber-700">{{ $post->category->name }}</a>
            @endif
            <h1 class="mt-3 text-4xl font-bold leading-tight tracking-tight text-stone-950 sm:text-5xl">{{ $post->title }}</h1>
            <div class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-stone-500">
                <span>By {{ $post->author?->name ?? 'Editorial team' }}</span>
                <span aria-hidden="true">·</span>
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time>
            </div>
        </header>

        @if ($post->featured_image)
            <img src="{{ asset('storage/'.$post->featured_image) }}" alt="" class="mt-8 max-h-[32rem] w-full rounded-2xl object-cover">
        @endif

        <div class="rich-content mt-9 max-w-none leading-8">
            {!! $safeBody !!}
        </div>

        @if ($post->tags->isNotEmpty())
            <footer class="mt-10 flex flex-wrap items-center gap-2 border-t border-stone-200 pt-6">
                <span class="mr-1 text-sm font-medium text-stone-500">Tagged:</span>
                @foreach ($post->tags as $tag)
                    <a href="{{ route('tags.show', $tag->slug) }}" class="rounded-full bg-stone-100 px-3 py-1 text-sm text-stone-700 hover:bg-amber-100 hover:text-amber-900">{{ $tag->name }}</a>
                @endforeach
            </footer>
        @endif
    </article>
@endsection
