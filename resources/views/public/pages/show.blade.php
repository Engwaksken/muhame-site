@extends('layouts.public')

@php($renderedBlocks = \App\Support\PageBlockRenderer::prepare($blocks))
@php($startsWithHero = ($renderedBlocks[0]['type'] ?? null) === 'hero')
@php($heroImage = \App\Support\Media::url($page->hero_image))

@section('title', $page->is_homepage ? $siteSettings->site_name.($siteSettings->tagline ? ' · '.$siteSettings->tagline : '') : $seoTitle.' · '.$siteSettings->site_name)
@section('description', $description)
@section('canonical', $page->url())
@if ($page->noindex)
    @section('robots', 'noindex,follow')
@endif
@if ($heroImage)
    @section('og_image', $heroImage)
@endif

@section('content')
    @unless ($startsWithHero)
        <header class="bg-brand-soft">
            <div @class(['mx-auto grid min-h-64 max-w-7xl sm:min-h-80', 'lg:grid-cols-2' => $heroImage])>
                <div class="self-center px-5 py-10 sm:px-8 sm:py-12">
                    @if ($page->parent)
                        <nav aria-label="Breadcrumb" class="mb-2 text-sm font-semibold">
                            <a href="{{ $page->parent->url() }}" class="text-brand-dark">{{ $page->parent->title }}</a>
                        </nav>
                    @endif
                    <h1 class="text-3xl font-bold leading-tight tracking-tight text-stone-950 sm:text-4xl">{{ $page->title }}</h1>
                    @if ($page->excerpt)
                        <p class="mt-2 max-w-2xl text-base leading-6 text-stone-700 sm:text-lg">{{ $page->excerpt }}</p>
                    @endif
                </div>
                @if ($heroImage)
                    <div class="relative min-h-64 overflow-hidden sm:min-h-80">
                        <img src="{{ $heroImage }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                    </div>
                @endif
            </div>
        </header>
    @endunless

    @forelse ($renderedBlocks as $block)
        @include($block['view'], ['data' => $block['data'], 'blockIndex' => $loop->index])
    @empty
        @if ($safeBody !== '')
            <div class="mx-auto max-w-7xl px-5 py-6 sm:px-8 sm:py-8">
                <div class="rich-content overflow-x-auto text-lg leading-8 text-stone-700">{!! $safeBody !!}</div>
            </div>
        @endif
    @endforelse
@endsection
