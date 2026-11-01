<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Tag;
use App\Support\SafeHtml;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PublicPostController extends Controller
{
    public function index(): View
    {
        $settings = SiteSetting::current();
        $isHomepage = request()->routeIs('home');

        return view('public.posts.index', [
            'heading' => $isHomepage ? $settings->homepage_title : 'Insights',
            'description' => $isHomepage
                ? $settings->homepage_description
                : 'Research, news and stories from our team.',
            'posts' => $this->publishedPosts()->paginate(12),
        ]);
    }

    public function search(Request $request): View
    {
        $validated = $request->validate(['q' => ['required', 'string', 'max:100']]);
        $term = trim($validated['q']);

        $posts = $this->publishedPosts()
            ->where(fn (Builder $query) => $query
                ->where('title', 'like', "%{$term}%")
                ->orWhere('body', 'like', "%{$term}%"))
            ->paginate(12)
            ->withQueryString();

        return view('public.posts.index', [
            'heading' => 'Search results',
            'description' => "Insights matching “{$term}”.",
            'posts' => $posts,
            'searchQuery' => $term,
        ]);
    }

    public function show(string $slug): View
    {
        $post = $this->publishedPosts()->where('slug', $slug)->firstOrFail();
        $safeBody = SafeHtml::clean($post->body);

        return view('public.posts.show', [
            'post' => $post,
            'seoTitle' => strip_tags($post->seo_title ?: $post->title),
            'safeBody' => $safeBody,
            'description' => Str::limit(strip_tags($safeBody), 155),
        ]);
    }

    public function category(string $slug): View
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();

        return view('public.posts.index', [
            'heading' => $category->name,
            'description' => $category->description,
            'activeCategory' => $category->slug,
            'posts' => $this->publishedPosts()
                ->where('category_id', $category->id)
                ->paginate(12),
        ]);
    }

    public function tag(string $slug): View
    {
        $tag = Tag::query()->where('slug', $slug)->firstOrFail();

        return view('public.posts.index', [
            'heading' => "Insights tagged “{$tag->name}”",
            'description' => null,
            'posts' => $this->publishedPosts()
                ->whereHas('tags', fn (Builder $query) => $query->where('tags.id', $tag->id))
                ->paginate(12),
        ]);
    }

    public function sitemap(): Response
    {
        $urls = [
            [route('home'), now()->toAtomString()],
            [route('posts.index'), now()->toAtomString()],
            [route('events.index'), now()->toAtomString()],
            [route('contact'), now()->toAtomString()],
        ];

        Page::query()->published()->where('noindex', false)->where('is_homepage', false)
            ->orderBy('slug')->get(['slug', 'updated_at'])
            ->each(function (Page $page) use (&$urls): void {
                $urls[] = [route('pages.show', $page->slug), $page->updated_at->toAtomString()];
            });

        Event::query()->published()->orderBy('slug')->get(['slug', 'updated_at'])
            ->each(function (Event $event) use (&$urls): void {
                $urls[] = [route('events.show', $event->slug), $event->updated_at->toAtomString()];
            });

        Post::query()->published()->where('noindex', false)->orderBy('slug')->get(['slug', 'updated_at'])
            ->each(function (Post $post) use (&$urls): void {
                $urls[] = [route('posts.show', $post->slug), $post->updated_at->toAtomString()];
            });

        Category::query()
            ->whereHas('posts', fn (Builder $query) => $query->published()->where('noindex', false))
            ->orderBy('slug')->get(['slug', 'updated_at'])
            ->each(function (Category $category) use (&$urls): void {
                $urls[] = [route('categories.show', $category->slug), $category->updated_at->toAtomString()];
            });

        Tag::query()
            ->whereHas('posts', fn (Builder $query) => $query->published()->where('noindex', false))
            ->orderBy('slug')->get(['slug', 'updated_at'])
            ->each(function (Tag $tag) use (&$urls): void {
                $urls[] = [route('tags.show', $tag->slug), $tag->updated_at->toAtomString()];
            });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as [$url, $lastModified]) {
            $xml .= '<url><loc>'.htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc><lastmod>'
                .htmlspecialchars($lastModified, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</lastmod></url>';
        }

        $xml .= '</urlset>';

        return response($xml)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $contents = "User-agent: *\nAllow: /\nDisallow: /admin\nSitemap: ".route('sitemap')."\n";

        return response($contents)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    private function publishedPosts(): Builder
    {
        return Post::query()
            ->published()
            ->with(['author', 'category', 'tags'])
            ->latest('published_at');
    }
}
