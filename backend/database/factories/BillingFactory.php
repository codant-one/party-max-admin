<?php

namespace Database\Factories;

use App\Models\Billing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class BillingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Billing::class;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        $arr = array("\r\n", "\n", "\r");
        $pse = rand(0, 1);

        return [
            'province_id' => rand(1,1996),
            'pse' => $pse,
            'card_number' => ($pse === 0) ? $this->faker->creditCardNumber :  null,
            'card_name' => ($pse === 0) ? $this->faker->name :  null,
            'expired_date' => ($pse === 0) ? $this->faker->creditCardExpirationDate :  null,
            'cvv_code' => ($pse === 0) ? '123' :  null,
            'name' => $this->faker->name,
            'last_name' => $this->faker->lastName                                  ,
            'company' => $this->faker->company ,
            'email' => $this->faker->email,
            'phone' => $this->faker->e164PhoneNumber,
            'address' => str_replace($arr, '', $this->faker->address),
            'street' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'postal_code' => $this->faker->postcode
        ];
    }
}
