<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;

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
        $limit = $request->has('limit') ? $request->limit : 10;
    
        $query = Product::applyFilters(
                                $request->only([
                                    'search',
                                    'orderByField',
                                    'orderBy'
                                ])
                            );

        $products = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);
        
        $count = $query->count();

        return response()->json([
            'products' => $products,
            'productsTotalCount' => $count
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::createProduct($request);

        return response()->json([
            'product' => Product::find($product->id),
            'success' => true
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product)
            return response()->json([
                'sucess' => false,
                'message' => 'Not found'
            ], 404);

        return response()->json([
            'success' => true,
            'product' => $product,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $product = $product->updateProduct($request, $product);

        return response()->json([
            'product' => Product::find($product->id),
            'success' => true
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request): JsonResponse
    {
        Product::deleteProducts($request->ids);

        return response()->json([
            'success' => true
        ]);
    }
}
