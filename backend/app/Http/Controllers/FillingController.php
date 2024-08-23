<?php

namespace App\Http\Controllers;

use App\Http\Requests\FillingRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\Filling;

class FillingController extends Controller
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

            $query = Filling::applyFilters(
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

            $fillings = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'fillings' => $fillings,
                    'fillingsTotalCount' => $count
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
    public function store(FillingRequest $request): JsonResponse
    {
        try {

            $filling = Filling::createFilling($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'filling' => Filling::find($filling->id)
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

            $filling = Filling::find($id);

            if (!$filling)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Relleno no encontrado'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'filling' => $filling
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
    public function update(FillingRequest $request, $id): JsonResponse
    {
        try {

            $filling = Filling::find($id);
        
            if (!$filling)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Relleno no encontrado'
                ], 404);

            $filling = $filling->updateFilling($request, $filling);

            return response()->json([
                'success' => true,
                'data' => [
                    'filling' => Filling::find($filling->id)
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

            $filling = Filling::find($id);
        
            if (!$filling)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Relleno no encontrado'
                ], 404);

            $filling->deleteFilling($id);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'filling' => $filling
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
