<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->addDays(fake()->numberBetween(3, 60))->setTime(14, 0);

        return [
            'title' => fake()->unique()->sentence(4),
            'slug' => fake()->unique()->slug(3),
            'summary' => fake()->sentence(12),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHours(2),
            'location' => fake()->city(),
            'status' => 'published',
        ];
    }

    public function past(): static
    {
        return $this->state(function () {
            $start = now()->subDays(fake()->numberBetween(5, 60))->setTime(14, 0);

            return ['starts_at' => $start, 'ends_at' => $start->copy()->addHours(2)];
        });
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }
}
