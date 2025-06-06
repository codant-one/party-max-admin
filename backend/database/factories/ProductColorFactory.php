<?php

namespace Database\Factories;

use App\Models\ProductColor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ProductColorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductColor::class;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $in_stock = rand(0, 1);
        
        return [
            'color_id' => rand(1, 10),
            'sku' => rand(100000, 999999),
            'stock' => ($in_stock === 0) ? 0 : $this->faker->numberBetween(0, 100),
            'in_stock' => $in_stock,
        ];
        
    }
}
