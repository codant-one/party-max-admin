<?php

namespace Database\Seeders;

use Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;

use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json_info = Storage::disk('local')->get('/json/categories.json');
        $categories_info = json_decode($json_info, true);

        foreach($categories_info as $key => $category) {
            $grandfather = '';
            $father = '';

            if(!is_null($category['category_id'])) {
                // Filtrar el array por 'name'
                $result = [];
                $category_id = $category['category_id'];
                
                $result = array_filter($categories_info, function ($element) use ($category_id) {
                    return $element['id'] === $category_id;
                });

                // Convertir el resultado nuevamente en un array indexado
                $result = array_values($result)[0];

                if(!is_null($result['category_id'])) {
                    $result2 = [];
                    $category_id = $result['category_id'];
                    $result2 = array_filter($categories_info, function ($element) use ($category_id) {
                        return $element['id'] === $category_id;
                    });
    
                    // Convertir el resultado nuevamente en un array indexado
                    $result2 = array_values($result2)[0];

                    $grandfather = Str::slug($result2['name']) . '/';
                    $father = Str::slug($result['name']) . '/';
                } else {
                    $father = Str::slug($result['name']) . '/';
                }
            }

            Category::query()->updateOrCreate([
                'id' => $category['id'],
                'category_id' => $category['category_id'],
                'name' => $category['name'],
                'slug' => $grandfather . $father . Str::slug($category['name'])
            ]);
        }
    }
    
}
