<?php

namespace Database\Factories;

use Str;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Service::class;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'user_id' => 1,
            'brand_id' => 6,
            'state_id' => rand(3, 5),
            'name' => $name,
            'description' => $this->faker->sentence,
            'single_description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 100), 
            'sales' => 0,
            'rating' => 0,
            'sku' => rand(100000, 999999),
            'slug' => Str::slug($name),
            'image' => 'services/main/' . $this->faker->file(public_path('images/services/main'), storage_path('app/public/services/main'), false),
            'created_at' => now(),
            'updated_at' => now()
        ];
        
    }
}
