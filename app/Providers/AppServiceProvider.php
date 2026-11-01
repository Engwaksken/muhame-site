<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Production hardening: generate https:// URLs behind proxies/load balancers
        // and block migrate:fresh / db:wipe style commands against the live database.
        URL::forceHttps($this->app->isProduction());
        DB::prohibitDestructiveCommands($this->app->isProduction());

        View::composer(['layouts.public', 'public.*'], function ($view): void {
            $view->with('siteSettings', SiteSetting::current());
        });

        View::composer('layouts.public', function ($view): void {
            $view->with([
                'navigationPages' => Page::query()->inNavigation()->get(['title', 'slug']),
                'navigationCategories' => Category::query()
                    ->whereHas('posts', fn (Builder $query) => $query->published())
                    ->orderBy('name')
                    ->limit(6)
                    ->get(),
            ]);
        });
    }
}
