<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\Category;

class CategoryController extends Controller
{
    protected $categories;

    public function __construct()
    {
        $this->categories = [];

        $this->middleware(PermissionMiddleware::class . ':ver categorías|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear categorías|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar categorías|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar categorías|administrador')->only(['delete']);
    }

    function traverseChildren($children, $level = 1)
    {
        $children->sortBy('id');
        
        foreach ($children as $child) {
            $data = [
                'id' => $child->id, 
                'name' => $child->name, 
                'level' => $level + 1
            ];

            array_push($this->categories, $data);
            $this->traverseChildren($child->children, $level + 1);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $limit = $request->has('limit') ? $request->limit : 10;
        
            $query = Category::with(['category.category'])
                            ->applyFilters(
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

    public function order(Request $request): JsonResponse
    {
        try {

            $query = Category::with('children')
                                ->whereNull('category_id')
                                ->orderBy('id')
                                ->get();

            foreach ($query as $category) {
                $data = [
                    'id' => $category->id, 
                    'name' => $category->name, 
                    'level' => 1
                ];

                array_push($this->categories, $data);

                $this->traverseChildren($category->children);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => $this->categories
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
    public function store(CategoryRequest $request): JsonResponse
    {
        try {

            $category = Category::createCategory($request);

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'categories/';

                $file_data = uploadFile($image, $path);

                $category->image = $file_data['filePath'];
                $category->update();
            } 

            return response()->json([
                'success' => true,
                'data' => [
                    'category' => Category::find($category->id)
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
            $category = Category::find($id);

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
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        try {

            $category = Category::find($id);

            if (!$category)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Categoría no encontrado'
                ], 404);

            $category = $category->updateCategory($request, $category);

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'categories/';

                $file_data = uploadFile($image, $path, $category->image);

                $category->image = $file_data['filePath'];
                $category->save();
            }

            return response()->json([
                'success' => true,
                'data' => [ 
                    'category' => Category::find($category->id)
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

            $category = Category::find($id);

            if (!$category)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Categoría no encontrado'
                ], 404);
                
            Category::deleteCategories($request->ids);

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

}
