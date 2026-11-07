@php($posts = $data['posts'])
@if ($posts->isNotEmpty())
    <section class="mx-auto max-w-7xl px-5 py-6 sm:px-8 sm:py-8">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <h2 class="text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">{{ filled($data['heading'] ?? null) ? $data['heading'] : 'Latest insights' }}</h2>
            <a href="{{ route('posts.index') }}" class="font-semibold text-brand-dark">View all posts <span aria-hidden="true">&rarr;</span></a>
        </div>
        @if ((int) ($data['count'] ?? 4) === 4 && $posts->count() > 1)
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-3">@include('partials.post-card', ['post' => $posts->first(), 'featured' => true])</div>
                @foreach ($posts->slice(1) as $post)
                    @include('partials.post-card', ['post' => $post])
                @endforeach
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @endforeach
            </div>
        @endif
    </section>
@endif
