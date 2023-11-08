<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductLike;

class HomeController extends Controller
{
    public function home(): JsonResponse
    {
        try {

            $data = [];
    
            $data['parentCategories'] = Category::select(['id', 'name', 'slug'])->whereNull('category_id')->get();
     
            $recommendations = Product::with(['user'])
                                      ->where('favourite', true)
                                      ->orderBy('created_at', 'desc')
                                      ->limit(5)
                                      ->get();

            // Validate if the user is authenticated
            if (auth()->check()) {

                $lastLike = ProductLike::where('user_id', auth()->user()->id)
                            ->orderBy('date', 'desc')
                            ->first();

            
                //Validate if the last Like exists
                if ($lastLike) {
                    $productCategory = ProductCategory::where('product_id', $lastLike->product_id)->first();
    
                    if ($productCategory) {
                        $category_id = $productCategory->category_id;

                        $recommendations = 
                            Product::with(['user'])
                                   ->join('product_categories', 'products.id', '=', 'product_categories.product_id')
                                   ->where('product_categories.category_id', $category_id)
                                   ->orderBy('products.created_at', 'desc')
                                   ->limit(5)
                                   ->get();

                        $data['recommendations'] = $recommendations;
                    }
                } else 
                    $data['recommendations'] = $recommendations;

            } else {
               
                $data['recommendations'] = $recommendations;
            }
            
            $data['mostSold'] = [];
        
            // Get the 10 most recent products
            $latestProducts = Product::with(['user'])
                                     ->orderBy('id', 'desc')
                                     ->limit(10)
                                     ->get();
        
            $bestSellers = Product::with(['user'])
                                  ->orderBy('id', 'asc')
                                  ->limit(10)
                                  ->get();
        
            $data['mostSold']['latestProducts'] = $latestProducts;
            $data['mostSold']['bestSellers'] = $bestSellers;
    
            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

    
}
