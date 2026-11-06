<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Blocks\PageBlocks;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
use App\Support\FaIcon;
use App\Support\SafeUrl;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Site';

    public static function getNavigationIcon(): \Illuminate\Contracts\Support\Htmlable { return FaIcon::make('fa-file-lines'); }

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()->columnSpanFull()->persistTabInQueryString()->tabs([
                    Tab::make('Page header')->icon(FaIcon::make('fa-pen-to-square'))->columns(2)->schema([
                        TextInput::make('title')->required()->maxLength(255)->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create'
                                ? $set('slug', Str::slug($state ?? '')) : null),
                        Textarea::make('excerpt')->label('Introduction')->rows(3)->maxLength(300)
                            ->helperText('Shown under the title at the top of the page, and in menu dropdowns.'),
                        TextInput::make('header_link_label')->label('Top header button text')->maxLength(60)->requiredWith('header_link_url')
                            ->helperText('Optional page-specific button shown in the top header.'),
                        TextInput::make('header_link_url')->label('Top header button URL')->maxLength(255)->requiredWith('header_link_label')
                            ->placeholder('https://example.com')
                            ->rules([fn () => function (string $attribute, mixed $value, Closure $fail): void {
                                if (filled($value) && SafeUrl::clean($value, allowRelative: false) === null) {
                                    $fail('Use a valid https://, http://, or mailto: URL.');
                                }
                            }]),
                        FileUpload::make('hero_image')->label('Header image')->image()->disk('public')->directory('pages')
                            ->visibility('public')->maxSize(5120)->columnSpanFull()
                            ->helperText('Optional wide image at the top of the page. Not shown when the page starts with a Hero banner section.'),
                    ]),

                    Tab::make('Sections')->icon(FaIcon::make('fa-table-cells-large'))
                        ->badge(fn (Get $get) => count($get('blocks') ?? []) ?: null)
                        ->schema([
                            PageBlocks::builder()->collapsed(),
                        ]),

                    Tab::make('Simple text')->icon(FaIcon::make('fa-file-lines'))->schema([
                        RichEditor::make('body')->hiddenLabel()
                            ->helperText('Optional. Shown only when the page has no sections. Handy for plain pages like a privacy policy.'),
                    ]),

                    Tab::make('Publishing & menu')->icon(FaIcon::make('fa-bars'))->columns(2)->schema([
                        Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])->default('draft')->required(),
                        TextInput::make('slug')->label('Address')->required()->maxLength(255)->unique(ignoreRecord: true)
                            ->prefix(url('/').'/')
                            ->rules(['regex:/^'.Page::SLUG_PATTERN.'$/', Rule::notIn(Page::RESERVED_SLUGS)])
                            ->validationMessages([
                                'regex' => 'Use lowercase letters, numbers and single hyphens only.',
                                'not_in' => 'This address is already used by the site. Please choose another.',
                            ]),
                        Toggle::make('is_homepage')->label('Use as the homepage')
                            ->helperText('Shown at the site root (/). Replaces the current homepage, if there is one.')->columnSpanFull(),
                        Section::make('Site menu')->columns(3)->columnSpanFull()->schema([
                            Toggle::make('show_in_navigation')->label('Show in site menu')->inline(false),
                            Select::make('parent_id')->label('Appears under')->placeholder('Top level of the menu')
                                ->relationship('parent', 'title', fn (Builder $query, ?Page $record) => $query
                                    ->whereNull('parent_id')
                                    ->when($record, fn (Builder $q) => $q->whereKeyNot($record->getKey())))
                                ->helperText('Pick a parent to show this page in its dropdown.'),
                            TextInput::make('navigation_order')->label('Menu order')->numeric()->minValue(0)->maxValue(999)->default(0)
                                ->helperText('Lower numbers appear first.'),
                        ]),
                    ]),

                    Tab::make('SEO')->icon(FaIcon::make('fa-magnifying-glass'))->schema([
                        TextInput::make('seo_title')->label('SEO title')->maxLength(255)->helperText('Leave blank to use the page title.'),
                        Textarea::make('seo_description')->label('SEO description')->maxLength(160)->rows(2)
                            ->helperText('Up to 160 characters. Leave blank to use the introduction.'),
                        Toggle::make('noindex')->label('Hide from search engines'),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('navigation_order')
            ->columns([
                TextColumn::make('title')->searchable()->sortable()
                    ->description(fn (Page $record) => $record->parent ? 'Under: '.$record->parent->title : null),
                TextColumn::make('slug')->label('Address')->prefix('/')->searchable(),
                TextColumn::make('status')->badge()->sortable()
                    ->color(fn (string $state) => $state === 'published' ? 'success' : 'gray'),
                IconColumn::make('is_homepage')->label('Home')->boolean()->trueIcon(FaIcon::make('fa-house'))->falseIcon(null),
                IconColumn::make('show_in_navigation')->label('In menu')->boolean(),
                TextColumn::make('navigation_order')->label('Order')->sortable(),
                TextColumn::make('updated_at')->dateTime()->since()->sortable(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with('parent'))
            ->filters([
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
            ])
            ->recordActions([
                Action::make('open')->label('Open')->icon(FaIcon::make('fa-arrow-up-right-from-square'))
                    ->url(fn (Page $record) => $record->url(), shouldOpenInNewTab: true)
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
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
