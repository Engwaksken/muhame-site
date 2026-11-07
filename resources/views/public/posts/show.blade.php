@extends('layouts.public')

@php($featuredImage = \App\Support\Media::url($post->featured_image))

@section('title', $seoTitle.' · '.$siteSettings->site_name)
@section('description', $post->seo_description ?: $description)
@section('canonical', route('posts.show', $post->slug))
@section('og_type', 'article')
@if ($post->noindex)
    @section('robots', 'noindex,follow')
@endif
@if ($featuredImage)
    @section('og_image', $featuredImage)
@endif

@section('content')
    <header class="bg-brand-soft">
        <div class="mx-auto max-w-4xl px-5 py-6 sm:px-8 sm:py-8">
            <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-sm font-semibold">
                <a href="{{ route('posts.index') }}" class="text-brand-dark">Insights</a>
                @if ($post->category)
                    <span aria-hidden="true" class="text-stone-400">/</span>
                    <a href="{{ route('categories.show', $post->category->slug) }}" class="text-brand-dark">{{ $post->category->name }}</a>
                @endif
            </nav>
            <h1 class="mt-5 text-4xl font-bold leading-tight tracking-tight text-stone-950 sm:text-5xl">{{ $post->title }}</h1>
            <div class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-1 text-stone-600">
                <span>By {{ $post->author?->name ?? 'Editorial team' }}</span>
                <span aria-hidden="true">·</span>
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time>
            </div>
        </div>
    </header>

    <article class="mx-auto max-w-4xl px-5 py-6 sm:px-8 sm:py-8">
        @if ($featuredImage)
            <img src="{{ $featuredImage }}" alt="" class="mb-10 max-h-[32rem] w-full rounded-3xl object-cover">
        @endif

        <div class="rich-content mx-auto max-w-3xl text-lg leading-8 text-stone-800">
            {!! $safeBody !!}
        </div>

        @if ($post->tags->isNotEmpty())
            <footer class="mx-auto mt-12 flex max-w-3xl flex-wrap items-center gap-2 border-t border-stone-200 pt-6">
                <span class="mr-1 text-sm font-medium text-stone-500">Tagged:</span>
                @foreach ($post->tags as $tag)
                    <a href="{{ route('tags.show', $tag->slug) }}" class="rounded-full bg-stone-100 px-3 py-1 text-sm text-stone-700 hover:bg-brand-soft hover:text-brand-dark">{{ $tag->name }}</a>
                @endforeach
            </footer>
        @endif
    </article>
@endsection
