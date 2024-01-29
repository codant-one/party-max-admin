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

    public function index(Request $request): JsonResponse
    {
       
        try {

            $cart = 
                ShoppingCart::with(['color.color', 'color.product.user', 'color.images'])
                            ->where('client_id', $request->client_id)
                            ->get()
                            ->groupBy('client_id')
                            ->map(function ($group) {
                                return $group->map(function ($item) {
                                    $product = $item->color->product;
                                    $product->color = $item->color->color;
                                    $product->images = $item->color->images;
                                    $product->product_color_id = $item->product_color_id;
                                    $product->quantity = $item->quantity;
                                    return $product;
                                })->all();
                            })
                            ->values()
                            ->all();

            return response()->json([
                'success' => true,
                'data' => [ 
                    'cart' => (count($cart) === 0) ? [] : $cart[0]
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

    public function add(Request $request)
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

    public function delete(Request $request)
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

    public function deleteAll(Request $request)
    {
        try 
        {
            $cart = ShoppingCart::deleteAll($request);

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
