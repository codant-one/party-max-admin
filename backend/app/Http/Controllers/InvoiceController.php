<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\OrderDetail;
use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceDetail;

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
            DB::beginTransaction();

            // Validar datos requeridos
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'start' => 'required|date',
                'end' => 'required|date|after:start',
                'payment_type' => 'required|string',
                'reference' => 'required|string',
                'total' => 'required|numeric|min:0',
                'payments' => 'required|array|min:1',
                'note' => 'nullable|string',
                'image' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048'
            ]);

            // Determinar correlativo por usuario (invoice_id)
            $nextInvoiceId = (int) (\App\Models\Invoice::where('user_id', $request->user_id)->max('invoice_id') ?? 0) + 1;

            // Crear la factura
            $invoice = Invoice::create([
                'user_id' => $request->user_id,
                'admin_id' => auth()->id(),
                'state_id' => 4, // Estado pendiente
                'invoice_id' => intval($nextInvoiceId),
                'start' => $request->start,
                'end' => $request->end,
                'subtotal' => $request->total,
                'discount' => '0.00',
                'total' => $request->total,
                'payment_type' => $request->payment_type,
                'reference' => $request->reference,
                'image' => null,
                'note' => $request->note
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'invoices';

                $file_data = uploadFile($image, $path);

                $invoice->image = $file_data['filePath'];
                $invoice->update();
            }

            // Procesar los pagos/productos/servicios
            foreach ($request->payments as $payment) {
                $paymentData = is_string($payment) ? json_decode($payment, true) : $payment;
                
                // Actualizar OrderDetail para marcarlo como facturado
                if (isset($paymentData['id']) && $paymentData['id'] > 0) {
                    $orderDetail = OrderDetail::where('id', $paymentData['id'])
                        ->update([
                            'is_invoice' => 1,
                            'invoice_id' => $invoice->id,
                            'updated_at' => now()
                        ]);
                    
                    $orderDetail = OrderDetail::with(['product_color', 'service'])->find($paymentData['id']);
                    //$orderDetail = $orderDetail->first();

                    InvoiceDetail::create([
                        'invoice_id' => $invoice->id,
                        'order_id' => $orderDetail->order_id,
                        'product_id' => $orderDetail->product_color->product_id,
                        'service_id' => $orderDetail->service_id,
                        'price' => $orderDetail->price,
                        'quantity' => $orderDetail->quantity,
                        'total' => $orderDetail->total
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Factura creada exitosamente',
                'data' => [
                    'invoice' => $invoice
                ]
            ]);

        } catch(\Illuminate\Validation\ValidationException $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'validation_error',
                'errors' => $ex->errors()
            ], 422);
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        } catch(\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'error',
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
            User::with('supplier.document.type')
                ->where('id', $id)
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


        // Procesar productos para el formato de factura
        $invoiceProducts = [];
        
        foreach ($products as $product) {
            foreach ($product->colors as $color) {
                foreach ($color->orders as $orderDetail) {
                    $invoiceProducts[] = [
                        'id' => $orderDetail->id,
                        'type' => 'product',
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'color_name' => $color->name,
                        'description' => $product->name . ' - ' . $color->name,
                        'quantity' => $orderDetail->quantity,
                        'price' => $orderDetail->price,
                        'total' => $orderDetail->total,
                        'image' => $product->image,
                        'disabled' => true,
                        'state_id' => 6 // Estado activo para facturación
                    ];
                }
            }
        }

        // Procesar servicios para el formato de factura
        foreach ($services as $service) {
            foreach ($service->orderDetails as $orderDetail) {
                $invoiceProducts[] = [
                    'id' => $orderDetail->id,
                    'type' => 'service',
                    'service_id' => $service->id,
                    'service_name' => $service->name,
                    'description' => $service->name,
                    'quantity' => $orderDetail->quantity,
                    'price' => $orderDetail->price,
                    'total' => $orderDetail->total,
                    'image' => $service->image,
                    'disabled' => true,
                    'state_id' => 6 // Estado activo para facturación
                ];
            }
        }

        // Determinar correlativo por usuario (invoice_id)
        $nextInvoiceId = (int) \App\Models\Invoice::where('user_id', $id)->max('invoice_id') ?? 0;


        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'products' => $products,
                'services' => $services,
                'payments' => $invoiceProducts,
                'last_record' => $nextInvoiceId //Ultimo numero de factura de ese usuario
            ]
        ]);
    }

}
