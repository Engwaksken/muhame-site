<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Resources\SiteSettings\Pages\ManageSiteSettings;
use App\Models\SiteSetting;
use BackedEnum;
use Closure;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static ?string $navigationLabel = 'Site settings';

    protected static ?string $modelLabel = 'site settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Site';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 10;

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
        $fontOptions = collect(SiteSetting::FONTS)->map(fn (array $font) => $font[0])->all();

        return $schema
            ->components([
                Tabs::make('Settings')->columnSpanFull()->persistTabInQueryString()->tabs([
                    Tab::make('General')->icon(Heroicon::OutlinedHome)->columns(2)->schema([
                        TextInput::make('site_name')->required()->maxLength(100),
                        TextInput::make('tagline')->maxLength(180)->helperText('Shown in the footer and search results.'),
                        Section::make('Homepage fallback')->columnSpanFull()->collapsed()
                            ->description('Only used when no page is published as the homepage. The homepage then lists the latest insights.')
                            ->schema([
                                TextInput::make('homepage_title')->required()->maxLength(160),
                                Textarea::make('homepage_description')->maxLength(300)->rows(2),
                            ]),
                    ]),

                    Tab::make('Contact & map')->icon(Heroicon::OutlinedMapPin)->columns(2)->schema([
                        TextInput::make('contact_email')->label('Email')->email()->maxLength(255)
                            ->helperText('Contact form messages are sent here. Also shown on the contact page and in the footer.'),
                        TextInput::make('contact_phone')->label('Phone')->tel()->maxLength(40)->placeholder('+256 700 000 000'),
                        Textarea::make('contact_address')->label('Address')->rows(3)->maxLength(500)
                            ->placeholder("Plot 12, Example Road\nKampala, Uganda"),
                        Textarea::make('office_hours')->label('Opening hours')->rows(3)->maxLength(300)
                            ->placeholder("Mon – Fri: 8:00 – 17:00\nSat: 9:00 – 13:00"),
                        Section::make('Map')->columnSpanFull()
                            ->description('Use either option. If both are filled in, the embed code is used for the map and the location for the "Get directions" link.')
                            ->schema([
                                Textarea::make('map_embed_url')->label('Option 1: paste map embed code')->rows(3)
                                    ->placeholder('<iframe src="https://www.google.com/maps/embed?pb=…" …></iframe>')
                                    ->helperText('In Google Maps, find your place, click Share → Embed a map → Copy HTML, and paste it here. OpenStreetMap embed code also works.')
                                    ->rules([fn () => function (string $attribute, mixed $value, Closure $fail): void {
                                        if (filled($value) && SiteSetting::extractMapEmbedUrl($value) === null) {
                                            $fail('This doesn\'t look like Google Maps or OpenStreetMap embed code. Copy it from Share → Embed a map.');
                                        }
                                    }])
                                    ->dehydrateStateUsing(fn (?string $state) => SiteSetting::extractMapEmbedUrl($state)),
                                TextInput::make('map_location')->label('Option 2: map location')->maxLength(255)
                                    ->placeholder('Muhame, Kampala, Uganda   or   0.3476,32.5825')
                                    ->helperText('An address, place name, or latitude,longitude. Also used for the "Get directions" link.'),
                            ]),
                    ]),

                    Tab::make('Branding')->icon(Heroicon::OutlinedSwatch)->schema([
                        Section::make('Logo & favicon')->columns(2)->schema([
                            FileUpload::make('logo_path')->label('Logo')->image()->disk('public')->directory('brand')
                                ->visibility('public')->maxSize(2048)->helperText('Shown in the header. PNG or SVG with a transparent background works best.'),
                            FileUpload::make('favicon_path')->label('Favicon')->disk('public')->directory('brand')->visibility('public')
                                ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon'])
                                ->maxSize(512)->helperText('The small icon in browser tabs. A square PNG (at least 64×64) or ICO file.'),
                            Radio::make('header_display')->label('Show in the header')->options(SiteSetting::HEADER_DISPLAY)
                                ->default('logo')->formatStateUsing(fn (?string $state) => $state ?? 'logo')->inline()->columnSpanFull()
                                ->helperText('Without a logo uploaded, the site name is always shown.'),
                        ]),
                        Section::make('Colours')->columns(3)
                            ->description('Used for buttons, links, banners and highlights.')
                            ->schema([
                                Select::make('primary_text_color')->label('Text on primary colour')->options(SiteSetting::PRIMARY_TEXT)
                                    ->selectablePlaceholder(false)->default('white')->formatStateUsing(fn (?string $state) => $state ?? 'white')
                                    ->helperText('Text colour on buttons and banners in the primary colour.'),
                                ColorPicker::make('primary_color')->label('Primary colour')->placeholder(SiteSetting::DEFAULT_PRIMARY)
                                    ->regex(SiteSetting::HEX_PATTERN)->helperText('Buttons, links and highlights.'),
                                ColorPicker::make('accent_color')->label('Dark colour')->placeholder(SiteSetting::DEFAULT_ACCENT)
                                    ->regex(SiteSetting::HEX_PATTERN)->helperText('Footer, statistics and dark banners.'),
                            ]),
                        Section::make('Fonts')->columns(3)
                            ->description('Fonts are loaded from Bunny Fonts, a privacy-friendly font service.')
                            ->schema([
                                Select::make('body_font')->label('Text font')->options($fontOptions)
                                    ->placeholder(SiteSetting::FONTS[SiteSetting::DEFAULT_FONT][0])->searchable(),
                                Select::make('heading_font')->label('Heading font')->options($fontOptions)
                                    ->placeholder('Same as text font')->searchable(),
                                Select::make('base_font_size')->label('Text size')
                                    ->options(['small' => 'Small', 'normal' => 'Normal', 'large' => 'Large', 'x-large' => 'Extra large'])
                                    ->placeholder('Normal'),
                            ]),
                    ]),

                    Tab::make('Menu')->icon(Heroicon::OutlinedBars3)->schema([
                        KeyValue::make('navigation_links')->label('Extra menu links')
                            ->helperText('Pages marked "Show in site menu" appear automatically. Add other links here, e.g. to a partner site.')
                            ->keyLabel('Link label')->valueLabel('URL or path')
                            ->keyPlaceholder('Shop')->valuePlaceholder('/shop or https://example.com')
                            ->reorderable(),
                    ]),

                    Tab::make('Footer & social')->icon(Heroicon::OutlinedRectangleGroup)->schema([
                        Textarea::make('footer_text')->label('Footer about text')->rows(3)->maxLength(500)
                            ->helperText('A short description or address shown in the footer.'),
                        KeyValue::make('footer_links')->label('Footer links')
                            ->keyLabel('Link label')->valueLabel('URL or path')
                            ->keyPlaceholder('Privacy policy')->valuePlaceholder('/privacy')
                            ->reorderable(),
                        KeyValue::make('social_links')->label('Social profiles')
                            ->keyLabel('Network')->valueLabel('Profile URL')
                            ->keyPlaceholder('LinkedIn')->valuePlaceholder('https://…')
                            ->reorderable(),
                    ]),

                    Tab::make('Newsletter')->icon(Heroicon::OutlinedEnvelope)->columns(2)->schema([
                        TextInput::make('newsletter_heading')->label('Heading')->maxLength(150)->placeholder('Stay in the loop'),
                        TextInput::make('newsletter_text')->label('Text')->maxLength(300)
                            ->placeholder('Get our latest insights and upcoming events in your inbox.'),
                    ]),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSiteSettings::route('/'),
        ];
    }
}
