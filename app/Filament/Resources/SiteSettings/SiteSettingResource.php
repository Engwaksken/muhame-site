<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Resources\SiteSettings\Pages\ManageSiteSettings;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Site';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public static function getEloquentQuery(): Builder
    {
        SiteSetting::current();

        return parent::getEloquentQuery()->whereKey(1);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('site_name')->required()->maxLength(100),
                TextInput::make('tagline')->maxLength(180),
                TextInput::make('homepage_title')->required()->maxLength(160),
                Textarea::make('homepage_description')->maxLength(300)->rows(3)->columnSpanFull(),
                TextInput::make('contact_email')->email()->maxLength(255),
                FileUpload::make('logo_path')->image()->disk('public')->directory('brand')->visibility('public')->maxSize(2048),
                KeyValue::make('navigation_links')->label('Custom navigation links')
                    ->keyLabel('Link label')->valueLabel('URL or path')
                    ->keyPlaceholder('About us')->valuePlaceholder('/about or https://example.com/about')
                    ->reorderable()->columnSpanFull(),
                KeyValue::make('social_links')->label('Social profiles')
                    ->keyLabel('Network')->valueLabel('Profile URL')
                    ->keyPlaceholder('Instagram')->valuePlaceholder('https://…')
                    ->reorderable()->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('site_name'),
                TextEntry::make('homepage_title'),
                TextEntry::make('contact_email'),
                TextEntry::make('updated_at')->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site_name')->label('Site'),
                TextColumn::make('homepage_title')->label('Homepage headline'),
                TextColumn::make('contact_email')->label('Contact'),
                TextColumn::make('updated_at')->dateTime()->since(),
            ])
            ->filters([
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSiteSettings::route('/'),
        ];
    }
}
