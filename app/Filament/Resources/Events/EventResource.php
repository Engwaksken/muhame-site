<?php

namespace App\Filament\Resources\Events;

use App\Filament\Resources\Events\Pages\ManageEvents;
use App\Models\Event;
use App\Support\SafeUrl;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()->columnSpanFull()->tabs([
                    Tab::make('Details')->icon(Heroicon::OutlinedPencilSquare)->schema([
                        TextInput::make('title')->required()->maxLength(255)->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create'
                                ? $set('slug', Str::slug($state ?? '')) : null),
                        Textarea::make('summary')->rows(2)->maxLength(300)
                            ->helperText('One or two sentences shown on event cards.'),
                        FileUpload::make('image')->image()->disk('public')->directory('events')->visibility('public')->maxSize(5120),
                        RichEditor::make('body')->label('Full description'),
                    ]),

                    Tab::make('Date, place & publishing')->icon(Heroicon::OutlinedCalendarDays)->columns(2)->schema([
                        DateTimePicker::make('starts_at')->label('Starts')->required()->seconds(false),
                        DateTimePicker::make('ends_at')->label('Ends')->seconds(false)->after('starts_at'),
                        TextInput::make('location')->maxLength(255)->placeholder('Venue and town')
                            ->helperText('Leave empty if the event is online only.'),
                        self::url('online_url')->label('Online link')->placeholder('https://zoom.us/…'),
                        self::url('registration_url')->label('Registration link')->placeholder('https://…')
                            ->helperText('Shows a "Register" button. Leave empty if not needed.'),
                        Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])->default('draft')->required(),
                        TextInput::make('slug')->label('Address')->required()->maxLength(255)->unique(ignoreRecord: true)
                            ->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])->prefix('/events/')->columnSpanFull(),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->limit(50),
                TextColumn::make('starts_at')->label('Starts')->dateTime('j M Y, H:i')->sortable(),
                TextColumn::make('location')->placeholder('Online')->limit(30),
                TextColumn::make('status')->badge()->sortable()
                    ->color(fn (string $state) => $state === 'published' ? 'success' : 'gray'),
            ])
            ->filters([
                TernaryFilter::make('upcoming')->label('When')
                    ->trueLabel('Upcoming')->falseLabel('Past')
                    ->queries(
                        true: fn (Builder $query) => $query->where('starts_at', '>=', now()->startOfDay()),
                        false: fn (Builder $query) => $query->where('starts_at', '<', now()->startOfDay()),
                    ),
            ])
            ->recordActions([
                Action::make('open')->label('Open')->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Event $record) => route('events.show', $record->slug), shouldOpenInNewTab: true)
                    ->visible(fn (Event $record) => $record->status === 'published'),
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
            'index' => ManageEvents::route('/'),
        ];
    }

    private static function url(string $name): TextInput
    {
        return TextInput::make($name)->maxLength(255)
            ->rules([fn () => function (string $attribute, mixed $value, Closure $fail): void {
                if (filled($value) && SafeUrl::clean($value, allowRelative: false) === null) {
                    $fail('Please enter a full link starting with https://');
                }
            }]);
    }
}
