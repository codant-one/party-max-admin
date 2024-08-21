<?php

namespace Database\Factories;

use App\Models\ServiceImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ServiceImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServiceImage::class;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'image' => 'services/gallery/' . $this->faker->file(public_path('images/services/gallery'), storage_path('app/public/services/gallery'), false),
        ];
        
    }
}
