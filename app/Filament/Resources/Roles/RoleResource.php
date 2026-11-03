<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\ManageRoles;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Roles & permissions';

    public static function canViewAny(): bool { return auth()->user()?->can('roles.viewAny') ?? false; }
    public static function canCreate(): bool { return auth()->user()?->can('roles.create') ?? false; }
    public static function canEdit($record): bool { return auth()->user()?->can('roles.update') ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->can('roles.delete') && ! in_array($record->name, ['Super Admin', 'Admin'], true); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->unique(ignoreRecord: true),
            CheckboxList::make('permissions')->relationship(titleAttribute: 'name')->columns(3)->bulkToggleable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('permissions_count')->counts('permissions')->label('Permissions'),
            TextColumn::make('users_count')->counts('users')->label('Users'),
        ])->recordActions([
            \Filament\Actions\EditAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array { return ['index' => ManageRoles::route('/')]; }
}
