<?php

use App\Http\Controllers\PublicPostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPostController::class, 'index'])->name('home');
Route::get('/posts', [PublicPostController::class, 'index'])->name('posts.index');
Route::get('/search', [PublicPostController::class, 'search'])->name('search');
Route::get('/sitemap.xml', [PublicPostController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PublicPostController::class, 'robots'])->name('robots');
Route::get('/posts/{slug}', [PublicPostController::class, 'show'])->name('posts.show');
Route::get('/categories/{slug}', [PublicPostController::class, 'category'])->name('categories.show');
Route::get('/tags/{slug}', [PublicPostController::class, 'tag'])->name('tags.show');
