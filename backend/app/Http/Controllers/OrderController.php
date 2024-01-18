<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\Order;

class OrderController extends Controller
{
    public function __construct()
    {

        $this->middleware(PermissionMiddleware::class . ':ver ordenes|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear ordenes|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar ordenes|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar ordenes|administrador')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;
        
            $query = Order::with(['details', 'billing'])
                        ->applyFilters(
                            $request->only([
                                'search',
                                'orderByField',
                                'orderBy'
                            ])
                        );

            $count = $query->applyFilters(
                        $request->only([
                            'search',
                            'orderByField',
                            'orderBy'
                        ])
                    )->count();

            $orders = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $orders,
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
                    'order' => Order::with(['details', 'billing'])->find($order->id)
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
    public function show($id)
    {
        try {

            $order = Order::with(['details', 'billing'])->find($id);

            if (!$order)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Orden no encontrada'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'order' => $order
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
        try {

            $order = Order::find($id);
        
            if (!$order)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Orden no encontrada'
                ], 404);

            $order = $order->updateOrder($request, $order);

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => Order::with(['details', 'billing'])->find($order->id)
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
                    'message' => 'Orden no encontrada'
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
}