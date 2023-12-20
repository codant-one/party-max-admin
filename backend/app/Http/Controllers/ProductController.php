<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Requests\StatusProductRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\Product;

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

            $limit = $request->has('limit') ? $request->limit : 10;
        
            $query = Product::with([
                            'colors.categories.category', 
                            'colors.images', 
                            'detail', 
                            'user', 
                            'state',
                            'tags'
                        ])->favorites()
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
                                'category_id'
                            ])
                        )
                        ->withTrashed();

            $products = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);
            
            $count = $query->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $products,
                    'productsTotalCount' => $count
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
                'user', 
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

            $product->updateStatusProduct($request, $field, $product); 

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
}
