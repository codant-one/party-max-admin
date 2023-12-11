<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use App\Models\Category;
use App\Models\Product;
use App\Models\FaqCategory;
use App\Models\Blog;
use App\Models\BlogCategory;

class MiscellaneousController extends Controller
{
    public function categories($slug): JsonResponse
    {
        try {

            $category = Category::with(['banner1', 'banner2', 'banner3', 'banner4'])
                                ->where('slug', $slug)
                                ->first();
    
            $products = Product::with(['user'])
                               ->join('product_colors', 'products.id', '=', 'product_colors.product_id')
                               ->join('product_categories', 'product_colors.id', '=', 'product_categories.product_color_id')
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

    public function categoriesAll(): JsonResponse
    {
        try {

            $categories = Category::whereNull('category_id')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => $categories
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

    public function products(): JsonResponse
    {
        try {

            $products = Product::with(['user'])->get();

            return response()->json([
                'success' => true,
                'data' => [
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

    public function productDetail($slug): JsonResponse
    {
        try {
    
            $product = Product::with([
                                'user', 
                                'brand', 
                                'colors.color', 
                                'colors.categories.category', 
                                'tags.tag'
                              ])
                              ->where('slug', $slug)
                              ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => $product
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

    public function faqs(): JsonResponse
    {
        try {

            $categories = 
                FaqCategory::with(['faqs'])
                           ->get();
        
            return response()->json([
                'success' => true,
                'data' => [ 
                    'categories' => $categories
                ]
            ]);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

    public function popularsBlogs(): JsonResponse
    {
        try {

            $blogs = 
                Blog::with(['category', 'user'])
                           ->get();

            $blogCategory = BlogCategory::with(['blogs'])
                           ->get();

        
            return response()->json([
                'success' => true,
                'data' => [ 
                    'blogs' => $blogs,
                    'categories' => $blogCategory
                ]
            ]);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

    
}
