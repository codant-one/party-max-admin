<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlogCategoryRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\BlogCategory;

class BlogCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver categorías-blogs|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear categorías-blogs|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar categorías-blogs|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar categorías-blogs|administrador')->only(['delete']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;
        
            $query = BlogCategory::
                applyFilters(
                    $request->only([
                        'search',
                        'orderByField',
                        'orderBy'
                    ])
                );

            $categories = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);
            
            $count = $query->count();

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
    public function store(BlogCategoryRequest $request): JsonResponse
    {
        try {

            $category = BlogCategory::createCategory($request);

            return response()->json([
                'success' => true,
                'data' => [
                    'category' => BlogCategory::find($category->id)
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

            $category = BlogCategory::find($id);

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
    public function update(BlogCategoryRequest $request, $id): JsonResponse
    {
        try {

            $category = BlogCategory::find($id);

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
                    'category' => BlogCategory::find($category->id)
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
                
            $category = BlogCategory::find($request->ids);

            if (!$category)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Categoría no encontrado'
                ], 404);

            BlogCategory::deleteCategories($request->ids);

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

    public function blogs(): JsonResponse
    {
        try {

            $categories = 
                BlogCategory::with(['blogs'])
                           ->get();
        
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
