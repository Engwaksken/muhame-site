<?php

namespace App\Filament\Blocks;

use App\Models\Category;
use App\Support\FaIcon;
use App\Support\SafeUrl;
use Closure;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Str;

/** The content blocks editors can stack to build a page (homepage, About, etc.). */
class PageBlocks
{
    /** Block types the public site knows how to render. */
    public const TYPES = ['hero', 'text', 'image_text', 'cards', 'stats', 'methodology_showcase', 'learner_journey', 'latest_posts', 'upcoming_events', 'cta_banner', 'newsletter'];

    public static function builder(string $name = 'blocks'): Builder
    {
        return Builder::make($name)
            ->label('Page content')
            ->blocks(self::blocks())
            ->addActionLabel('Add a section')
            ->reorderableWithButtons()
            ->collapsible()
            ->cloneable()
            ->blockNumbers(false)
            ->columnSpanFull();
    }

    /** @return array<Block> */
    public static function blocks(): array
    {
        return [
            Block::make('hero')->label(self::label('Hero banner'))->icon(FaIcon::make('fa-wand-magic-sparkles'))->schema([
                TextInput::make('heading')->maxLength(120),
                Textarea::make('text')->rows(2)->maxLength(300),
                self::image('image')->helperText('Wide illustration or photo shown beside the headline. Optional.'),
                ...self::button(),
            ]),

            Block::make('text')->label(self::label('Text section'))->icon(FaIcon::make('fa-align-left'))->schema([
                TextInput::make('heading')->maxLength(150),
                Select::make('layout')->options(['standard' => 'Standard reading column', 'wide' => 'Wide content', 'centered' => 'Centered content', 'split' => 'Two-column content'])->default('standard'),
                RichEditor::make('content'),
                ...self::button(),
            ]),

            Block::make('image_text')->label(self::label('Image + text'))->icon(FaIcon::make('fa-image'))->schema([
                TextInput::make('heading')->maxLength(150),
                RichEditor::make('content'),
                self::image('image')->required(),
                Select::make('image_position')->options(['left' => 'Image on the left', 'right' => 'Image on the right'])->default('left')->required(),
                ...self::button(),
            ]),

            Block::make('cards')->label(self::label('Card row'))->icon(FaIcon::make('fa-table-cells-large'))->schema([
                TextInput::make('heading')->maxLength(150),
                Textarea::make('intro')->rows(2),
                Select::make('layout')->options(['cards' => 'Cards', 'numbered' => 'Numbered list', 'minimal' => 'Minimal columns'])->default('cards'),
                Select::make('image_ratio')->label('Image crop')->options(['landscape' => 'Landscape (16:10)', 'square' => 'Square (1:1)', 'portrait' => 'Portrait (4:5)'])->default('landscape')
                    ->helperText('Images fill the selected shape and are cropped to fit.'),
                Select::make('columns')->options([2 => '2 per row', 3 => '3 per row', 4 => '4 per row'])->default(3)->required(),
                Repeater::make('cards')->schema([
                    self::image('image')->helperText('Optional. When an image is uploaded, it is shown instead of the icon.'),
                    TextInput::make('icon')->label('Icon (optional)')->maxLength(80)
                        ->placeholder('fa-graduation-cap')->regex('/^fa-[a-z0-9]+(?:-[a-z0-9]+)*$/')
                        ->helperText('Font Awesome solid icon name, e.g. fa-users, fa-leaf or fa-graduation-cap.'),
                    Select::make('alignment')->label('Content alignment')
                        ->options(['left' => 'Left', 'center' => 'Center', 'right' => 'Right'])->default('left'),
                    TextInput::make('title')->required()->maxLength(120),
                    Textarea::make('text')->rows(2),
                    Textarea::make('source')->label('Source text (optional)')->rows(2)->maxLength(300)
                        ->helperText('A citation or credit displayed beneath the card text.'),
                    Grid::make(2)->schema([
                        self::url('url')->label('Link'),
                        TextInput::make('link_label')->maxLength(40)->placeholder('Read more'),
                    ]),
                ])->minItems(1)->maxItems(12)->defaultItems(3)->collapsible()->reorderableWithButtons()->itemLabel(fn (array $state) => $state['title'] ?? null),
            ]),

            Block::make('stats')->label(self::label('Key statistics'))->icon(FaIcon::make('fa-chart-column'))->schema([
                TextInput::make('heading')->maxLength(150),
                Select::make('layout')->options(['band' => 'Highlight band', 'cards' => 'Individual cards', 'inline' => 'Inline figures'])->default('band'),
                Repeater::make('items')->label('Statistics')->schema([
                    TextInput::make('value')->required()->maxLength(20)->placeholder('25,000+'),
                    Textarea::make('label')->required()->rows(2)->maxLength(500)->placeholder('people trained'),
                ])->columns(2)->minItems(1)->maxItems(4)->defaultItems(3)->reorderableWithButtons(),
            ]),

            Block::make('methodology_showcase')->label('NeLP methodology showcase')->icon(FaIcon::make('fa-diagram-project'))->schema([
                TextInput::make('eyebrow')->label('Small label')->default('04 · THE METHODOLOGY')->maxLength(80),
                TextInput::make('heading')->maxLength(150),
                Textarea::make('intro')->label('Subtitle')->rows(2)->maxLength(300),
                TextInput::make('about_heading')->default('ABOUT NeLP®')->maxLength(100),
                Textarea::make('about_content')->label('About the methodology')->rows(4)->maxLength(1200),
                Repeater::make('highlights')->label('Highlights row')->schema([
                    TextInput::make('title')->required()->maxLength(120),
                    Textarea::make('text')->rows(2)->maxLength(350),
                    TextInput::make('source')->maxLength(180),
                ])->columns(1)->minItems(1)->maxItems(4)->defaultItems(4)->collapsible()->reorderableWithButtons(),
                Repeater::make('experiences')->label('Numbered learning experiences')->schema([
                    TextInput::make('number')->required()->maxLength(4)->placeholder('01'),
                    TextInput::make('title')->required()->maxLength(120),
                    Textarea::make('text')->required()->rows(2)->maxLength(350),
                    TextInput::make('evidence')->maxLength(220),
                ])->columns(2)->minItems(1)->maxItems(6)->defaultItems(4)->collapsible()->reorderableWithButtons(),
                Repeater::make('journey')->label('Learner journey steps')->schema([
                    TextInput::make('label')->required()->maxLength(60),
                ])->columns(2)->minItems(2)->maxItems(8)->defaultItems(6)->collapsible()->reorderableWithButtons(),
                Textarea::make('journey_note')->label('Text under journey')->rows(2)->maxLength(300),
            ]),

            Block::make('learner_journey')->label('Learner journey timeline')->icon(FaIcon::make('fa-route'))->schema([
                TextInput::make('heading')->default('THE LEARNER JOURNEY')->maxLength(120),
                Repeater::make('steps')->label('Journey steps')->schema([
                    TextInput::make('label')->required()->maxLength(60),
                ])->columns(2)->minItems(2)->maxItems(8)->defaultItems(6)->collapsible()->reorderableWithButtons()
                    ->default([['label' => 'Sign up'], ['label' => 'Get assessed'], ['label' => 'Learn adaptively'], ['label' => 'Get certified'], ['label' => 'Get matched'], ['label' => 'Get placed']]),
                Textarea::make('note')->label('Supporting text')->rows(2)->maxLength(300),
            ]),

            Block::make('latest_posts')->label(self::label('Latest insights'))->icon(FaIcon::make('fa-newspaper'))->schema([
                TextInput::make('heading')->default('Latest insights')->maxLength(150),
                Select::make('category_id')->label('Only from category')->placeholder('All categories')
                    ->options(fn () => Category::query()->orderBy('name')->pluck('name', 'id')),
                Select::make('count')->options([3 => '3', 4 => '4 (1 featured + 3)', 6 => '6'])->default(4)->required(),
            ]),

            Block::make('upcoming_events')->label(self::label('Upcoming events'))->icon(FaIcon::make('fa-calendar-days'))->schema([
                TextInput::make('heading')->default('Upcoming events')->maxLength(150),
                Select::make('count')->options([3 => '3', 4 => '4', 6 => '6'])->default(3)->required(),
            ]),

            Block::make('cta_banner')->label(self::label('Call-to-action banner'))->icon(FaIcon::make('fa-bullhorn'))->schema([
                TextInput::make('heading')->maxLength(150),
                Textarea::make('text')->rows(2)->maxLength(300),
                ...self::button(required: true),
            ]),

            Block::make('newsletter')->label(self::label('Newsletter signup'))->icon(FaIcon::make('fa-envelope'))->schema([
                TextInput::make('heading')->maxLength(150)->placeholder('Uses the heading from Site settings'),
                Textarea::make('text')->rows(2)->maxLength(300)->placeholder('Uses the text from Site settings'),
            ]),
        ];
    }

    /** Block title in the editor, followed by the section's own heading once filled in. */
    private static function label(string $type): Closure
    {
        return function (?array $state) use ($type): string {
            $heading = trim((string) ($state['heading'] ?? ''));

            return $heading === '' ? $type : $type.': '.Str::limit($heading, 60);
        };
    }

    private static function image(string $name): FileUpload
    {
        return FileUpload::make($name)->image()->disk('public')->directory('blocks')->visibility('public')->maxSize(5120);
    }

    /** @return array<Grid> */
    private static function button(bool $required = false): array
    {
        return [
            Grid::make(2)->schema([
                TextInput::make('button_label')->maxLength(40)->required($required),
                self::url('button_url')->label('Button link')->required($required),
            ]),
        ];
    }

    private static function url(string $name): TextInput
    {
        return TextInput::make($name)->maxLength(255)->placeholder('/about or https://…')
            ->rules([fn () => function (string $attribute, mixed $value, Closure $fail): void {
                if (filled($value) && SafeUrl::clean($value) === null) {
                    $fail('Use a full https:// link, a path starting with /, or a #anchor.');
                }
            }]);
    }
}
