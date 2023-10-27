<?php

namespace Database\Factories;

use Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

use App\Models\Faq;
use App\Models\FaqCategory;

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
            'faq_category_id' => FaqCategory::InRandomOrder()->first()->id,
            'title' => '¿'.$this->faker->sentence.'?',
            'description' => $this->faker->paragraph
        ];
        
    }
}
