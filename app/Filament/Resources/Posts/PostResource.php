<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\ManagePosts;
use App\Models\Post;
use App\Support\FaIcon;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    public static function getNavigationIcon(): \Illuminate\Contracts\Support\Htmlable { return FaIcon::make('fa-newspaper'); }

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Editor', 'Moderator'])) {
            return $query;
        }

        if ($user->hasRole('Viewer')) {
            return $query->where('status', 'published');
        }

        return $query->where(function (Builder $query) use ($user): void {
            $query->where('user_id', $user->id)->orWhere('status', 'published');
        });
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()->columnSpanFull()->tabs([
                    Tab::make('Content')->icon(FaIcon::make('fa-pen-to-square'))->schema([
                        TextInput::make('title')->required()->maxLength(255)->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create'
                                ? $set('slug', Str::slug($state ?? '')) : null),
                        RichEditor::make('body')->required(),
                    ]),

                    Tab::make('Image & category')->icon(FaIcon::make('fa-image'))->columns(2)->schema([
                        FileUpload::make('featured_image')->label('Featured image')->image()->disk('public')->directory('posts')
                            ->visibility('public')->maxSize(5120)->columnSpanFull()
                            ->helperText('Shown on insight cards and at the top of the post.'),
                        Select::make('category_id')->label('Category')->relationship('category', 'name')->searchable()->preload()->nullable()
                            ->helperText('Shown as the label on cards, e.g. Report, News.'),
                        Select::make('tags')->relationship('tags', 'name')->multiple()->searchable()->preload(),
                    ]),

                    Tab::make('Publishing & SEO')->icon(FaIcon::make('fa-globe'))->columns(2)->schema([
                        Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])
                            ->default('draft')->required()->visible(fn () => auth()->user()?->can('posts.publish') ?? false),
                        TextInput::make('slug')->label('Address')->required()->maxLength(255)->unique(ignoreRecord: true)
                            ->prefix('/insights/')->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])
                            ->validationMessages(['regex' => 'Use lowercase letters, numbers and single hyphens only.']),
                        TextInput::make('seo_title')->label('SEO title')->maxLength(255)->helperText('Leave blank to use the post title.'),
                        Textarea::make('seo_description')->label('SEO description')->maxLength(160)->rows(2)
                            ->helperText('Up to 160 characters. Leave blank to use an excerpt.'),
                        Toggle::make('noindex')->label('Hide from search engines')->columnSpanFull()
                            ->helperText('The post stays public but is left out of the sitemap and marked noindex.'),
                    ]),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('slug'),
                TextEntry::make('status')->badge(),
                TextEntry::make('category.name')->label('Category'),
                TextEntry::make('author.name')->label('Author'),
                TextEntry::make('published_at')->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')->disk('public'),
                TextColumn::make('title')->searchable()->sortable()->limit(50),
                TextColumn::make('category.name')->label('Category')->sortable(),
                TextColumn::make('author.name')->label('Author')->searchable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('published_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
                SelectFilter::make('category')->relationship('category', 'name'),
            ])
            ->recordActions([
                ViewAction::make()->modalWidth(Width::FourExtraLarge),
                EditAction::make()->modalWidth(Width::FourExtraLarge),
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
            'index' => ManagePosts::route('/'),
        ];
    }
}
