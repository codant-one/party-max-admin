<?php

namespace App\Http\Controllers;

use App\Http\Requests\CakeTypeRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\CakeType;

class CakeTypeController extends Controller
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

            $query = CakeType::applyFilters(
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

            $cakeTypes = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'cakeTypes' => $cakeTypes,
                    'cakeTypesTotalCount' => $count
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
    public function store(CakeTypeRequest $request): JsonResponse
    {
        try {

            $cakeType = CakeType::createCakeType($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'cakeType' => CakeType::find($cakeType->id)
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

            $cakeType = CakeType::find($id);

            if (!$cakeType)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Tipo de torta no encontrado'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'cakeType' => $cakeType
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
    public function update(CakeTypeRequest $request, $id): JsonResponse
    {
        try {

            $cakeType = CakeType::find($id);
        
            if (!$cakeType)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Tipo de torta no encontrado'
                ], 404);

            $cakeType = $cakeType->updateCakeType($request, $cakeType);

            return response()->json([
                'success' => true,
                'data' => [
                    'cakeType' => CakeType::find($cakeType->id)
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

            $cakeType = CakeType::find($id);
        
            if (!$cakeType)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Tipo de torta no encontrado'
                ], 404);

            $cakeType->deleteCakeType($id);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'cakeType' => $cakeType
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
