<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderFileRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Order;
use App\Models\ProductColor;
use App\Models\Product;
use App\Models\OrderFile;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;
        
            $query = Order::with([
                            'details', 
                            'address.type', 
                            'billing', 
                            'shipping', 
                            'payment', 
                            'client.user.userDetail'
                        ])
                        ->applyFilters(
                            $request->only([
                                'search',
                                'orderByField',
                                'orderBy',
                                'clientId',
                                'wholesale',
                                'shipping_state_id',
                                'payment_state_id',
                                'type'
                            ])
                        );

            $count = $query->count();

            $orders = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            $payments = [
                'pendingPayments' => Order::where('payment_state_id', 1)->count(),
                'canceledPayments' => Order::where('payment_state_id', 2)->count(),
                'failedPayments' => Order::where('payment_state_id', 3)->count(),
                'successPayments' => Order::where('payment_state_id', 4)->count(),
                'pendingShipping' => Order::where([['payment_state_id', 4], ['shipping_state_id', 1]])->count(),
                'outforDeliveryShipping' => Order::where([['payment_state_id', 4], ['shipping_state_id', 2]])->count(),
                'sentShipping' => Order::where([['payment_state_id', 4], ['shipping_state_id', 3]])->count(),
                'deliveredShipping' => Order::where([['payment_state_id', 4], ['shipping_state_id', 4]])->count(),
                'orderInfo' => User::productsCount()->ordersCount()->sales()->find(Auth::user()->id)
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $orders,
                    'ordersTotalCount' => $count,
                    'payments' => $payments
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
     * Store a newly created resource in storage.
     */
    public function store(OrderRequest $request): JsonResponse
    {
        try {

            $order = Order::createOrder($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'order' => Order::with(['details', 'address.type', 'billing', 'shipping', 'payment', 'client.user.userDetail'])->find($order->id)
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
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {

            $order = Order::withTrashed()->with([
                'details.product_color.product.user.userDetail', 
                'details.product_color.product.user.supplier', 
                'details.product_color.color',
                'details.product_color.images', 
                'details.service.user.userDetail',
                'details.service.user.supplier',
                'details.service.images',
                'details.service.cupcakes',
                'details.cake_size',
                'details.flavor',
                'details.filling',
                'details.order_file',
                'address.type', 
                'billing.document_type', 
                'shipping', 
                'payment', 
                'client.user.userDetail',
                'histories',
                'address_type'
            ])->find($id);

            if (!$order)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Pedido no encontrado'
                ], 404);

            $count = Order::where('client_id', $order->client_id)->count();

            return response()->json([
                'success' => true,
                'data' => [ 
                    'order' => $order,
                    'ordersTotalCount' => $count
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

    /*
     *
     * Update the specified resource in storage.
     */
    public function update(OrderRequest $request, $id): JsonResponse
    {
 
    }

    /*
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {

            $order = Order::find($id);
        
            if (!$order)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Pedido no encontrado'
                ], 404);

            $order->deleteOrder($id);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'order' => $order
                ]
            ], 200);
            
        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    
    }

    public function updatePaymentState(Request $request, $id): JsonResponse
    {
            $validate = Validator::make($request->all(), [
                'payment_state_id' => [
                    'required',
                    'integer',
                    'exists:App\Models\PaymentState,id'
                ]
            ]);
        
            if ($validate->fails()) {
                return response()->json([
                    'success' => false,
                    'feedback' => 'params_validation_failed',
                    'message' => $validate->errors()
                ], 400);
            }
    
            try {
                $order = Order::find($id);

                if (!$order)
                    return response()->json([
                        'success' => false,
                        'feedback' => 'not_found',
                        'message' => 'Pedido no encontrado'
                    ], 404);
                
                $order->updatePaymentState($request, $order);
                $order = $order->load(['details', 'address.type', 'billing', 'shipping', 'payment', 'client.user.userDetail']);
    
                return response()->json([
                    'success' => true,
                    'data' => [ 
                        'order' => $order
                    ]
                ], 200);
    
    
            } catch(\Illuminate\Database\QueryException $ex) {
                return response()->json([
                  'success' => false,
                  'message' => 'database_error',
                  'exception' => $ex->getMessage()
                ], 500);
            }
    }

    public function ordersbyclient(Request $request, $id): JsonResponse
    {

        try {

            $limit = $request->has('limit') ? $request->limit : 5;

            $query = Order::with([
                                'details.product_color.product', 
                                'details.product_color.color', 
                                'details.service',
                                'details.cake_size',
                                'details.flavor',
                                'details.filling',
                                'shipping', 
                                'payment'
                            ])
                            ->where('client_id', $id)
                            ->applyFilters(
                                $request->only([
                                    'search',
                                    'orderByField',
                                    'orderBy',
                                    'clientId',
                                    'wholesale',
                                    'shipping_state_id',
                                    'payment_state_id'
                                ])
                            );

            $count = $query->count();

            $orders = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            $orderData = [];

            foreach ($orders as $order) {

                $orderInfo = [
                    'order_id' => $order->id,
                    'order_date' => $order->date,
                    'shipping' => $order->shipping,
                    'payment' => $order->payment,
                    'products' => [],
                    'services' => []
                ];
            
                foreach ($order->details as $detail) {
                    if($detail->product_color) {
                        $productInfo = [
                            'product_id' => $detail->product_color->product->id,
                            'product_name' => $detail->product_color->product->name,
                            'product_image' => $detail->product_color->product->image,
                            'color' => $detail->product_color->color->name,
                            'slug'=> $detail->product_color->product->slug,
                            'quantity' => $detail->quantity
                        ];
                
                        $orderInfo['products'][] = $productInfo;
                    } else {
                        $serviceInfo = [
                            'service_id' => $detail->service->id,
                            'service_name' => $detail->service->name,
                            'service_image' => $detail->service->image,
                            'flavor' => $detail->flavor ? $detail->flavor->name : null,
                            'filling' => $detail->filling ? $detail->filling->name : null,
                            'cake_size' => $detail->cake_size ? $detail->cake_size->name : null,
                            'slug' => $detail->service->slug,
                            'quantity' => $detail->quantity,
                        ];

                        $orderInfo['services'][] = $serviceInfo;
                    }
                }
            
                $orderData[] = $orderInfo;
            }
        
            return response()->json([
                'success' => true,
                'data' => [ 
                    'orders' => $orderData,
                    'ordersAll' => $orders,
                    'ordersTotalCount' => $count
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

    public function orderbyID($id): JsonResponse
    {
        try {

            $orders = Order::with([
                                'details.product_color.product.reviews', 
                                'details.product_color.color',
                                'details.service',
                                'details.cake_size',
                                'details.flavor',
                                'details.filling',
                                'shipping', 
                                'payment', 
                                'billing',
                                'address.province',
                                'histories'
                           ])
                           ->where('id', $id)
                           ->get();
            
            $orderData = [];

            foreach ($orders as $order) {

                $orderInfo = [
                    'order_id' => $order->id,
                    'type' => $order->type,
                    'order_date' => $order->date,
                    'subtotal' => $order->sub_total,
                    'shipping_cost' => $order->shipping_total,
                    'total' => $order->total,
                    'shipping' => $order->shipping,
                    'payment' => $order->payment,
                    'billing' => $order->billing,
                    'address' => $order->address,
                    'histories' => $order->histories,
                    'updated_at' => $order->updated_at,
                    'products' => [],
                    'services' => []
                ];
            
                foreach ($order->details as $detail) {
                   
                    if($detail->product_color) {
                        $review = 
                            $detail->product_color->product->reviews->firstWhere('client_id', $order->client_id);
                        
                        $productInfo = [
                            'product_color_id' => $detail->product_color->id,
                            'product_id' => $detail->product_color->product->id,
                            'product_name' => $detail->product_color->product->name,
                            'product_image' => $detail->product_color->product->image,
                            'color' => $detail->product_color->color->name,
                            'slug' => $detail->product_color->product->slug,
                            'quantity' => $detail->quantity,
                            'rating' => $review ? $review->rating : 0
                        ];
                
                        $orderInfo['products'][] = $productInfo;
                    } else {
                        $serviceInfo = [
                            'service_id' => $detail->service->id,
                            'service_name' => $detail->service->name,
                            'service_image' => $detail->service->image,
                            'flavor' => $detail->flavor ? $detail->flavor->name : null,
                            'filling' => $detail->filling ? $detail->filling->name : null,
                            'cake_size' => $detail->cake_size ? $detail->cake_size->name : null,
                            'slug' => $detail->service->slug,
                            'quantity' => $detail->quantity,
                            'rating' => 0
                        ];

                        $orderInfo['services'][] = $serviceInfo;
                    }
                }
            
                $orderData[] = $orderInfo;
            }
        
            return response()->json([
                'success' => true,
                'data' => [ 
                    'orders' => $orderData
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

    public function send(Request $request, $id): JsonResponse
    {
        try {

            $order = Order::find($id);
        
            if (!$order)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Pedido no encontrado'
                ], 404);

            $order->sendOrder($order, $request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'order' => $order
                ]
            ], 200);
            
        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    
    }

    public function file(OrderFileRequest $request): JsonResponse
    {
        try {

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'orders/';

                $file_data = uploadFile($image, $path);

                $order_file = new OrderFile;
                $order_file->image = $file_data['filePath'];
                $order_file->save();
            }

            return response()->json([
                'success' => true,
                'data' => [ 
                    'order_file' => $order_file
                ]
            ], 200);
            
        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    
    }

}
