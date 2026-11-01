<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicPostController;
use App\Models\Page;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPostController::class, 'index'])->name('home');
Route::get('/posts', [PublicPostController::class, 'index'])->name('posts.index');
Route::get('/search', [PublicPostController::class, 'search'])->name('search');
Route::get('/sitemap.xml', [PublicPostController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PublicPostController::class, 'robots'])->name('robots');
Route::get('/posts/{slug}', [PublicPostController::class, 'show'])->name('posts.show');
Route::get('/categories/{slug}', [PublicPostController::class, 'category'])->name('categories.show');
Route::get('/tags/{slug}', [PublicPostController::class, 'tag'])->name('tags.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,10')->name('contact.store');

// Must stay last: admin-managed pages live at the top level, e.g. /about.
Route::get('/{slug}', [PageController::class, 'show'])->where('slug', Page::routePattern())->name('pages.show');
