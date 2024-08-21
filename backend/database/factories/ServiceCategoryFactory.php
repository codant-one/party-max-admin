<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ServiceCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServiceCategory::class;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $category = Category::where('category_type_id', 2)->first();
        $category_id = ($category) ? $category->id : 1;

        return [
            'category_id' => $category_id ,
            'created_at' => now(),
            'updated_at' => now()
        ];
        
    }
}
