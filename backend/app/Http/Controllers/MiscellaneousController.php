<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use App\Models\Category;
use App\Models\Product;

class MiscellaneousController extends Controller
{
    public function categories($slug): JsonResponse
    {
        try {

            $category = Category::with(['banner1', 'banner2', 'banner3', 'banner4'])
                                ->where('slug', $slug)
                                ->first();
    
            $products = Product::with(['user'])
                               ->join('product_categories', 'products.id', '=', 'product_categories.product_image_id')
                               ->where('product_categories.category_id', $category->id)
                               ->orderBy('products.created_at', 'desc')
                               ->limit(5)
                               ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'category' => $category,
                    'products' => $products
                ]
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
