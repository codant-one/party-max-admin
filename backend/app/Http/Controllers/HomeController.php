<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductTag;
use App\Models\ProductLike;
use App\Models\HomeImage;

class HomeController extends Controller
{
    public function home(): JsonResponse
    {
        try {

            $data = [];
    
            $data['parentCategories'] = Category::select(['id', 'name', 'slug', 'icon_subcategory'])->where('category_type_id', 1)->whereNull('category_id')->get()->toArray();

            foreach($data['parentCategories'] as $key => $parentCategory) {
                
                $data['parentCategories'][$key]['children'] = Category::select(['id', 'name', 'slug', 'icon_subcategory'])->where('category_id', $parentCategory['id'])->get();

                foreach($data['parentCategories'][$key]['children'] as $k => $son) {
                    $data['parentCategories'][$key]['children'][$k]['grandchildren'] = Category::select(['id', 'name', 'slug', 'icon_subcategory'])->where('category_id', $son['id'])->get();
                }
            }

            $data['parentServices'] = Category::select(['id', 'name', 'slug', 'icon_subcategory'])->where('category_type_id', 2)->whereNull('category_id')->get()->toArray();

            foreach($data['parentServices'] as $key => $parentCategory) {
                
                $data['parentServices'][$key]['children'] = Category::select(['id', 'name', 'slug', 'icon_subcategory'])->where('category_id', $parentCategory['id'])->get();

                foreach($data['parentServices'][$key]['children'] as $k => $son) {
                    $data['parentServices'][$key]['children'][$k]['grandchildren'] = Category::select(['id', 'name', 'slug', 'icon_subcategory'])->where('category_id', $son['id'])->get();
                }
            }

            $recommendations = Product::with(['user.userDetail', 'user.supplier'])
                                      ->where('favourite', true)
                                      ->orderBy('created_at', 'desc')
                                      ->limit(5)
                                      ->get();

            // Validate if the user is authenticated
            if (auth()->check()) {

                $lastLike = ProductLike::where('user_id', auth()->user()->id)
                            ->orderBy('created_at', 'desc')
                            ->first();

                //Validate if the last Like exists
                if ($lastLike) {
                    $productTag = ProductTag::where('product_id', $lastLike->product_id)->first();
                    
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

                        $data['recommendations'] = $recommendations;
                    } else {
                        $data['recommendations'] = $recommendations;
                    }
                } else 
                    $data['recommendations'] = $recommendations;

            } else {
                $data['recommendations'] = $recommendations;
            }
            
            $data['mostSold'] = [];
        
            // Get the 10 most recent products
            $latestProducts = Product::with(['user.userDetail', 'user.supplier'])
                                     ->orderBy('id', 'desc')
                                     ->limit(10)
                                     ->get();
        
            $bestSellers = Product::with(['user.userDetail', 'user.supplier'])
                                  ->bestSellers()
                                  ->orderBy('count', 'desc')
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


    public function index(Request $request): JsonResponse
    {
        
        try {

            $limit = $request->has('limit') ? $request->limit : 10;

            $query = HomeImage::query(); 

            $homeimages = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            $count = $query->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'homeimages' => $homeimages,
                    'homeimagesTotalCount' => $count
                ]
            ]);
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
              ], 500);
        }
    }

    public function store(Request $request)
    {
        try {

            $home_image = HomeImage::createHomeImage($request);

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'home_images/';

                $file_data = uploadFile($image, $path);

                $home_image->image = $file_data['filePath'];
                $home_image->update();
            } 

            return response()->json([
                'success' => true,
                'data' => [
                    'home_image' => HomeImage::find($home_image->id)
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
