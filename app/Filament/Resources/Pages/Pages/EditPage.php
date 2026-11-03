<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Support\FaIcon;
use App\Models\Page;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')->label('View on site')->icon(FaIcon::make('fa-arrow-up-right-from-square'))->color('gray')
                ->url(fn (Page $record) => $record->url(), shouldOpenInNewTab: true)
                ->visible(fn (Page $record) => $record->status === 'published'),
            DeleteAction::make(),
        ];
    }
}
