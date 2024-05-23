<?php

namespace App\Http\Controllers\Shopping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductLike;

class FavoriteController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 5;

            $favoritesQuery = ProductLike::with(['user', 'product'])
                             ->where('user_id', $request->user_id)
                             ->paginate($limit); 

            $favorites = $favoritesQuery->getCollection()
                            ->groupBy('user_id')
                            ->map(function ($group) {
                                return $group->map(function ($item) {
                                    $product = $item->product;
                                    return $product;
                                });
                            })
                            ->values()
                            ->all();

            $favoritesQuery->setCollection(collect($favorites));

            $count = $favoritesQuery->count();

            return response()->json([
                'success' => true,
                'data' => [ 
                    'favorites' => $favoritesQuery,
                    'favoritesTotalCount' => $count
                ]
            ], 200);

        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

    public function add(Request $request): JsonResponse
    {

        try {

            $favorite = ProductLike::addFavorite($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'favorite' => $favorite
                ]
            ], 200);

        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }

    }

    public function delete(Request $request): JsonResponse
    {
        try {

            $favorite = ProductLike::deleteFavorite($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'favorite' => $favorite
                ]
            ], 200);
            
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

    public function show(Request $request): JsonResponse
    {

        try {

            $favorite = ProductLike::where([
                ['user_id', $request->user_id],
                ['product_id', $request->product_id]]
            )->first();

            return response()->json([
                'success' => true,
                'data' => [ 
                    'favorite' => $favorite ? true : false
                ]
            ], 200);

        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }

    }
}
