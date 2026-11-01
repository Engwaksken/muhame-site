@extends('layouts.public')

@section('title', $seoTitle.' · '.$siteSettings->site_name)
@section('description', $description)
@section('canonical', route('pages.show', $page->slug))
@if ($page->noindex)
    @section('robots', 'noindex,follow')
@endif

@section('content')
    <article class="mx-auto max-w-3xl">
        <h1 class="text-4xl font-bold leading-tight tracking-tight text-stone-950 sm:text-5xl">{{ $page->title }}</h1>

        <div class="rich-content mt-9 max-w-none leading-8">
            {!! $safeBody !!}
        </div>
    </article>
@endsection
