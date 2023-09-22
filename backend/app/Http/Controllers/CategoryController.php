<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\Category;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver categorías|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear categorías|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar categorías|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar categorías|administrador')->only(['delete']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->has('limit') ? $request->limit : 10;
    
        $query = Category::with(['category'])
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $category = Category::createCategory($request);

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
        $category = Category::with(['instructions'])->find($id);

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

    public function all(): JsonResponse
    {
        $categories = 
            Category::with(['instructions'])
                    ->withCount(['instructions'])
                    ->instruction()
                    ->where('category_type_id', 1)
                    ->get();

        $instructionsPopulars = 
            Instruction::with(['category'])
                  ->orderBy('is_popular_instruction', 'DESC')
                  ->orderBy('id', 'DESC')
                  ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories,
            'instructionsPopulars' => $instructionsPopulars
        ]);
    }

    public function faqs(): JsonResponse
    {
        $categories = 
            Category::with(['faqs'])
                    ->where('category_type_id', 2)
                    ->get();


        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    public function events(): JsonResponse
    {
        $users = User::pluck('id')->where('unsubscribe', false)->toArray();
        $categories = Category::select('name', 'color','icon')->where('category_type_id', 3)->get();
        $availableCalendars = [];
        $selectedCalendars = [];
        $calendarsColor = [];

        foreach($categories as $category){

            $calendarsColor[$category['name']] = $category['color'];

            $available = [];
            $available['color'] = $category['color'];
            $available['label'] = $category['name'];
            $available['icon'] = $category['icon'];

            array_push($selectedCalendars, $category['name']);
            array_push($availableCalendars, $available);
        }

        return response()->json([
            'success' => true,
            'calendarsColor' => $calendarsColor,
            'availableCalendars' => $availableCalendars,
            'selectedCalendars' => $selectedCalendars,
            'selectedUsers' => $users
        ]);
    }
}
