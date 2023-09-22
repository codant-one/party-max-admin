<?php

namespace Database\Factories;

use App\Models\ProductDetail;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ProductDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductDetail::class;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'width' => rand(1,20),
            'height'=> rand(1,20),
            'deep' => rand(1,20),
            'created_at' => now(),
            'updated_at' => now() 
        ];
        
    }
}
