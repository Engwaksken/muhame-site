<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(2),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'status' => 'published',
            'show_in_navigation' => false,
            'navigation_order' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function inNavigation(int $order = 0): static
    {
        return $this->state(['show_in_navigation' => true, 'navigation_order' => $order]);
    }
}
