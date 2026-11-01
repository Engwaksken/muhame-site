<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'summary', 'body', 'starts_at', 'ends_at', 'location', 'online_url', 'registration_url', 'image', 'status'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Event $event): void {
            if (blank($event->slug) && filled($event->title)) {
                $event->slug = Str::slug($event->title);
            }
        });
    }

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /** Events that haven't finished yet, soonest first. */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->published()
            ->where(fn (Builder $q) => $q->where('ends_at', '>=', now())
                ->orWhere(fn (Builder $q) => $q->whereNull('ends_at')->where('starts_at', '>=', now()->startOfDay())))
            ->orderBy('starts_at');
    }

    /** Finished events, most recent first. */
    public function scopePast(Builder $query): Builder
    {
        return $query->published()
            ->where(fn (Builder $q) => $q->where('ends_at', '<', now())
                ->orWhere(fn (Builder $q) => $q->whereNull('ends_at')->where('starts_at', '<', now()->startOfDay())))
            ->orderByDesc('starts_at');
    }

    public function isOnline(): bool
    {
        return filled($this->online_url) && blank($this->location);
    }
}
