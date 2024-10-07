<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Requests\StatusProductRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\Product;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\ProductList;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver productos|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear productos|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar productos|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar productos|administrador')->only(['delete']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? ($request->limit === 'Todos' ? -1 : $request->limit) : 10;
        
            $query = Product::with([
                            'colors.categories.category', 
                            'colors.images', 
                            'colors.color', 
                            'colors.orders', 
                            'detail', 
                            'user.userDetail',
                            'user.supplier',
                            'state',
                            'tags'
                        ])
                        ->sales()
                        ->favorites()
                        ->order($request->category_id)
                        ->applyFilters(
                            $request->only([
                                'search',
                                'orderByField',
                                'orderBy',
                                'favourite',
                                'archived',
                                'discarded',
                                'state_id',
                                'in_stock',
                                'type_sales',
                                'category_id',
                                'supplierId',
                                'isSales'
                            ])
                        )
                        ->withTrashed();
            
            $count = $query->count();
            
            $products = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            $data = [
                'ordersTotalCount' => Order::count(),
                'ordersClient' => Order::distinct('client_id')->count('client_id'),
                'ordersSales' => Order::sum('total'),
                'suppliersTotalCount' => Supplier::count()
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $products,
                    'productsTotalCount' => $count,
                    'data' => $data
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
    public function store(ProductRequest $request): JsonResponse
    {
        try {

            $product = Product::createProduct($request);

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'products/main/';

                $file_data = uploadFile($image, $path);

                $product->image = $file_data['filePath'];
                $product->update();
            }

            $order_id = Product::latest('order_id')->first()->order_id ?? 0;

            $product->order_id = $order_id + 1;
            $product->update();

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => Product::find($product->id)
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

            $product = Product::with([
                'colors.categories.category', 
                'colors.images', 
                'detail', 
                'user.userDetail', 
                'state',
                'tags'
            ])->find($id);

            if (!$product)
                return response()->json([
                    'sucess' => false,
                    'message' => 'Not found',
                    'message' => 'Producto no encontrado'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'product' => $product
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
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        try {

            $product = Product::with([
                'colors.categories.category', 
                'colors.images', 
                'detail', 
                'user', 
                'state',
                'tags'
            ])->find($product->id);

            if (!$product)
                return response()->json([
                    'sucess' => false,
                    'message' => 'Not found',
                    'message' => 'Producto no encontrado'
                ], 404);

            $product = $product->updateProduct($request, $product);

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'products/main/';

                $file_data = uploadFile($image, $path, $product->image);

                $product->image = $file_data['filePath'];
                $product->update();
            } 

            return response()->json([
                'success' => true,
                'data' => [ 
                    'product' => Product::find($product->id)
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

            $product = Product::find($request->ids);

            if (!$product)
                return response()->json([
                    'sucess' => false,
                    'message' => 'Not found',
                    'message' => 'Producto no encontrado'
                ], 404);

            Product::deleteProducts($request->ids);

            return response()->json([
                'success' => true
            ]);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

    public function updateStatus(StatusProductRequest $request, $id): JsonResponse
    {
        try {

            $product = Product::find($id);
        
            if (!$product)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Producto no encontrado'
                ], 404);

            $field = $request->has('favourite')
                     ? 'favourite'
                     : ($request->has('discarded') ? 'discarded' : ($request->has('archived') ? 'archived' : null));

            $product->updateStatusProduct($field, $product); 

            return response()->json([
                'success' => true,
                'data' => [ 
                    'product' => $product
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

    public function uploadImage(Request $request): JsonResponse 
    {
        try {

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'products/';

                $file_data = uploadFile($image, $path);

                return response()->json([
                    'success' => true,
                    'url' => $file_data['filePath']
                ], 200);
            }

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }

    }

    public function updateStates(Request $request, $id): JsonResponse
    {
        try {

            $product = Product::find($id);
        
            if (!$product)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Producto no encontrado'
                ], 404);

            $product->updateStatesProduct($request, $product); 

            return response()->json([
                'success' => true,
                'data' => [ 
                    'product' => $product
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

    /**
     * update the order_id.
     */
    public function updateOrder(Request $request): JsonResponse
    { 
        $countProducts = 1;

        foreach($request->all() as $productRequest){

            if($productRequest['category_id'] != '')
                ProductList::updateOrCreate(
                    [ 
                        'product_id' => $productRequest['id'],
                        'category_id' => $productRequest['category_id']
                    ],
                    [ 'order_id' => $countProducts++ ]
                );
            else 
                Product::updateOrCreate(
                    [ 
                        'id' => $productRequest['id'],
                        'name' => $productRequest['name']
                    ],
                    [ 'order_id' => $countProducts++ ]
                );
        }

        return response()->json([
            'success' => 1
        ]);
    }
}
