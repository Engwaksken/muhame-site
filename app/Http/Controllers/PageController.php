<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\SafeHtml;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /** The site root: the page marked as homepage, or the latest insights if none is set. */
    public function home(PublicPostController $posts): View
    {
        $page = Page::homepage();

        return $page ? $this->render($page) : $posts->index();
    }

    public function show(string $slug): View|RedirectResponse
    {
        $page = Page::query()->published()->where('slug', $slug)->firstOrFail();

        // The homepage has one address: the site root.
        if ($page->is_homepage) {
            return redirect()->route('home', status: 301);
        }

        return $this->render($page);
    }

    private function render(Page $page): View
    {
        $safeBody = filled($page->body) ? SafeHtml::clean($page->body) : '';

        return view('public.pages.show', [
            'page' => $page,
            'blocks' => is_array($page->blocks) ? $page->blocks : [],
            'safeBody' => $safeBody,
            'seoTitle' => strip_tags($page->seo_title ?: $page->title),
            'description' => $page->seo_description ?: $page->excerpt ?: Str::limit(strip_tags($safeBody), 155),
        ]);
    }
}
