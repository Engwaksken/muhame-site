{{-- Insight card. Expects $post; optional $featured for the large variant. --}}
@php($featured = $featured ?? false)
@php($image = \App\Support\Media::url($post->featured_image))
<article @class(['group relative flex h-full flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white transition hover:-translate-y-1 hover:shadow-lg', 'lg:flex-row' => $featured && $image])>
    @if ($image)
        <div @class(['overflow-hidden bg-stone-100', 'aspect-[16/10]' => ! $featured, 'aspect-[16/10] lg:aspect-auto lg:w-3/5' => $featured])>
            <img src="{{ $image }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
        </div>
    @endif
    <div @class(['flex flex-1 flex-col p-6', 'lg:p-10 lg:justify-center' => $featured])>
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-dark">{{ $post->category?->name ?? 'Insight' }}</p>
        <h3 @class(['mt-3 font-bold leading-snug tracking-tight text-stone-950', 'text-2xl lg:text-3xl' => $featured, 'text-xl' => ! $featured])>
            <a href="{{ route('posts.show', $post->slug) }}" class="after:absolute after:inset-0 group-hover:text-brand-dark">{{ $post->title }}</a>
        </h3>
        @if ($featured || ! $image)
            <p class="mt-3 leading-7 text-stone-600">{{ \Illuminate\Support\Str::limit($post->seo_description ?: strip_tags($post->body), $featured ? 220 : 140) }}</p>
        @endif
        <p class="mt-auto pt-5 text-sm text-stone-500"><time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time></p>
    </div>
</article>
