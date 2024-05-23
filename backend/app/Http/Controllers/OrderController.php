<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use Illuminate\Support\Facades\Validator;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductColor;
use App\Models\Product;

class OrderController extends Controller
{
    public function __construct()
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;
        
            $query = Order::with(['details', 'address.type', 'billing', 'shipping', 'payment', 'client.user.userDetail'])
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

            $payments = [
                'pendingPayments' => Order::where('payment_state_id', 1)->count(),
                'canceledPayments' => Order::where('payment_state_id', 2)->count(),
                'failedPayments' => Order::where('payment_state_id', 3)->count(),
                'successPayments' => Order::where('payment_state_id', 4)->count(),
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

            $order = Order::with([
                'details.product_color.product', 
                'details.product_color.color',
                'details.product_color.images', 
                'address.type', 
                'billing', 
                'shipping', 
                'payment', 
                'client.user.userDetail'
            ])->find($id);

            if (!$order)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Pedido no encontrada'
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
                    'message' => 'Pedido no encontrada'
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
                        'message' => 'Pedido no encontrada'
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

            $ordersPending = Order::where([['client_id', $id], ['payment_state_id', 1]])->get();
            
            foreach ($ordersPending as $item) {

                $order = Order::find($item->id);

                if($order) {
                    $order->update([
                        'payment_state_id' => 2
                    ]);  
                }
            }

            $limit = $request->has('limit') ? $request->limit : 5;

            $query = Order::with(['details.product_color.product', 'shipping', 'payment'])
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
                    'products' => []
                ];
            
                foreach ($order->details as $detail) {
                    $productInfo = [
                        'product_id' => $detail->product_color->product->id,
                        'product_name' => $detail->product_color->product->name,
                        'product_image' => $detail->product_color->product->image,
                        'slug'=> $detail->product_color->product->slug,
                        'quantity' => $detail->quantity
                    ];
            
                    $orderInfo['products'][] = $productInfo;
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

            $orders = Order::with(['details.product_color.product', 'shipping', 'payment', 'address.province'])
                           ->where('id', $id)
                           ->get();
            
            $orderData = [];

            foreach ($orders as $order) {

                $orderInfo = [
                    'order_id' => $order->id,
                    'order_date' => $order->date,
                    'subtotal' => $order->sub_total,
                    'shipping_cost' => $order->shipping_total,
                    'total' => $order->total,
                    'shipping' => $order->shipping,
                    'payment' => $order->payment,
                    'address' => $order->address,
                    'updated_at' => $order->updated_at,
                    'products' => []
                ];
            
                foreach ($order->details as $detail) {
                    $productInfo = [
                        'product_id' => $detail->product_color->product->id,
                        'product_name' => $detail->product_color->product->name,
                        'product_image' => $detail->product_color->product->image,
                        'slug' => $detail->product_color->product->slug,
                        'quantity' => $detail->quantity
                    ];
            
                    $orderInfo['products'][] = $productInfo;
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

}
