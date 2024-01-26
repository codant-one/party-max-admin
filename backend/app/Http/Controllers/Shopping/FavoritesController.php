<?php

namespace App\Http\Controllers\Shopping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductLike;

class FavoritesController extends Controller
{


    public function show_favorites($id)
    {
        try 
        {
            $favorites = ProductLike::with(['user','product'])
                                      ->where('user_id',$id)
                                      ->get()
                                      ->groupBy('user_id') // Agrupa por user
                                      ->map(function ($group) {
                                        // La función de mapeo para agregar detalles del producto y cantidad a cada elemento del grupo
                                        return 
                                            
                                             $group->map(function ($item) {

                                                $producto = $item->product;
                                                return $producto;
                                            })->all();
                                       
                                    })
                                    ->values()
                                    ->all();
 

            return response()->json([
                'success' => true,
                'data' => [ 
                    'favorites' => $favorites[0]
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

    public function add_favorite(Request $request)
    {

        try 
        {
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


    public function delete_favorite(Request $request)
    {
        try 
        {
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
}
