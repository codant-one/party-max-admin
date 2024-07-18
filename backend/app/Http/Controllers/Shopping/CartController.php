<?php

namespace App\Http\Controllers\Shopping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

use App\Models\ProductColor;

class CartController extends Controller
{

    public function index(Request $request): JsonResponse
    {
       
        try {

            $productColorIds = explode(',', $request->product_color_id);
            $quantities = explode(',', $request->quantity);
            $sales = explode(',', $request->wholesale);

            $cart = ProductColor::with([
                        'color',
                        'product.user.userDetail',
                        'product.user.supplier',
                        'images'
                    ])
                    ->whereIn('id', $productColorIds)
                    ->get()
                    ->map(function ($item) use ($productColorIds, $quantities) {
                        $index = array_search($item->id, $productColorIds);
                        $quantity = $quantities[$index] ?? 1;
                        $wholesale = $sales[$index] ?? 0;

                        $product = $item;
                        $product->product = $item->product;
                        $product->user = $item->product->user;
                        $product->userDetail = $item->product->user->userDetail;
                        $product->supplier = $item->product->user->supplier;
                        $product->color = $item->color;
                        $product->images = $item->images;
                        $product->product_color_id = $item->id;
                        $product->quantity = $quantity;
                        $product->wholesale = $wholesale;
                        return $product;
                    })
                    ->values()
                    ->all();

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

            $productColorIds = explode(',', $request->product_color_id);
            $quantities = explode(',', $request->quantity);

            $products = ProductColor::with(['product:id,stock'])
                                    ->whereIn('id', $productColorIds)
                                    ->get();

            $cart = collect($products)->map(function ($productColor, $index) use ($quantities) {
                return [
                    'stock' => $productColor->product->stock,
                    'quantity' => intval($quantities[$index])
                ];
            });

            $allAvailable = $cart->every(function ($item) {
                return $item['stock'] >= $item['quantity'];
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
