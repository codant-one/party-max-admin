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
     * Display a listing of the resource.
     */
    public function bypay(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? ($request->limit === 'Todos' ? -1 : $request->limit) : 10;
        
            $query = Invoice::with([
                'user.supplier.document.type',
                'orders'
            ])
            ->whereHas('user.roles', fn($q) => $q->where('name', 'Proveedor'))
            ->whereNull('payment_date')
            ->applyFilters(
                $request->only([
                    'search',
                    'orderByField',
                    'orderBy',
                    'invoices',
                    'user_id'
                ]))
                ->withCount([
                    'orders as products_invoice_bypay_count' => fn($q) =>
                        $q->whereNull('payment_date')
                            ->whereNotNull('product_color_id'),
                    'orders as services_invoice_bypay_count' => fn($q) =>
                        $q->whereNull('payment_date')
                            ->whereNotNull('service_id')
                ])
                ->withSum(['orders as products_bypay_total' => fn($q) =>
                    $q->whereNull('payment_date')
                        ->whereNotNull('product_color_id')
                ], 'total')
                ->withSum(['orders as services_bypay_total' => fn($q) =>
                    $q->whereNull('payment_date')
                        ->whereNotNull('service_id')
                ], 'total')
                ->addSelect(['unpaid_invoices_count' => Invoice::selectRaw('COUNT(*)')
                    ->whereColumn('id', 'invoices.id')
                    ->whereNull('payment_date')
                ])
            ->withTrashed()
            ->havingRaw('unpaid_invoices_count > 0');               
            
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
     * Display a listing of the resource.
     */
    public function paid(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? ($request->limit === 'Todos' ? -1 : $request->limit) : 10;
        
            $query = Invoice::with([
                    'user.supplier.document.type',
                    'orders'
                ])
                ->whereHas('user.roles', fn($q) => $q->where('name', 'Proveedor'))
                ->whereNotNull('payment_date')
                ->applyFilters(
                    $request->only([
                        'search',
                        'orderByField',
                        'orderBy',
                        'invoices',
                        'user_id'
                    ]))
                    ->withCount([
                        'orders as products_invoice_paid_count' => fn($q) =>
                            $q->whereNotNull('payment_date')
                                ->whereNotNull('product_color_id'),
                        'orders as services_invoice_paid_count' => fn($q) =>
                            $q->whereNotNull('payment_date')
                                ->whereNotNull('service_id')
                    ])
                    ->withSum(['orders as products_paid_total' => fn($q) =>
                        $q->whereNotNull('payment_date')
                            ->whereNotNull('product_color_id')
                    ], 'total')
                    ->withSum(['orders as services_paid_total' => fn($q) =>
                        $q->whereNotNull('payment_date')
                            ->whereNotNull('service_id')
                    ], 'total')
                // Invoices counts by payment state
                ->addSelect(['paid_invoices_count' => Invoice::selectRaw('COUNT(*)')
                    ->whereColumn('id', 'invoices.id')
                    ->whereNotNull('payment_date')
                ])
            ->withTrashed()
            ->havingRaw('paid_invoices_count > 0');               
            
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
     * Display a listing of the resource from All Invoices Status.
     */
    public function all(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? ($request->limit === 'Todos' ? -1 : $request->limit) : 10;
        
            $query = User::with([
                'supplier.document.type',
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
            // Pending (is_invoice = 0) counts
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
                'productOrderDetails as products_invoice_bypay_count' => fn($q) =>
                    $q->where('is_invoice', 1)
                        ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                        ->whereHas('invoice', fn($pq) => $pq->whereNull('payment_date')),
                'serviceOrderDetails as services_invoice_bypay_count' => fn($q) =>
                    $q->where('is_invoice', 1)
                        ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                        ->whereHas('invoice', fn($pq) => $pq->whereNull('payment_date')),
                'productOrderDetails as products_invoice_paid_count' => fn($q) =>
                    $q->where('is_invoice', 1)
                        ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                        ->whereHas('invoice', fn($pq) => $pq->whereNotNull('payment_date')),
                'serviceOrderDetails as services_invoice_paid_count' => fn($q) =>
                    $q->where('is_invoice', 1)
                        ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                        ->whereHas('invoice', fn($pq) => $pq->whereNotNull('payment_date')),
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
            ->withSum(['productOrderDetails as products_bypay_total' => fn($q) =>
                $q->where('is_invoice', 1)
                ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                ->whereHas('product_color.product', fn($pq) => $pq->where('state_id', 3))
                ->whereHas('invoice', fn($pq) => $pq->whereNull('payment_date'))
            ], 'total')
            ->withSum(['serviceOrderDetails as services_bypay_total' => fn($q) =>
                $q->where('is_invoice', 1)
                ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                ->whereHas('service', fn($sq) => $sq->where('state_id', 3))
                ->whereHas('invoice', fn($pq) => $pq->whereNull('payment_date'))
            ], 'total')
            ->withSum(['productOrderDetails as products_paid_total' => fn($q) =>
                $q->where('is_invoice', 1)
                ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                ->whereHas('product_color.product', fn($pq) => $pq->where('state_id', 3))
                ->whereHas('invoice', fn($pq) => $pq->whereNotNull('payment_date'))
            ], 'total')
            ->withSum(['serviceOrderDetails as services_paid_total' => fn($q) =>
                $q->where('is_invoice', 1)
                ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                ->whereHas('service', fn($sq) => $sq->where('state_id', 3))
                ->whereHas('invoice', fn($pq) => $pq->whereNotNull('payment_date'))
            ], 'total')
            // Invoices counts by payment state
            ->addSelect(['unpaid_invoices_count' => Invoice::selectRaw('COUNT(*)')
                ->whereColumn('user_id', 'users.id')
                ->whereNull('payment_date')
            ])
            ->addSelect(['paid_invoices_count' => Invoice::selectRaw('COUNT(*)')
                ->whereColumn('user_id', 'users.id')
                ->whereNotNull('payment_date')
            ])
            ->withTrashed()
            ->havingRaw('products_count > 0 OR services_count > 0 OR unpaid_invoices_count > 0 OR paid_invoices_count > 0');
            
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
                // 'payment_type' => 'required|string',
                // 'reference' => 'required|string',
                'total' => 'required|numeric|min:0',
                'payments' => 'required|array|min:1',
                'note' => 'nullable|string',
                // 'image' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048'
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
                'payment_date' => null,
                'reference' => $request->reference,
                'image' => null,
                'note' => $request->note
            ]);

            // if ($request->hasFile('image')) {
            //     $image = $request->file('image');

            //     $path = 'invoices';

            //     $file_data = uploadFile($image, $path);

            //     $invoice->image = $file_data['filePath'];
            //     $invoice->update();
            // }

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
    public function update(Request $request, $id): JsonResponse
    {
        try {

            $invoice = Invoice::find($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'invoice' => $invoice
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
     * Update the specified resource in storage.
     */
    public function updatePayment(Request $request, $id): JsonResponse
    {
        try {

            // Validar datos requeridos
            $request->validate([
                'payment_type' => 'required|string',
                'reference' => 'required|string',
                'image' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048'
            ]);

            $invoice = Invoice::find($id);

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encuentra la factura'
                ], 404);
            }

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'invoices';

                $file_data = uploadFile($image, $path);

                $invoice->image = $file_data['filePath'];
            }

            $invoice->payment_date = now()->toDateString();

            $invoice->fill($request->except(['id', 'image', 'payment_date']));
            $invoice->update();

            return response()->json([
                'success' => true,
                'data' => [
                    'invoice' => $invoice
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

    public function invoicesByUser($id, $type, $invoice_id) : JsonResponse
    {
        $is_invoice = 0;
        $is_payment = false;

        switch($type) {
            case 0:
                $is_invoice = 0;
                $is_payment = false;
                $invoice_id = null;
                break;
            case 1:
                $is_invoice = 1;
                $is_payment = false;
                break;
            case 2:
                $is_invoice = 1;
                $is_payment = true;
                break;
            default:
                $is_invoice = 0;
                $is_payment = false;
                $invoice_id = null;
                break;
        }

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
                    $q->where('is_invoice', $is_invoice)
                    ->where('invoice_id', $invoice_id)
                    ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                )
                ->with(['colors.orders' => fn($q) =>
                    $q->where('is_invoice', $is_invoice)
                    ->where('invoice_id', $invoice_id)
                    ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                ])
                ->get();

        // Servicios filtrados
        $services = 
            $user->services()
                ->where('state_id', 3)
                ->whereHas('orderDetails', fn($q) =>
                    $q->where('is_invoice', $is_invoice)
                    ->where('invoice_id', $invoice_id)
                    ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                )
                ->with(['orderDetails' => fn($q) =>
                    $q->where('is_invoice', $is_invoice)
                    ->where('invoice_id', $invoice_id)
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

        $invoice = null;
        if ($is_invoice === 1){
            $invoice = Invoice::where('id', $invoice_id)->first();
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
                'invoice' => $invoice,
                'last_record' => $nextInvoiceId //Ultimo numero de factura de ese usuario
            ]
        ]);
    }

}
