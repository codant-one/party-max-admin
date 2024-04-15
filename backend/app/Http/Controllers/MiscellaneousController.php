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
use App\Models\ProductLike;
use App\Models\ProductTag;
use App\Models\Color;

class MiscellaneousController extends Controller
{
    public function categories($slug): JsonResponse
    {
        try {

            $category = Category::with(['banner1', 'banner2', 'banner3', 'banner4','children'])
                                ->where('slug', $slug)
                                ->first();
    
            $products = Product::with(['user.userDetail', 'user.supplier', 'colors.categories'])
                                ->whereHas('colors.categories', function($query) use ($category) {
                                    $query->where('category_id', $category->id);
                                })
                               ->orderBy('created_at', 'desc')
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

    public function products(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 12;

            $query = Product::with(['user.userDetail', 'user.supplier', 'order'])
                            ->where('state_id', 3)
                            ->applyFilters(
                                $request->only([
                                    'searchPublic',
                                    'orderByField',
                                    'orderBy',
                                    'category',
                                    'subcategory',
                                    'colorId',
                                    'min',
                                    'max',
                                    'wholesalers',
                                    'sortBy',
                                    'rating'
                                ])
                            );

            $count = $query->count();
                           
            $products = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'colors' => Color::where('name', '<>', 'Ninguno')->get(),
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
                                'user.userDetail', 
                                'user.supplier',
                                'brand', 
                                'colors.color', 
                                'colors.images',
                                'colors.categories.category', 
                                'tags.tag',
                                'detail'
                              ])
                              ->where('slug', $slug)
                              ->first();

            $recommendations = 
                Product::with(['user.userDetail', 'user.supplier'])
                       ->where('favourite', true)
                       ->orderBy('created_at', 'desc')
                       ->limit(5)
                       ->get();

            // Validate if the user is authenticated
            if (auth()->check()) {

                $productTag = ProductTag::where('product_id', $product->id)->first();
                    
                if ($productTag) {
                    $tag_id = $productTag->tag_id;

                    $recommendations = 
                            Product::with(['user.userDetail', 'user.supplier', 'tags'])
                                   ->whereHas('tags', function($query) use ($tag_id) {
                                        $query->where('tag_id', $tag_id);
                                   })
                                   ->orderBy('created_at', 'desc')
                                   ->limit(5)
                                   ->get();
                }

            }

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => $product,
                    'recommendations' => $recommendations,
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

    public function popularsBlogs(Request $request): JsonResponse
    {
        try {

            $tag = $request->has('tag') ? $request->tag : false;
            $search = $request->has('search') ? $request->search : false;
            $query = Blog::with(['user']);

            if($tag) {
                $query = $query->whereHas('tags', function ($q) use ($tag) {
                            $q->whereHas('tag', function ($q) use ($tag) {
                                $q->where(function ($query) use ($tag) {
                                    $query->where('slug', $tag );
                                });
                            });
                        });
            } else 
                $query = $query->where('is_popular_blog', 1);

            if($search)
                $query = $query->where('title', 'LIKE', '%' . $search . '%')
                               ->orWhere('description', 'LIKE', '%' . $search . '%');

            $blogs = $query->get();
                
            $tags = Tag::withCount('blogTags')->where('tag_type_id', 2)->get();

            $latestBlogs = Blog::orderBy('created_at', 'desc')
                               ->limit(5)          
                               ->get();
        
            return response()->json([
                'success' => true,
                'data' => [ 
                    'blogs' => $blogs,
                    'tags' => $tags,
                    'latestBlogs' => $latestBlogs
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

    public function blogDetail($slug): JsonResponse
    {
        try {

            $blog = Blog::with(['category', 'user', 'tags.tag'])
                        ->where('slug',$slug)
                        ->first();

            $tags = Tag::withCount('blogTags')->where('tag_type_id', 2)->get();

            $latestBlogs = Blog::orderBy('created_at', 'desc')
                               ->limit(5)          
                               ->get();
        
            return response()->json([
                'success' => true,
                'data' => [ 
                    'blog' => $blog,
                    'tags' => $tags,
                    'latestBlogs' => $latestBlogs
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
