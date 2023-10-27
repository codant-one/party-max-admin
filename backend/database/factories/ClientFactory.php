<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = rand(1,3); //1 Femenino, 2 Masculino, 3: Otro

        $mod_create_date = strtotime(Carbon::now()->format('Y-m-d H:m:s')."- ".rand(1,60)." days");
        $created_at = date("Y-m-d H:m:s",$mod_create_date);

        return [
            'gender_id' => $gender,
            'birthcountry_id' => rand(1,200),
            'nationality_id' => 1,
            'birthday' => $this->faker->date,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ];
    }
}
