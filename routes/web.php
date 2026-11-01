<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicPostController;
use App\Models\Page;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/insights', [PublicPostController::class, 'index'])->name('posts.index');
Route::get('/insights/{slug}', [PublicPostController::class, 'show'])->name('posts.show');
Route::get('/categories/{slug}', [PublicPostController::class, 'category'])->name('categories.show');
Route::get('/tags/{slug}', [PublicPostController::class, 'tag'])->name('tags.show');
Route::get('/search', [PublicPostController::class, 'search'])->name('search');

// Old addresses from before posts became "Insights".
Route::permanentRedirect('/posts', '/insights');
Route::get('/posts/{slug}', fn (string $slug) => redirect()->route('posts.show', $slug, 301));

Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,10')->name('contact.store');
Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:5,10')->name('newsletter.store');

Route::get('/sitemap.xml', [PublicPostController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PublicPostController::class, 'robots'])->name('robots');

// Must stay last: admin-managed pages live at the top level, e.g. /about.
Route::get('/{slug}', [PageController::class, 'show'])->where('slug', Page::routePattern())->name('pages.show');
