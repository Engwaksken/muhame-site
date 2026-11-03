<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Page;
use App\Models\Post;
use App\Models\Subscriber;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CmsStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        return [
            Stat::make('Published insights', Post::query()->published()->count())
                ->description('Live articles on the site')
                ->color('success'),
            Stat::make('Upcoming events', Event::query()->upcoming()->count())
                ->description('Published events yet to finish')
                ->color('success'),
            Stat::make('Published pages', Page::query()->where('status', 'published')->count())
                ->description('Public pages')
                ->color('success'),
            Stat::make('Subscribers', Subscriber::query()->count())
                ->description('Newsletter subscribers')
                ->color('success'),
        ];
    }
}
