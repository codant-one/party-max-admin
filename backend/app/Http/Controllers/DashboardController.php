<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderFileRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Product;
use App\Models\Service;
use App\Models\Supplier;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $deliveredShipping = 
                User::withTrashed()
                    ->from('users as u')
                    ->selectRaw('COUNT(DISTINCT o.id) as order_count')
                    ->join('products as p', 'p.user_id', '=', 'u.id')
                    ->join('product_colors as pc', 'p.id', '=', 'pc.product_id')
                    ->join('order_details as od', 'od.product_color_id', '=', 'pc.id')
                    ->join('orders as o', 'od.order_id', '=', 'o.id')
                    ->where('o.shipping_state_id', 3)
                    ->where('u.id', Auth::user()->id)
                    ->groupBy('p.user_id')
                    ->first();
      
            $products = 
                Product::with(['colors.color'])
                ->withTrashed()
                ->where([
                    ['user_id', Auth::user()->id],
                    ['state_id', 3]
                ])
                ->withCount([
                    'orderDetails as total_sold' => function (Builder $query) {
                        $query->join('orders as o', 'order_details.order_id', '=', 'o.id')
                              ->where('o.payment_state_id', 4); // Filtrar solo órdenes entregadas
                    }
                ])
                ->orderBy('total_sold', 'DESC')
                ->limit(6)  // Ordenar por la cantidad vendida de mayor a menor
                ->get();

            $productsWithLessStock = 
                Product::with(['colors.color'])
                ->withTrashed()
                ->where('user_id', Auth::id())
                ->where('state_id', 3)
                ->get()
                ->map(function ($product) {
                    $color = $product->colors->sortBy('stock')->first();

                    $stock = $color->stock ?? 0;
                    $min = $product->wholesale_min ?: 1;
                    $stock_percentage = $stock <= 6 ? round(($stock / $min) * 100, 1) : 100.0;

                    $product->stock_percentage = $stock_percentage;
                    $product->lowest_color_stock = $stock;
                    $product->sku = $color->sku ?? null;

                    return $product;
                })
                ->sortBy('stock_percentage')
                ->take(6);

            $services = 
                Service::withTrashed()
                ->where([
                    ['user_id', Auth::user()->id],
                    ['state_id', 3]
                ])
                ->withCount([
                    'orderDetails as total_sold' => function (Builder $query) {
                        $query->join('orders as o', 'order_details.order_id', '=', 'o.id')
                              ->where('o.payment_state_id', 4); // Filtrar solo órdenes entregadas
                    }
                ])
                ->orderBy('total_sold', 'DESC')
                ->limit(6)  // Ordenar por la cantidad vendida de mayor a menor
                ->get();

            $productsCount = 
                Product::
                where([
                    ['user_id', Auth::user()->id],
                    ['state_id', 3]
                ])
                ->count();

            $serviceCount = 
                Service::
                where([
                    ['user_id', Auth::user()->id],
                    ['state_id', 3]
                ])
                ->count();

            $supplier = 
                Supplier::with(['account'])
                        ->sales()
                        ->services()
                        ->retailSales()
                        ->wholesaleSales()
                        ->salesNotInvoice()
                        ->servicesNotInvoice()
                        ->retailSalesNotInvoice()
                        ->wholesaleSalesNotInvoice()
                        ->salesInvoice()
                        ->retailSalesInvoice()
                        ->wholesaleSalesInvoice()
                        ->servicesInvoice()
                        ->salesInvoicePaid()
                        ->retailSalesInvoicePaid()
                        ->wholesaleSalesInvoicePaid()
                        ->servicesInvoicePaid()
                        ->salesInvoiceNotPaid()
                        ->retailSalesInvoiceNotPaid()
                        ->wholesaleSalesInvoiceNotPaid()
                        ->servicesInvoiceNotPaid()
                        ->where('user_id', Auth::user()->id)
                        ->first();
        
            return response()->json([
                'success' => true,
                'data' => [
                    'deliveredShipping' => $deliveredShipping ? $deliveredShipping['order_count'] : 0,
                    'products' => $products,
                    'stock' => $productsWithLessStock,
                    'services' => $services,
                    'productsCount' => $productsCount,
                    'serviceCount' => $serviceCount,
                    'supplier' => $supplier
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

}
