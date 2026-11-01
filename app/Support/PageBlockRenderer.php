<?php

namespace App\Support;

use App\Filament\Blocks\PageBlocks;
use App\Models\Event;
use App\Models\Post;
use Illuminate\Support\Collection;

/** Turns a page's stored blocks into view data, loading posts/events for the dynamic ones. */
class PageBlockRenderer
{
    /**
     * @param  array<mixed>  $blocks
     * @return array<int, array{type: string, view: string, data: array<string, mixed>}>
     */
    public static function prepare(array $blocks): array
    {
        $prepared = [];

        foreach ($blocks as $block) {
            $type = is_array($block) ? ($block['type'] ?? null) : null;

            if (! in_array($type, PageBlocks::TYPES, true)) {
                continue;
            }

            $data = is_array($block['data'] ?? null) ? $block['data'] : [];

            $data = match ($type) {
                'latest_posts' => [...$data, 'posts' => self::latestPosts($data)],
                'upcoming_events' => [...$data, 'events' => self::upcomingEvents($data)],
                default => $data,
            };

            $prepared[] = ['type' => $type, 'view' => 'blocks.'.str_replace('_', '-', $type), 'data' => $data];
        }

        return $prepared;
    }

    /** @param array<string, mixed> $data */
    private static function latestPosts(array $data): Collection
    {
        return Post::query()->published()
            ->with(['category'])
            ->when(filled($data['category_id'] ?? null), fn ($q) => $q->where('category_id', $data['category_id']))
            ->latest('published_at')
            ->limit(self::count($data, 4))
            ->get();
    }

    /** @param array<string, mixed> $data */
    private static function upcomingEvents(array $data): Collection
    {
        return Event::query()->upcoming()->limit(self::count($data, 3))->get();
    }

    /** @param array<string, mixed> $data */
    private static function count(array $data, int $default): int
    {
        return max(1, min(12, (int) ($data['count'] ?? $default)));
    }
}
