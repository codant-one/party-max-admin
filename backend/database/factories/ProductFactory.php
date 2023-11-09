<?php

namespace Database\Factories;

use Str;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->unique()->words(3, true);
        $rand = rand(1, 0);

        return [
            'user_id' => rand(1, 20),
            'brand_id' => rand(1, 2),
            'name' => $name,
            'description' => $this->faker->sentence,
            'single_description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 100), 
            'price_for_sale' => $this->faker->randomFloat(2, 5, 50), 
            'wholesale_price' => $this->faker->randomFloat(2, 5, 50), 
            'stock' => $this->faker->numberBetween(0, 100),
            'sales' => $this->faker->numberBetween(0, 100),
            'rating' => $this->faker->numberBetween(0, 5),
            'slug' => Str::slug($name),
            'image' => 'products/main/' . $this->faker->file(public_path('images/products/main'), storage_path('app/public/products/main'), false),
            'estimated_delivery_time' => ($rand === 1) ? now()->addHours(rand(1, 20)) : now()->addDays(rand(1, 2)),
            'created_at' => now(),
            'updated_at' => now()
        ];
        
    }
}
