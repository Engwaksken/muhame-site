<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\SafeHtml;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = Page::query()->published()->where('slug', $slug)->firstOrFail();
        $safeBody = SafeHtml::clean($page->body);

        return view('public.pages.show', [
            'page' => $page,
            'safeBody' => $safeBody,
            'seoTitle' => strip_tags($page->seo_title ?: $page->title),
            'description' => $page->seo_description ?: Str::limit(strip_tags($safeBody), 155),
        ]);
    }
}
