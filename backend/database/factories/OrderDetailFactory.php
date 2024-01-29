<?php

namespace Database\Factories;

use App\Models\OrderDetail;
use App\Models\ProductColor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class OrderDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderDetail::class;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        $price = $this->faker->randomFloat(2, 10, 100);
        $quantity = $this->faker->numberBetween(1, 10);

        return [
            'product_color_id' => ProductColor::InRandomOrder()->first()->id,
            'price' => $price,
            'quantity' => $quantity,
            'total' => $price * $quantity
        ];
    }
}
