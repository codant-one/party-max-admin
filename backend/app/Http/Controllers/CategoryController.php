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
                'category_id' => $child->category_id,
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
                            ->withCount(['product'])
                            ->categoryTotalPrice()
                            ->applyFilters(
                                    $request->only([
                                        'search',
                                        'fathers',
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
                    'category_id' => $category->category_id,
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

            if ($request->hasFile('banner')) {
                $banner = $request->file('banner');

                $path = 'categories/';

                $file_data = uploadFile($banner, $path);

                $category->banner = $file_data['filePath'];
                $category->update();
            } 

            if ($request->hasFile('banner_2')) {
                $banner_2 = $request->file('banner_2');

                $path = 'categories/';

                $file_data = uploadFile($banner_2, $path);

                $category->banner_2 = $file_data['filePath'];
                $category->update();
            } 

            if ($request->hasFile('banner_3')) {
                $banner_3 = $request->file('banner_3');

                $path = 'categories/';

                $file_data = uploadFile($banner_3, $path);

                $category->banner_3 = $file_data['filePath'];
                $category->update();
            } 

            if ($request->hasFile('banner_4')) {
                $banner_4 = $request->file('banner_4');

                $path = 'categories/';

                $file_data = uploadFile($banner_4, $path);

                $category->banner_4 = $file_data['filePath'];
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
            
            $category = Category::with(['category.category'])->find($id);

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
    public function update(CategoryRequest $request, $id): JsonResponse
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

            if ($request->hasFile('banner')) {
                $banner = $request->file('banner');

                $path = 'categories/';

                $file_data = uploadFile($banner, $path, $category->banner);

                $category->banner = $file_data['filePath'];
                $category->banner_category_id = $request->banner_category_id;
                $category->save();
            } elseif($request->has('banner_category_id') && !empty($category->banner) ){
                $category->banner_category_id = $request->banner_category_id;
                $category->save();
            }

            if ($request->hasFile('banner_2')) {
                $banner_2 = $request->file('banner_2');

                $path = 'categories/';

                $file_data = uploadFile($banner_2, $path, $category->banner_2);

                $category->banner_2 = $file_data['filePath'];
                $category->banner2_category_id = $request->banner2_category_id;
                $category->save();
            } elseif($request->has('banner2_category_id') && !empty($category->banner_2) ){
                $category->banner2_category_id = $request->banner2_category_id;
                $category->save();
            }

            if ($request->hasFile('banner_3')) {
                $banner_3 = $request->file('banner_3');

                $path = 'categories/';

                $file_data = uploadFile($banner_3, $path, $category->banner_3);

                $category->banner_3 = $file_data['filePath'];
                $category->banner3_category_id = $request->banner3_category_id;
                $category->save();
            } elseif($request->has('banner3_category_id') && !empty($category->banner_3) ){
                $category->banner3_category_id = $request->banner3_category_id;
                $category->save();
            }

            if ($request->hasFile('banner_4')) {
                $banner_4 = $request->file('banner_4');

                $path = 'categories/';

                $file_data = uploadFile($banner_4, $path, $category->banner_4);

                $category->banner_4 = $file_data['filePath'];
                $category->banner4_category_id = $request->banner4_category_id;
                $category->save();
            } elseif($request->has('banner4_category_id') && !empty($category->banner_4) ){
                $category->banner4_category_id = $request->banner4_category_id;
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

            $category = Category::find($request->ids);

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
