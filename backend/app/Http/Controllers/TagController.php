<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\Tag;

class TagController extends Controller
{

    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver tags|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear tags|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar tags|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar tags|administrador')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;
        
            $query = Tag::applyFilters(
                        $request->only([
                            'search',
                            'orderByField',
                            'orderBy'
                        ])
                    );

            $tags = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);
            
            $count = $query->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'tags' => $tags,
                    'tagsTotalCount' => $count
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
    public function store(TagRequest $request): JsonResponse
    {
        try {

            $tag = Tag::createTag($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'tag' => Tag::find($tag->id)
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

            $tag = Tag::find($id);

            if (!$tag)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Tag no encontrado'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'tags' => $tag
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

    /*
     *
     * Update the specified resource in storage.
     */
    public function update(TagRequest $request, $id): JsonResponse
    {
        try {

            $tag = Tag::find($id);
        
            if (!$tag)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Tag no encontrado'
                ], 404);

            $tag = $tag->updateTag($request, $tag);

            return response()->json([
                'success' => true,
                'data' => [
                    'tag' => Tag::find($tag->id)
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


    /*
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {

            $tag = Tag::find($id);
        
            if (!$tag)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Tag no encontrado'
                ], 404);

            $tag->deleteTag($id);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'tag' => $tag
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
