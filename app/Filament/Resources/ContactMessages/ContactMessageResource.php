<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Models\ContactMessage;
use App\Support\FaIcon;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationLabel = 'Inbox';

    protected static ?string $modelLabel = 'message';

    protected static string|\UnitEnum|null $navigationGroup = 'Site';

    public static function getNavigationIcon(): \Illuminate\Contracts\Support\Htmlable { return FaIcon::make('fa-inbox'); }

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $unread = ContactMessage::query()->whereNull('read_at')->count();

        return $unread > 0 ? (string) $unread : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')->copyable(),
                TextEntry::make('subject')->placeholder('No subject')->columnSpanFull(),
                TextEntry::make('message')->columnSpanFull()->extraAttributes(['class' => 'whitespace-pre-line']),
                TextEntry::make('created_at')->label('Received')->dateTime(),
                TextEntry::make('ip_address')->label('IP address')->placeholder('Unknown'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()
                    ->weight(fn (ContactMessage $record) => $record->read_at ? null : 'bold'),
                TextColumn::make('email')->searchable(),
                TextColumn::make('subject')->limit(50)->placeholder('No subject'),
                TextColumn::make('created_at')->label('Received')->since()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('read')->label('Status')
                    ->trueLabel('Read')->falseLabel('Unread')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('read_at'),
                        false: fn (Builder $query) => $query->whereNull('read_at'),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('markRead')->label('Mark as read')->icon(FaIcon::make('fa-check'))
                    ->visible(fn (ContactMessage $record) => ! $record->read_at)
                    ->action(fn (ContactMessage $record) => $record->markAsRead()),
                Action::make('reply')->label('Reply')->icon(FaIcon::make('fa-envelope'))
                    ->url(fn (ContactMessage $record) => 'mailto:'.$record->email.'?subject='.rawurlencode('Re: '.($record->subject ?: 'Your message'))),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageContactMessages::route('/'),
        ];
    }
}
