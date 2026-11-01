<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Creates a draft homepage with a full set of example sections, so editors
 * can see the layout and replace the text. It stays a draft (the live site
 * keeps its current homepage) until someone publishes it in the admin.
 * Runs only once: skipped if a homepage or a "home" page already exists.
 */
class StarterHomepageSeeder extends Seeder
{
    public function run(): void
    {
        if (Page::query()->where('is_homepage', true)->orWhere('slug', 'home')->exists()) {
            return;
        }

        Page::query()->create([
            'title' => 'Home',
            'slug' => 'home',
            'status' => 'draft',
            'is_homepage' => true,
            'excerpt' => 'Replace this with a one-sentence summary of who you are.',
            'blocks' => [
                ['type' => 'hero', 'data' => [
                    'heading' => 'A clear headline about the change you make',
                    'text' => 'One or two sentences that explain what your organisation does and who it is for.',
                    'button_label' => 'About us',
                    'button_url' => '/contact',
                ]],
                ['type' => 'cards', 'data' => [
                    'heading' => 'What we do',
                    'intro' => 'A short introduction to your main areas of work.',
                    'columns' => '3',
                    'cards' => [
                        ['title' => 'Research', 'text' => 'Describe this area of work in a sentence or two.', 'url' => '/insights', 'link_label' => 'Read more'],
                        ['title' => 'Programmes', 'text' => 'Describe this area of work in a sentence or two.', 'url' => '/events', 'link_label' => 'Read more'],
                        ['title' => 'Partnerships', 'text' => 'Describe this area of work in a sentence or two.', 'url' => '/contact', 'link_label' => 'Work with us'],
                    ],
                ]],
                ['type' => 'stats', 'data' => [
                    'heading' => 'Our impact',
                    'items' => [
                        ['value' => '1,000+', 'label' => 'people reached (replace with your figure)'],
                        ['value' => '25', 'label' => 'partner organisations (replace with your figure)'],
                        ['value' => '10', 'label' => 'years of work (replace with your figure)'],
                    ],
                ]],
                ['type' => 'latest_posts', 'data' => ['heading' => 'Latest insights', 'count' => '4']],
                ['type' => 'upcoming_events', 'data' => ['heading' => 'Upcoming events', 'count' => '3']],
                ['type' => 'cta_banner', 'data' => [
                    'heading' => 'Work with us',
                    'text' => 'Invite partners, funders or members to get in touch.',
                    'button_label' => 'Get in touch',
                    'button_url' => '/contact',
                ]],
                ['type' => 'newsletter', 'data' => []],
            ],
        ]);
    }
}
