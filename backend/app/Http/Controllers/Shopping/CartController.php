<?php

namespace App\Http\Controllers\Shopping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Models\ShoppingCart;
use App\Models\Client;
use App\Models\Product;


class CartController extends Controller
{

    public function show($id)
    {
       
        try 
        {
            $cart = ShoppingCart::with(['client','product.user'])
                                        ->where('client_id',$id)
                                        ->get()
                                        ->groupBy('client_id') // Agrupa por client_id
                                        ->map(function ($group) {
                                            // La función de mapeo para agregar detalles del producto y cantidad a cada elemento del grupo
                                            return [
                                                'cliente' => $group->first()->client,
                                                'detalles' => $group->map(function ($item) {

                                                    $producto = $item->product;
                                                    $producto->quantity = $item->quantity;
                                                    return $producto;
                                                })->all(),
                                            ];
                                        })
                                        ->values()
                                        ->all();

            return response()->json([
                'success' => true,
                'data' => [ 
                    'cart' => $cart[0]
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

    public function add_cart(Request $request)
    {
        try 
        {
            $cart = ShoppingCart::addCart($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'cart' => $cart
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

    public function delete_cart(Request $request)
    {
        try 
        {
            $cart = ShoppingCart::deleteCart($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'cart' => $cart
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
