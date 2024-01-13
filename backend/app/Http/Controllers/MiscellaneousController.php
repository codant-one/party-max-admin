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
use App\Models\Tag;

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
            
            $latestProductColors = $category->productColors()
                                   ->with(['product', 'color'])
                                   ->limit(5)
                                   ->get();

            $latestProductsCategory = $latestProductColors->map(function ($productColor) {
                return [
                    'product' => $productColor->product,
                    'color' => $productColor->color,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [            
                    'category' => $category,
                    'products' => $products,
                    'latestProductsCategory' => $latestProductsCategory

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

    public function products(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 12;

            $products = Product::with(['user'])->get();

            $query = Product::with(['user'])
                    ->applyFilters(
                        $request->only([
                            'search',
                            'orderByField',
                            'orderBy'
                        ])
                    );

            $count = $query->applyFilters(
                        $request->only([
                            'search',
                            'orderByField',
                            'orderBy'
                        ])
                    )->count();

            $products = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $products,
                    'productsTotalCount' => $count
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
                                'colors.images',
                                'colors.categories.category', 
                                'tags.tag',
                                'detail'
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
                           ->where('is_popular_blog', 1)
                           ->get();

            $blogCategory = BlogCategory::with(['blogs'])
                           ->get();

            $blogsLatest = Blog::with(['category', 'user'])
                           ->latest('created_at') 
                           ->limit(5)           
                           ->get();

            $tagsCount = Tag::withCount('blogTags')->get();

        
            return response()->json([
                'success' => true,
                'data' => [ 
                    'blogs' => $blogs,
                    'categories' => $blogCategory,
                    'blogsLatest' => $blogsLatest,
                    'tagsCount' => $tagsCount
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
