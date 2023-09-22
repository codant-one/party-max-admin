<?php

namespace Database\Factories;

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
        return [
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->sentence,
            'sku' => rand(100000, 999999),
            'price' => $this->faker->randomFloat(2, 10, 100), 
            'price_for_sale' => $this->faker->randomFloat(2, 5, 50), 
            'stock' => $this->faker->numberBetween(0, 100),
            'image' => 'products/main/' . $this->faker->file(public_path('images/products/main'), storage_path('app/public/products/main'), false),
            'created_at' => now(),
            'updated_at' => now()
        ];
        
    }
}
