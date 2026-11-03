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
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),
            Stat::make('Upcoming events', Event::query()->upcoming()->count())
                ->description('Published events yet to finish')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),
            Stat::make('Published pages', Page::query()->where('status', 'published')->count())
                ->description('Public pages')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('success'),
            Stat::make('Subscribers', Subscriber::query()->count())
                ->description('Newsletter subscribers')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('success'),
        ];
    }
}
