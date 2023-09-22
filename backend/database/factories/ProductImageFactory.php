<?php

namespace Database\Factories;

use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ProductImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductImage::class;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'color_id' => rand(1,10),
            'image' => 'products/gallery/'.$this->faker->file(public_path('images/products/gallery'), storage_path('app/public/products/gallery'), false),
        ];
        
    }
}
