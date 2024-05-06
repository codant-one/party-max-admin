<?php

namespace App\Http\Controllers;

use App\Http\Requests\FaqCategoryRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\FaqCategory;

class FaqCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver categorías-faqs|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear categorías-faqs|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar categorías-faqs|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar categorías-faqs|administrador')->only(['delete']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;
        
            $query = FaqCategory::
                applyFilters(
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

            $categories = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => $categories,
                    'categoriesTotalCount' => $count
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
    public function store(FaqCategoryRequest $request): JsonResponse
    {
        try {

            $category = FaqCategory::createCategory($request);

            return response()->json([
                'success' => true,
                'data' => [
                    'category' => FaqCategory::find($category->id)
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

            $category = FaqCategory::find($id);

            if (!$category)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Categoría no encontrado'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'category' => $category
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
    public function update(FaqCategoryRequest $request, $id): JsonResponse
    {
        try {

            $category = FaqCategory::find($id);

            if (!$category)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Categoría no encontrado'
                ], 404);

            $category = $category->updateCategory($request, $category);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'category' => FaqCategory::find($category->id)
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
                
            $category = FaqCategory::find($request->ids);

            if (!$category)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Categoría no encontrado'
                ], 404);

            FaqCategory::deleteCategories($request->ids);

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

    public function faqs(): JsonResponse
    {
        try {

            $categories = 
                FaqCategory::with(['faqs'=> 
                        function ($query) {
                            $query->orderBy('order_id', 'asc');
                        }
                    ])->get();
        
            return response()->json([
                'success' => true,
                'data' => [ 
                    'categories' => $categories
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
