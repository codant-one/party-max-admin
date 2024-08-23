<?php

namespace App\Http\Controllers;

use App\Http\Requests\CakeSizeRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\CakeSize;

class CakeSizeController extends Controller
{

    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver parámetros|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear parámetros|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar parámetros|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar parámetros|administrador')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;

            $query = CakeSize::with(['cake_type'])
                             ->applyFilters(
                                $request->only([
                                    'search',
                                    'orderByField',
                                    'orderBy',
                                    'cake_type_id'
                                ])
                            );

            $count = $query->count();

            $cakeSizes = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'cakeSizes' => $cakeSizes,
                    'cakeSizesTotalCount' => $count
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
    public function store(CakeSizeRequest $request): JsonResponse
    {
        try {

            $cakeSize = CakeSize::createCakeSize($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'cakeSize' => CakeSize::with(['cake_type'])->find($cakeSize->id)
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

            $cakeSize = CakeSize::with(['cake_type'])->find($id);

            if (!$cakeSize)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Tamaño de torta no encontrada'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'cakeSize' => $cakeSize
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
    public function update(CakeSizeRequest $request, $id): JsonResponse
    {
        try {

            $cakeSize = CakeSize::find($id);
        
            if (!$cakeSize)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Tamaño de torta no encontrado'
                ], 404);

            $cakeSize = $cakeSize->updateCakeSize($request, $cakeSize);

            return response()->json([
                'success' => true,
                'data' => [
                    'cakeSize' => CakeSize::with(['cake_type'])->find($cakeSize->id)
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

            $cakeSize = CakeSize::with(['cake_type'])->find($id);
        
            if (!$cakeSize)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Tamaño de torta no encontrada'
                ], 404);

            $cakeSize->deleteCakeSize($id);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'cakeSize' => $cakeSize
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
