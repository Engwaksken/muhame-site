<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Post> */
class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('####'),
            'body' => fake()->paragraphs(3, true),
            'status' => 'draft',
            'published_at' => null,
            'featured_image' => null,
            'seo_title' => null,
            'seo_description' => null,
            'noindex' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => 'published', 'published_at' => now()]);
    }
}
