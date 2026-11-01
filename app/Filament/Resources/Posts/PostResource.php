<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\ManagePosts;
use App\Models\Post;
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
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
                TextInput::make('title')->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                TextInput::make('seo_title')->label('SEO title')->maxLength(255)->helperText('Leave blank to use the post title.'),
                Textarea::make('seo_description')->label('SEO description')->maxLength(160)->rows(2)
                    ->helperText('Up to 160 characters. Leave blank to use an excerpt.'),
                Toggle::make('noindex')->label('Hide from search engines')
                    ->helperText('The post remains public but is excluded from the sitemap and marked noindex.'),
                Select::make('category_id')->relationship('category', 'name')->searchable()->preload()->nullable(),
                Select::make('tags')->relationship('tags', 'name')->multiple()->searchable()->preload(),
                RichEditor::make('body')->required()->columnSpanFull(),
                FileUpload::make('featured_image')->image()->disk('public')->directory('posts')->visibility('public')->maxSize(5120),
                Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])
                    ->default('draft')->required()->visible(fn () => auth()->user()?->can('posts.publish') ?? false),
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
                ViewAction::make(),
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
            'index' => ManagePosts::route('/'),
        ];
    }
}
