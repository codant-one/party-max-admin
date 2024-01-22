<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @var string
     */

     protected $model = Supplier::class;
     

    public function definition(): array
    {
        $gender = rand(1,3); //1 Femenino, 2 Masculino, 3: Otro
        $mod_create_date = strtotime(Carbon::now()->format('Y-m-d H:m:s')."- ".rand(1,60)." days");
        $created_at = date("Y-m-d H:m:s",$mod_create_date);
        $name = $this->faker->name();

        return [
            'gender_id' => $gender,
            'birthday' => $this->faker->date,
            'slug' => Str::slug($name),
            'about_us' => $this->faker->sentence,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
