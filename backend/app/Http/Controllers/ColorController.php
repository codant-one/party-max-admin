<?php

namespace App\Http\Controllers;

use App\Http\Requests\ColorRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\Color;

class ColorController extends Controller
{
    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver atributos|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear atributos|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar atributos|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar atributos|administrador')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;

            $query = Color::applyFilters(
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
                    )
                    ->count();

            $colors = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'colors' => $colors,
                    'colorsTotalCount' => $count
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
    public function store(ColorRequest $request): JsonResponse
    {
        try {

            $color = Color::createColor($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'color' => Color::find($color->id)
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
    public function show(string $id)
    {
        try {

            $color = Color::find($id);

            if (!$color)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Color no encontrado'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'color' => $color
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
    public function update(ColorRequest $request, string $id)
    {
        try {

            $color = Color::find($id);
        
            if (!$color)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Color no encontrado'
                ], 404);

            $color = $color->updateColor($request, $color);

            return response()->json([
                'success' => true,
                'data' => [
                    'color' => Color::find($color->id)
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
    public function destroy(string $id)
    {
        try {

            $color = Color::find($id);
        
            if (!$color)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Color no encontrado'
                ], 404);

            $color->deleteColor($id);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'color' => $color
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

    public function all()
    {
        $colors = Color::all();

        return response()->json([
            'success' => true,
            'data' => $colors
        ], 200);
    }

}
