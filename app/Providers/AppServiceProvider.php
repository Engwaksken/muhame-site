<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Event;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Subscriber;
use App\Models\Tag;
use App\Models\User;
use App\Observers\AuditObserver;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
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
        foreach ([Category::class, ContactMessage::class, Event::class, Page::class, Post::class, SiteSetting::class, Subscriber::class, Tag::class, User::class] as $model) {
            $model::observe(AuditObserver::class);
        }

        // Production hardening: generate https:// URLs behind proxies/load balancers
        // and block migrate:fresh / db:wipe style commands against the live database.
        URL::forceHttps($this->app->isProduction());
        DB::prohibitDestructiveCommands($this->app->isProduction());

        // Admin pop-up forms: a comfortable default width instead of near full-screen.
        // Resources with long forms (posts, events) set a wider size themselves.
        foreach ([CreateAction::class, EditAction::class, ViewAction::class] as $action) {
            $action::configureUsing(fn ($modalAction) => $modalAction->modalWidth(Width::TwoExtraLarge));
        }

        View::composer(['layouts.public', 'public.*'], function ($view): void {
            $view->with('siteSettings', SiteSetting::current());
        });

        // Menu data for the layout and the insights filters. once() keeps it to
        // one set of queries per request, however many views use it.
        View::composer(['layouts.public', 'public.*'], function ($view): void {
            $view->with(once(fn () => [
                'navigationCategories' => Category::query()
                    ->whereHas('posts', fn (Builder $query) => $query->published())
                    ->orderBy('name')
                    ->limit(8)
                    ->get(),
            ]));
        });
    }
}
