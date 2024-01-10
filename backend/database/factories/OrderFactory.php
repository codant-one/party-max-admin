<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        $sub_total = $this->faker->randomFloat(2, 10, 100);
        $shipping_total =  $this->faker->randomFloat(2, 10, 100);
        $tax =  2;

        return [
            'shipping_state_id' => rand(1,4),
            'payment_state_id' => rand(1,4),
            'address_id' => Address::InRandomOrder()->first()->id,
            'sub_total' => $sub_total,
            'shipping_total' => $shipping_total,
            'tax' => $tax,
            'total' => $sub_total + $shipping_total + $tax
        ];
    }
}
