<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\BlogRequest;
use App\Models\Blog;
use Spatie\Permission\Middlewares\PermissionMiddleware;

class BlogController extends Controller
{
    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver blogs|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear blogs|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar blogs|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar blogs|administrador')->only(['delete']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;

            $query = Blog::query();            

            $blogs = $query->applyFilters(
                        $request->only([
                                'search',
                                'orderByField',
                                'orderBy'
                            ])
                        )->paginateData($limit);
    
            $count = $query->applyFilters(
                        $request->only([
                            'search',
                            'orderByField',
                            'orderBy'
                        ])
                    )->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'blogs' => $blogs,
                    'blogsTotalCount' => $count
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BlogRequest $request): JsonResponse
    {
        try{
            
            $blog = Blog::createBlog($request);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
    
                $path = 'blogs/';
    
                $file_data = uploadFile($image, $path);
    
                $blog->image = $file_data['filePath'];
                $blog->update();
            } 

            return response()->json([
                'success' => true,
                'data' => [ 
                    'blog' => $blog
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
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {

            $blog = Blog::find($id);
        
            if (!$blog)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Blog no encontrado'
                ], 404);
            
            return response()->json([
                'success' => true,
                'data' => [ 
                    'blog' => $blog
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BlogRequest $request, $id): JsonResponse
    {
        try {

            $blog = Blog::find($id);
       
            if (!$blog)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Blog no encontrado'
                ], 404);

            $blog = $blog->updateBlog($request, $blog); 

            if ($request->hasFile('image')) {
                $image = $request->file('image');
    
                $path = 'blogs/';
    
                $file_data = uploadFile($image, $path, $blog->image);
    
                $blog->image = $file_data['filePath'];
                $blog->save();
            }

            return response()->json([
                'success' => true,
                'data' => [ 
                    'blog' => $blog
                ]
            ], 200);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {

            $blog = Blog::find($id);
        
            if (!$blog)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Blog no encontrado'
                ], 404);
            
            $blog->deleteBlog($id);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'blog' => $blog
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
