<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        $arr = array("\r\n", "\n", "\r");

        return [
            'phone' => $this->faker->e164PhoneNumber,
            'address' => str_replace($arr, '', $this->faker->address),
            'street' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'postal_code' => $this->faker->postcode,
            'province_id' => rand(1,1996),
            'addresses_type_id' => rand(1,2)
        ];
    }
}
