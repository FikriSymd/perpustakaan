<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'category_id' => Category::factory(),
            'title' => ucwords($title),
            'author' => fake()->name(),
            'publisher' => fake()->company(),
            'publication_year' => fake()->numberBetween(1990, (int) date('Y')),
            'isbn' => fake()->unique()->numerify('978##########'),
            'stock_total' => 3,
            'stock_available' => 3,
            'description' => fake()->paragraph(),
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_total' => 0,
            'stock_available' => 0,
        ]);
    }
}
