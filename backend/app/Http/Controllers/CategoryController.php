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
            'categories' => $categories,
            'categoriesTotalCount' => $count
        ]);
    }

    public function order(Request $request): JsonResponse
    {
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
            'categories' => $this->categories
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $category = Category::createCategory($request);

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            $path = 'categories/';

            $file_data = uploadFile($image, $path);

            $category->image = $file_data['filePath'];
            $category->update();
        } 

        return response()->json([
            'category' => Category::find($category->id),
            'success' => true
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category)
            return response()->json([
                'sucess' => false,
                'message' => 'Not found'
            ], 404);

        return response()->json([
            'success' => true,
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $category = $category->updateCategory($request, $category);

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            $path = 'categories/';

            $file_data = uploadFile($image, $path, $category->image);

            $category->image = $file_data['filePath'];
            $category->save();
        }

        return response()->json([
            'category' => Category::find($category->id),
            'success' => true
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request): JsonResponse
    {
        Category::deleteCategories($request->ids);

        return response()->json([
            'success' => true
        ]);
    }

}
