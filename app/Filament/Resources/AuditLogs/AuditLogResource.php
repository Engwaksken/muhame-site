<?php

namespace App\Filament\Resources\AuditLogs;

use App\Filament\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Models\AuditLog;
use App\Support\FaIcon;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Audit log';
    protected static ?string $modelLabel = 'audit entry';

    public static function getNavigationIcon(): \Illuminate\Contracts\Support\Htmlable { return FaIcon::make('fa-clipboard-list'); }

    public static function canViewAny(): bool { return auth()->user()?->can('audit.viewAny') ?? false; }
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('created_at')->dateTime(),
            TextEntry::make('actor.name')->label('Performed by'),
            TextEntry::make('event'),
            TextEntry::make('subject_type'),
            TextEntry::make('subject_id'),
            TextEntry::make('ip_address'),
            TextEntry::make('old_values')->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT)),
            TextEntry::make('new_values')->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            TextColumn::make('created_at')->dateTime()->sortable(),
            TextColumn::make('actor.name')->label('User')->placeholder('System')->searchable(),
            TextColumn::make('event')->badge(),
            TextColumn::make('subject_type')->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—'),
            TextColumn::make('subject_id'),
            TextColumn::make('ip_address'),
        ])->recordActions([\Filament\Actions\ViewAction::make()]);
    }

    public static function getPages(): array { return ['index' => ListAuditLogs::route('/')]; }
}
