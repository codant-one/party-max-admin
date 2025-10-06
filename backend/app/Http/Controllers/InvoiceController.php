<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\OrderDetail;
use App\Models\User;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver facturas|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear facturas|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar facturas|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar facturas|administrador')->only(['delete']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? ($request->limit === 'Todos' ? -1 : $request->limit) : 10;
        
            $query = OrderDetail::applyFilters(
                            $request->only([
                                'search',
                                'orderByField',
                                'orderBy'
                            ])
                        )
                        ->withTrashed();
            
            $count = $query->count();
            $totalSum = $request->isSales === '1' ? number_format($query->sum('sales_total'), 2) : 0;
            $invoices = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);
            

            return response()->json([
                'success' => true,
                'data' => [
                    'invoices' => $invoices,
                    'invoicesTotalCount' => $count
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
     * Display a listing of the resource.
     */
    public function pending(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? ($request->limit === 'Todos' ? -1 : $request->limit) : 10;
        
            $query = User::with([
                'supplier.document.type',
                'products.colors.orders' => fn($q) =>
                    $q->where('is_invoice', 0)
                      ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                      ->whereHas('product_color.product', fn($pq) => $pq->where('state_id', 3)),
                'services.orderDetails' => fn($q) =>
                    $q->where('is_invoice', 0)
                      ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                      ->whereHas('service', fn($sq) => $sq->where('state_id', 3)),
            ])
            ->whereHas('roles', fn($q) => $q->where('name', 'Proveedor'))
            ->applyFilters(
                $request->only([
                    'search',
                    'orderByField',
                    'orderBy',
                    'invoices',
                    'user_id'
                ]))
            ->withCount([
                'products as products_count' => fn($q) =>
                    $q->where('state_id', 3)
                      ->whereHas('colors.orders', fn($q2) =>
                        $q2->where('is_invoice', 0)
                           ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                    ),
                'services as services_count' => fn($q) =>
                    $q->where('state_id', 3)
                      ->whereHas('orderDetails', fn($q2) =>
                        $q2->where('is_invoice', 0)
                           ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                    ),
            ])
            ->withSum(['productOrderDetails as products_total' => fn($q) =>
                $q->where('is_invoice', 0)
                ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                ->whereHas('product_color.product', fn($pq) => $pq->where('state_id', 3))
            ], 'total')
            ->withSum(['serviceOrderDetails as services_total' => fn($q) =>
                $q->where('is_invoice', 0)
                ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                ->whereHas('service', fn($sq) => $sq->where('state_id', 3))
            ], 'total')
            ->withTrashed()
            ->havingRaw('products_count > 0 OR services_count > 0');               
            
            $count = $query->count();
            $invoices = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'invoices' => $invoices,
                    'invoicesTotalCount' => $count
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
    public function store(Request $request): JsonResponse
    {
        try {

           //

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

            //

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        try {

            //

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request): JsonResponse
    {
        try {

           //

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

    public function invoicesByUser($id) : JsonResponse
    {
        $user = 
            User::where('id', $id)
                ->whereHas('roles', fn($q) => $q->where('name', 'Proveedor'))
                ->withTrashed()
                ->firstOrFail();

        // Productos filtrados
        $products = 
            $user->products()
                ->where('state_id', 3)
                ->whereHas('colors.orders', fn($q) =>
                    $q->where('is_invoice', 0)
                    ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                )
                ->with(['colors.orders' => fn($q) =>
                    $q->where('is_invoice', 0)
                    ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                ])
                ->get();

        // Servicios filtrados
        $services = 
            $user->services()
                ->where('state_id', 3)
                ->whereHas('orderDetails', fn($q) =>
                    $q->where('is_invoice', 0)
                    ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                )
                ->with(['orderDetails' => fn($q) =>
                    $q->where('is_invoice', 0)
                    ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                ])
                ->get();


        return response()->json([
            'user' => $user,
            'products' => $products,
            'services' => $services
        ]);
    }

}
