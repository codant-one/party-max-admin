<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCategory;


class HomeController extends Controller
{
    public function home(): JsonResponse
    {
        try {

            $data = [];
            $errors = [];
    
            // Obtener las categorías padres
            $parentcategories = Category::whereNull('category_id')->get();
    
            if ($parentcategories->isEmpty()) {
                $errors[] = [
                    'feedback' => 'not_found',
                    'message' => 'Categorías Padre no encontradas'
                ];
            } else {
                $data['parentCategory'] = $parentcategories;
            }
    
            // Validate if the user is authenticated
            if (auth()->check()) {
                $userauth = auth()->user()->id;
                $lastLike = DB::table('product_likes')
                    ->where('user_id', $userauth)
                    ->orderBy('date', 'desc')
                    ->first();
                
                //Validate if the last Like exists
                if ($lastLike) {
                    $productLike = ProductCategory::where('product_id', $lastLike->product_id)->first();
    
                    if ($productLike) {
                        $categoryLike = $productLike->category_id;
                        $recommendations = Product::join('product_categories', 'products.id', '=', 'product_categories.product_id')
                            ->where('product_categories.category_id', $categoryLike)
                            ->orderBy('products.created_at', 'desc')
                            ->take(5)
                            ->get();
                    } else {
                        $errors[] = [
                            'feedback' => 'not_found',
                            'message' => 'Categoría del último like no encontrada'
                        ];
                    }
                } else {
                    //The last like does not exist, get the top 5 favorite products
                    $recommendations = Product::where('favourite', true)
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                }
            } else {
                //User is not authenticated, get top 5 favorite products
                $recommendations = Product::where('favourite', true)
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get(); 
            }
            
            
            if (count($recommendations) == 5) {
                    $data['recommendations'] = $recommendations;
            } else {
                    $errors[] = [
                        'feedback' => 'not_found',
                        'message' => 'No se encontraron suficientes recomendaciones'
                    ];
            }

            // Get the 10 most recent products
            $latestProducts = Product::orderBy('created_at', 'desc')
                ->take(10)
                ->get();
      
            // Verificar si hay exactamente 10 elementos en cada uno
            if (count($latestProducts) == 10) {
                $data['topProductos'] = [
                    'latestProducts' => $latestProducts
                ];
            } else {
                $errors[] = [
                    'feedback' => 'not_found',
                    'message' => 'No se encontraron suficientes productos recientes o antiguos'
                ];
            }
    
            return response()->json([
                'success' => empty($errors),
                'errors' => $errors,
                'data' => $data
            ], empty($errors) ? 200 : 404);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

    
}
