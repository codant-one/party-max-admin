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
                ShoppingCart::with([
                                'color.color',
                                'color.product.user.userDetail',
                                'color.product.user.supplier',
                                'color.images'
                            ])
                            ->where('client_id', $request->client_id)
                            ->get()
                            ->groupBy('client_id')
                            ->map(function ($group) {
                                return $group->map(function ($item) {
                                    $product = $item->color;
                                    $product->product = $item->color->product;
                                    $product->user = $item->color->product->user;
                                    $product->userDetail = $item->color->product->user->userDetail;
                                    $product->supplier = $item->color->product->user->supplier;
                                    $product->color = $item->color->color;
                                    $product->images = $item->color->images;
                                    $product->product_color_id = $item->product_color_id;
                                    $product->quantity = $item->quantity;
                                    $product->wholesale = $item->wholesale;
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

    public function add(Request $request): JsonResponse
    {
        try {
            $cart = ShoppingCart::addCart($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'cart' => $cart,
                    'count' => ShoppingCart::where('client_id', $request->client_id)->count()
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

    public function deleteAll(Request $request): JsonResponse
    {
        try {
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

    public function checkAvailability(Request $request): JsonResponse
    {
        try {

            $cart = 
                ShoppingCart::with(['color.product:id,stock'])
                            ->where('client_id', $request->client_id)
                            ->get(['product_color_id', 'quantity']);

            $allAvailable = $cart->every(function ($item) {
                return $item['color']['product']['stock'] >= $item['quantity'];
            });

            return response()->json([
                'success' => true,
                'data' => [ 
                    'allAvailable' => $allAvailable
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
