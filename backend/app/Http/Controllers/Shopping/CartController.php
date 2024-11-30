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
            $types = array_filter(explode(',', $request->type), 'strlen');

            foreach ($types as $index => $type) {
                if ($type === '0') {
                    $productColorId = explode(',', $request->product_color_id)[$index] ?? null;
                    $quantity = explode(',', $request->quantity)[$index] ?? 1;
                    $wholesale = explode(',', $request->wholesale)[$index] ?? 0;

                    if (!$productColorId) {
                        continue;
                    }

                    $product = ProductColor::with([
                        'color',
                        'product.user.userDetail',
                        'product.user.supplier',
                        'images'
                    ])->find($productColorId);
            
                    $product->product = $product->product;
                    $product->user = $product->product->user;
                    $product->user_detail = $product->product->user->userDetail;
                    $product->supplier = $product->product->user->supplier;
                    $product->product_color_id = $productColorId;
                    $product->quantity = $quantity;
                    $product->wholesale = (int)$wholesale;
                    $product->type = (int)$type;

                    $cart[] = $product;

                } elseif ($type === '1') {
                    $serviceId = explode(',', $request->service_id)[$index] ?? null;
                    $cakeSizeId = explode(',', $request->cake_size_id)[$index] ?? null;
                    $quantity = explode(',', $request->quantity)[$index] ?? 1;
                    $date = explode(',', $request->date)[$index] ?? null;
                    $flavorId = explode(',', $request->flavor_id)[$index] ?? 0;
                    $fillingId = explode(',', $request->filling_id)[$index] ?? 0;
                    $orderFileId = explode(',', $request->order_file_id)[$index] ?? 0;
                    $flavor = $flavorId > 0 ? Flavor::find($flavorId) : null;
                    $filling = $fillingId > 0 ? Filling::find($fillingId) : null;

                    if (!$serviceId) {
                        continue;
                    }

                    $service = Service::with([
                        'images',
                        'user.userDetail',
                        'user.supplier',
                        'cupcakes.cake_size.cake_type'
                    ])->find($serviceId);

                    $service->user_detail = $service->user->userDetail;
                    $service->cake_size_id = (int)$cakeSizeId;
                    $service->date = $date;
                    $service->quantity = $quantity;
                    $service->flavor = $flavor;
                    $service->filling = $filling;
                    $service->order_file_id = $orderFileId;
                    $service->type = (int)$type;

                    $cart[] = $service;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'cart' => $cart
                ],
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
