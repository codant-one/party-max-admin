<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

use App\Models\BlogCategory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blog>
 */
class BlogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(4, true);

        return [
            'blog_category_id' => BlogCategory::InRandomOrder()->first()->id,
            'is_popular_blog' => rand(0, 1),
            'title' => $name,
            'description' => $this->faker->text(),
            'image' => 'blogs/' . $this->faker->file(public_path('images/blogs'), storage_path('app/public/blogs'), false),
            'slug' => Str::slug($name)
        ];
    }
}
