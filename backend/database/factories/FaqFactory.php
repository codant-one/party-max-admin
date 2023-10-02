<?php

namespace Database\Factories;

use Str;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class FaqFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Faq::class;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => '¿'.$this->faker->sentence.'?',
            'description' => $this->faker->paragraph
        ];
        
    }
}
