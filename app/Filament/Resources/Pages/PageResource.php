<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\ManagePages;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Site';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true)
                    ->prefix(url('/').'/')
                    ->rules(['regex:/^'.Page::SLUG_PATTERN.'$/', Rule::notIn(Page::RESERVED_SLUGS)])
                    ->validationMessages([
                        'regex' => 'Use lowercase letters, numbers and single hyphens only.',
                        'not_in' => 'This address is already used by the site. Please choose another.',
                    ]),
                RichEditor::make('body')->required()->columnSpanFull(),
                Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])->default('draft')->required(),
                Toggle::make('show_in_navigation')->label('Show in site menu'),
                TextInput::make('navigation_order')->label('Menu order')->numeric()->minValue(0)->maxValue(999)->default(0)
                    ->helperText('Lower numbers appear first.'),
                TextInput::make('seo_title')->label('SEO title')->maxLength(255)->helperText('Leave blank to use the page title.'),
                Textarea::make('seo_description')->label('SEO description')->maxLength(160)->rows(2)
                    ->helperText('Up to 160 characters. Leave blank to use an excerpt.'),
                Toggle::make('noindex')->label('Hide from search engines'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('navigation_order')
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('slug')->prefix('/')->searchable(),
                TextColumn::make('status')->badge()->sortable()
                    ->color(fn (string $state) => $state === 'published' ? 'success' : 'gray'),
                IconColumn::make('show_in_navigation')->label('In menu')->boolean(),
                TextColumn::make('navigation_order')->label('Order')->sortable(),
                TextColumn::make('updated_at')->dateTime()->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
            ])
            ->recordActions([
                Action::make('open')->label('Open')->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Page $record) => route('pages.show', $record->slug), shouldOpenInNewTab: true)
                    ->visible(fn (Page $record) => $record->status === 'published'),
                EditAction::make(),
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
            'index' => ManagePages::route('/'),
        ];
    }
}
