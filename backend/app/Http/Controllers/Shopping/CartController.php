<?php

namespace App\Http\Controllers\Shopping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

use App\Models\ProductColor;
use App\Models\Service;
use App\Models\Flavor;
use App\Models\Filling;

class CartController extends Controller
{

    public function index(Request $request): JsonResponse
    {
       
        try {

            $cart = [];

            if($request->type === '0') {
                $productColorIds = explode(',', $request->product_color_id);
                $quantities = explode(',', $request->quantity);
                $wholesale = $request->wholesale;

                $cart = ProductColor::with([
                            'color',
                            'product.user.userDetail',
                            'product.user.supplier',
                            'images'
                        ])
                        ->whereIn('id', $productColorIds)
                        ->get()
                        ->map(function ($item) use ($productColorIds, $quantities, $wholesale) {
                            $index = array_search($item->id, $productColorIds);
                            $quantity = $quantities[$index] ?? 1;

                            $product = $item;
                            $product->product = $item->product;
                            $product->user = $item->product->user;
                            $product->userDetail = $item->product->user->userDetail;
                            $product->supplier = $item->product->user->supplier;
                            $product->color = $item->color;
                            $product->images = $item->images;
                            $product->product_color_id = $item->id;
                            $product->quantity = $quantity;
                            $product->wholesale = (int)$wholesale;
                            return $product;
                        })
                        ->values()
                        ->all();
            } else if($request->type === '1') {
                $servicesIds = explode(',', $request->service_id);
                $cakeSizeIds = explode(',', $request->cake_size_id);
                $quantities = explode(',', $request->quantity);
                $dateIds = explode(',', $request->date);
                $flavorIds = explode(',', $request->flavor_id);
                $fillingIds = explode(',', $request->filling_id);
                $orderFileIds = explode(',', $request->order_file_id);

                $cart = Service::with([
                            'images',
                            'user.userDetail',
                            'user.supplier',
                            'cupcakes.cake_size.cake_type'
                        ])
                        ->whereIn('id', $servicesIds)
                        ->get()
                        ->map(function ($item) use ($cakeSizeIds, $dateIds, $servicesIds, $quantities, $flavorIds, $fillingIds, $orderFileIds) {
                            $index = array_search($item->id, $servicesIds);
                            $cake_size_id = $cakeSizeIds[$index] === 0 ? null : $cakeSizeIds[$index];
                            $quantity = $quantities[$index] ?? 1;
                            $date = $dateIds[$index];
                            $flavor_id = $flavorIds[$index] ?? 0;
                            $filling_id = $fillingIds[$index] ?? 0;
                            $order_file_id = $orderFileIds[$index] ?? 0;

                            $flavor = ($flavor_id > 0) ? Flavor::find($flavor_id) : null;
                            $filling = ($filling_id > 0) ? Filling::find($filling_id) : null;

                            $product = $item;
                            $product->user = $item->user;
                            $product->userDetail = $item->user->userDetail;
                            $product->supplier = $item->user->supplier;
                            $product->images = $item->images;
                            $product->cake_size_id = (int)$cake_size_id;
                            $product->date = $date;
                            $product->quantity = $quantity;
                            $product->flavor = ($flavor_id > 0) ? $flavor: null;
                            $product->filling = ($filling_id > 0) ? $filling : null;
                            $product->order_file_id = (int)$order_file_id;
                            return $product;
                        })
                        ->values()
                        ->all();
            }

            return response()->json([
                'success' => true,
                'data' => [ 
                    'cart' => $cart,
                    'type' => (int)$request->type
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
