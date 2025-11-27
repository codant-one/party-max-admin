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
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Str;

use PDF;

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
                'supplier.document.type'
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
                'productOrderDetails as products_count' => fn($q) =>
                    $q->where('is_invoice', 0)
                      ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                      ->whereHas('product_color.product', fn($pq) => $pq->where('state_id', 3)),
                'serviceOrderDetails as services_count' => fn($q) =>
                    $q->where('is_invoice', 0)
                      ->whereHas('order', fn($oq) => $oq->where('payment_state_id', 4))
                      ->whereHas('service', fn($sq) => $sq->where('state_id', 3)),
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
    public function suppliers(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? ($request->limit === 'Todos' ? -1 : $request->limit) : 10;
        
            $query = Invoice::with([
                'user.supplier.document.type',
                'orders'
            ])
            ->where('user_id', auth()->id())
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
                            ->whereNotNull('service_id'),
                    'orders as products_invoice_paid_count' => fn($q) =>
                            $q->whereNotNull('payment_date')
                                ->whereNotNull('product_color_id'),
                    'orders as services_invoice_paid_count' => fn($q) =>
                            $q->whereNotNull('payment_date')
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
                ->withSum(['orders as products_paid_total' => fn($q) =>
                    $q->whereNotNull('payment_date')
                        ->whereNotNull('product_color_id')
                ], 'total')
                ->withSum(['orders as services_paid_total' => fn($q) =>
                    $q->whereNotNull('payment_date')
                        ->whereNotNull('service_id')
                ], 'total')
                ->addSelect(['unpaid_invoices_count' => Invoice::selectRaw('COUNT(*)')
                    ->whereColumn('id', 'invoices.id')
                    ->where('user_id', auth()->id())
                    ->whereNull('payment_date')
                ])
                ->addSelect(['paid_invoices_count' => Invoice::selectRaw('COUNT(*)')
                    ->whereColumn('id', 'invoices.id')
                    ->where('user_id', auth()->id())
                    ->whereNotNull('payment_date')
                ])
            ->withTrashed();               
            
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
                    'admin',
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
            $request->validate(
                [
                    'user_id' => 'required|exists:users,id',
                    'start' => 'required|date',
                    'end' => 'required|date|after:start',
                    // 'payment_type' => 'required|string',
                    // 'reference' => 'required|string',
                    'total' => 'required|numeric|min:0',
                    'totalProducts' => 'required|numeric|min:0',
                    'totalServices' => 'required|numeric|min:0',
                    'commissionProducts' => 'required|numeric|min:0',
                    'amountCommissionProducts' => 'required|numeric|min:0',
                    'commissionServices' => 'required|numeric|min:0',
                    'amountCommissionServices' => 'required|numeric|min:0',
                    'totalLessCommission' => 'required|numeric|min:0',
                    'payments' => 'required|array|min:1',
                    'note' => 'nullable|string',
                    // 'image' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048'
                ],
                [
                    'user_id.required' => 'El usuario es obligatorio.',
                    'user_id.exists' => 'El usuario seleccionado no existe.',
                    'start.required' => 'La fecha de emisión es obligatoria.',
                    'start.date' => 'La fecha de emisión no tiene un formato válido.',
                    'end.required' => 'La fecha de vencimiento es obligatoria.',
                    'end.date' => 'La fecha de vencimiento no tiene un formato válido.',
                    'end.after' => 'La fecha de vencimiento debe ser posterior a La fecha de emisión.',
                    'total.required' => 'El total es obligatorio.',
                    'total.numeric' => 'El total debe ser numérico.',
                    'total.min' => 'El total no puede ser negativo.',
                    'totalProducts.required' => 'El total de productos es obligatorio.',
                    'totalProducts.numeric' => 'El total de productos debe ser numérico.',
                    'totalProducts.min' => 'El total de productos no puede ser negativo.',
                    'totalServices.required' => 'El total de servicios es obligatorio.',
                    'totalServices.numeric' => 'El total de servicios debe ser numérico.',
                    'totalServices.min' => 'El total de servicios no puede ser negativo.',
                    'commissionProducts.required' => 'El porcentaje de comisión de productos es obligatorio.',
                    'commissionProducts.numeric' => 'El porcentaje de comisión de productos debe ser numérico.',
                    'commissionProducts.min' => 'El porcentaje de comisión de productos no puede ser negativo.',
                    'amountCommissionProducts.required' => 'El monto de comisión de productos es obligatorio.',
                    'amountCommissionProducts.numeric' => 'El monto de comisión de productos debe ser numérico.',
                    'amountCommissionProducts.min' => 'El monto de comisión de productos no puede ser negativo.',
                    'commissionServices.required' => 'El porcentaje de comisión de servicios es obligatorio.',
                    'commissionServices.numeric' => 'El porcentaje de comisión de servicios debe ser numérico.',
                    'commissionServices.min' => 'El porcentaje de comisión de servicios no puede ser negativo.',
                    'amountCommissionServices.required' => 'El monto de comisión de servicios es obligatorio.',
                    'amountCommissionServices.numeric' => 'El monto de comisión de servicios debe ser numérico.',
                    'amountCommissionServices.min' => 'El monto de comisión de servicios no puede ser negativo.',
                    'totalLessCommission.required' => 'El total menos comisión es obligatorio.',
                    'totalLessCommission.numeric' => 'El total menos comisión debe ser numérico.',
                    'totalLessCommission.min' => 'El total menos comisión no puede ser negativo.',
                    'payments.required' => 'Debe seleccionar al menos un elemento para facturar.',
                    'payments.array' => 'El campo pagos debe ser un arreglo.',
                    'payments.min' => 'Debe seleccionar al menos un elemento para facturar.',
                    'note.string' => 'La nota debe ser un texto.'
                ],
                [
                    'user_id' => 'usuario',
                    'start' => 'fecha de inicio',
                    'end' => 'fecha fin',
                    'total' => 'total',
                    'totalProducts' => 'total de productos',
                    'totalServices' => 'total de servicios',
                    'commissionProducts' => 'comisión de productos (%)',
                    'amountCommissionProducts' => 'monto de comisión de productos',
                    'commissionServices' => 'comisión de servicios (%)',
                    'amountCommissionServices' => 'monto de comisión de servicios',
                    'totalLessCommission' => 'total menos comisión',
                    'payments' => 'pagos',
                    'note' => 'nota'
                ]
            );

            // Determinar correlativo por usuario (invoice_id)
            $nextInvoiceId = (int) (\App\Models\Invoice::where('user_id', $request->user_id)->max('invoice_id') ?? 0) + 1;

            // Crear la factura
            $invoice = Invoice::create([
                'user_id' => $request->user_id,
                'admin_id' => null,
                'state_id' => 4, // Estado pendiente
                'invoice_id' => intval($nextInvoiceId),
                'start' => $request->start,
                'end' => $request->end,
                'subtotal' => $request->total,
                'discount' => '0.00',
                'total' => $request->total,
                'total_products' => $request->totalProducts,
                'total_services' => $request->totalServices,
                'products_commission_percentage' => $request->commissionProducts,
                'products_commission_amount' => $request->amountCommissionProducts,
                'services_commission_percentage' => $request->commissionServices,
                'services_commission_amount' => $request->amountCommissionServices,
                'total_amount' => $request->totalLessCommission,
                'payment_type' => $request->payment_type === 'null' ? null : $request->payment_type,
                'payment_date' => null,
                'reference' => $request->reference == 'null' ? null : $request->reference,
                'image' => null,
                'note' => $request->note === '' ? null : $request->note
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
                        'product_id' => ($orderDetail->product_color) ? $orderDetail->product_color->product_id : null,
                        'service_id' => $orderDetail->service_id,
                        'price' => $orderDetail->price,
                        'quantity' => $orderDetail->quantity,
                        'total' => $orderDetail->total
                    ]);
                }
            }

            // Generar y guardar PDF de la factura
            $invoiceFull = Invoice::with([
                'user.supplier.document.type',
                'orders.product_color.product',
                'orders.product_color.color',
                'orders.service',
                'orders.cake_size',
                'orders.flavor',
                'orders.filling'
            ])->find($invoice->id);

            $products = [];
            $services = [];

            foreach ($invoiceFull->orders as $detail) {
                if ($detail->product_color) {
                    $products[] = [
                        'product_id' => $detail->product_color->product->id,
                        'product_name' => $detail->product_color->product->name,
                        'product_image' => asset('storage/' . $detail->product_color->product->image),
                        'color' => optional($detail->product_color->color)->name,
                        'slug' => env('APP_DOMAIN').'/products/'. $detail->product_color->product->slug,
                        'quantity' => $detail->quantity,
                        'product_price' => $detail->price,
                        'product_total' => $detail->total,
                    ];
                } elseif ($detail->service) {
                    $services[] = [
                        'service_id' => $detail->service->id,
                        'service_name' => $detail->service->name,
                        'service_is_full' => $detail->service->is_full,
                        'service_image' => asset('storage/' . $detail->service->image),
                        'flavor' => optional($detail->flavor)->name,
                        'filling' => optional($detail->filling)->name,
                        'cake_size' => optional($detail->cake_size)->name,
                        'slug' => env('APP_DOMAIN').'/services/'. $detail->service->slug,
                        'quantity' => $detail->quantity,
                        'service_price' => $detail->price,
                        'service_total' => $detail->total,
                    ];
                }
            }

            $date = now()->format('YmdHis');
            $dir = storage_path('app/public/pdfs');
            if (!file_exists($dir)) {
                @mkdir($dir, 0755, true);
            }
            $filename = 'invoice-'.Str::slug((string) $invoice->id).'-'.$date.'.pdf';
            $fullPath = $dir.'/'.$filename;

            PDF::loadView('pdfs.invoice', [
                'invoice' => $invoiceFull,
                'products' => $products,
                'services' => $services
            ])->save($fullPath);

            $invoice->pdf = 'pdfs/'.$filename;
            $invoice->save();

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
            DB::beginTransaction();

            // Validar datos requeridos
            $request->validate(
                [
                    'payment_type' => 'required|string',
                    'reference' => 'required|string',
                    //'image' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048'
                ],
                [
                    'payment_type.required' => 'El tipo de pago es obligatorio.',
                    'payment_type.string' => 'El tipo de pago no es válido.',
                    'reference.required' => 'La referencia es obligatoria.',
                    'reference.string' => 'La referencia debe ser un texto.'
                ],
                [
                    'payment_type' => 'tipo de pago',
                    'reference' => 'referencia'
                ]
            );

            $invoice = Invoice::find($id);

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encuentra la factura'
                ], 404);
            }

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'invoices/';

                $file_data = uploadFile($image, $path);

                $invoice->image = $file_data['filePath'];
            }

            $invoice->payment_date = now()->toDateString();
            $invoice->admin_id = auth()->id();
            $invoice->note = $request->note === 'null' ? null : $request->note;

            $invoice->fill($request->except(['id', 'image', 'payment_date', 'note']));

            //Se borra el archivo PDF si existe
            if (!empty($invoice->pdf)) {
                deleteFile($invoice->pdf);
                $invoice->pdf = null;
            }

            $invoice->update();

            // Generar y guardar PDF de la factura
            $invoiceFull = Invoice::with([
                'user.supplier.document.type',
                'orders.product_color.product',
                'orders.product_color.color',
                'orders.service',
                'orders.cake_size',
                'orders.flavor',
                'orders.filling'
            ])->find($invoice->id);

            $products = [];
            $services = [];

            foreach ($invoiceFull->orders as $detail) {
                if ($detail->product_color) {
                    $products[] = [
                        'product_id' => $detail->product_color->product->id,
                        'product_name' => $detail->product_color->product->name,
                        'product_image' => asset('storage/' . $detail->product_color->product->image),
                        'color' => optional($detail->product_color->color)->name,
                        'slug' => env('APP_DOMAIN').'/products/'. $detail->product_color->product->slug,
                        'quantity' => $detail->quantity,
                        'product_price' => $detail->price,
                        'product_total' => $detail->total,
                    ];
                } elseif ($detail->service) {
                    $services[] = [
                        'service_id' => $detail->service->id,
                        'service_name' => $detail->service->name,
                        'service_is_full' => $detail->service->is_full,
                        'service_image' => asset('storage/' . $detail->service->image),
                        'flavor' => optional($detail->flavor)->name,
                        'filling' => optional($detail->filling)->name,
                        'cake_size' => optional($detail->cake_size)->name,
                        'slug' => env('APP_DOMAIN').'/services/'. $detail->service->slug,
                        'quantity' => $detail->quantity,
                        'service_price' => $detail->price,
                        'service_total' => $detail->total,
                    ];
                }
            }

            $date = now()->format('YmdHis');
            $dir = storage_path('app/public/pdfs');
            if (!file_exists($dir)) {
                @mkdir($dir, 0755, true);
            }
            $filename = 'invoice-'.Str::slug((string) $invoice->id).'-'.$date.'.pdf';
            $fullPath = $dir.'/'.$filename;

            PDF::loadView('pdfs.invoice', [
                'invoice' => $invoiceFull,
                'products' => $products,
                'services' => $services
            ])->save($fullPath);

            $invoice->pdf = 'pdfs/'.$filename;
            $invoice->save();

            DB::commit();

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
                        'state_id' => 6, // Estado activo para facturación
                        'sku' => $color->sku,
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
                    'state_id' => 6, // Estado activo para facturación
                    'sku' => $service->sku
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

    public function pdf($id)
    {
        try {
            $invoice = Invoice::with([
                'user.supplier.document.type',
                'orders.product_color.product',
                'orders.product_color.color',
                'orders.service',
                'orders.cake_size',
                'orders.flavor',
                'orders.filling'
            ])->findOrFail($id);

            $products = [];
            $services = [];

            foreach ($invoice->orders as $detail) {
                if ($detail->product_color) {
                    $products[] = [
                        'product_id' => $detail->product_color->product->id,
                        'product_name' => $detail->product_color->product->name,
                        'product_image' => asset('storage/' . $detail->product_color->product->image),
                        'color' => optional($detail->product_color->color)->name,
                        'slug' => env('APP_DOMAIN').'/products/'. $detail->product_color->product->slug,
                        'quantity' => $detail->quantity,
                        'product_price' => $detail->price,
                        'product_total' => $detail->total,
                    ];
                } elseif ($detail->service) {
                    $services[] = [
                        'service_id' => $detail->service->id,
                        'service_name' => $detail->service->name,
                        'service_is_full' => $detail->service->is_full,
                        'service_image' => asset('storage/' . $detail->service->image),
                        'flavor' => optional($detail->flavor)->name,
                        'filling' => optional($detail->filling)->name,
                        'cake_size' => optional($detail->cake_size)->name,
                        'slug' => env('APP_DOMAIN').'/services/'. $detail->service->slug,
                        'quantity' => $detail->quantity,
                        'service_price' => $detail->price,
                        'service_total' => $detail->total,
                    ];
                }
            }

            $pdf = PDF::loadView('pdfs.invoice', compact('invoice', 'products', 'services'));
            return $pdf->stream('invoice-'.$invoice->id.'.pdf');
        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => 'error_generating_pdf',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

}
