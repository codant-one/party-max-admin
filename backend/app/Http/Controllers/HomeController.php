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
    
            $data['images'] = HomeImage::all();

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

            $query = HomeImage::applyFilters(
                        $request->only([
                                'search',
                                'orderByField',
                                'orderBy',
                                'is_slider'
                            ])
                    );   

            $count = $query->applyFilters(
                        $request->only([
                                'search',
                                'orderByField',
                                'orderBy',
                                'is_slider'
                            ])
                    )->count();

            $homeImages = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'homeImages' => $homeImages,
                    'homeImagesTotalCount' => $count
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

            $homeImage = HomeImage::createHomeImage($request);

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'home/';

                $file_data = uploadFile($image, $path);

                $homeImage->image = $file_data['filePath'];
                $homeImage->update();
            } 

            $order_id = HomeImage::where('is_slider', $homeImage->is_slider)
                           ->latest('order_id')
                           ->first()
                           ->order_id ?? 0;

            $homeImage->order_id = $order_id + 1;
            $homeImage->update();

            return response()->json([
                'success' => true,
                'data' => [
                    'homeImages' => HomeImage::find($homeImage->id)
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {

            $homeImage = HomeImage::find($id);

            if (!$homeImage)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Imagen no encontrado'
                ], 404);

            $homeImage = $homeImage->updateHomeImage($request, $homeImage);

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'home/';

                $file_data = uploadFile($image, $path, $homeImage->image);

                $homeImage->image = $file_data['filePath'];
                $homeImage->update();
            } 

            return response()->json([
                'success' => true,
                'data' => [ 
                    'homeImage' => HomeImage::find($homeImage->id)
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

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request): JsonResponse
    {
        try {

            $homeImage = HomeImage::find($request->ids);
        
            if (!$homeImage)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Imagen no encontrada'
                ], 404);
            
            HomeImage::deleteHomeImage($request->ids);

            return response()->json([
                'success' => true
            ]);
            
        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }
    
     /**
     * update the order_id.
     */
    public function updateOrder(Request $request): JsonResponse
    { 
        $countImages = 1;

        foreach($request->all() as $imageRequest){
            HomeImage::updateOrCreate(
                [ 
                    'id' => $imageRequest['id'],
                    'is_slider' => $imageRequest['is_slider']
                ],
                [ 'order_id' => $countImages++ ]
            );
        }

        return response()->json([
            'success' => 1
        ]);
    }
}
