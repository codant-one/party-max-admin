<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ProductCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductCategory::class;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        //este procedimiento porque 
        //los ID de las Categorias no son continuos.
        //Algunos como el ID 123 no existe.
        $category_id = 0;
        $tries = 0;
        do{
            $category_id = rand(1, 150);
            $category = Category::find($category_id);
            if (!$category)
                $category_id = 0;
            $tries++;
        }while ($tries < 150 && $category_id==0);

        $category_id = ($category_id == 0) ? 1 : $category_id;

        return [
            'category_id' => $category_id ,
            'created_at' => now(),
            'updated_at' => now()
        ];
        
    }
}
