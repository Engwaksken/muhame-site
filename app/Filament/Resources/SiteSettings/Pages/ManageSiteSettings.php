<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

/** Site settings is a single record, so its admin page is just the edit form. */
class ManageSiteSettings extends EditRecord
{
    protected static string $resource = SiteSettingResource::class;

    protected static ?string $title = 'Site settings';

    public function mount(int|string|null $record = null): void
    {
        parent::mount(SiteSetting::current()->getKey());
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewSite')->label('View site')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->color('gray')
                ->url(route('home'), shouldOpenInNewTab: true),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }
}
