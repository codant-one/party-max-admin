<?php

namespace App\Http\Controllers;

use App\Http\Requests\FlavorRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\Flavor;

class FlavorController extends Controller
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

            $query = Flavor::applyFilters(
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

            $flavors = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'flavors' => $flavors,
                    'flavorsTotalCount' => $count
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
    public function store(FlavorRequest $request): JsonResponse
    {
        try {

            $flavor = Flavor::createFlavor($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'flavor' => Flavor::find($flavor->id)
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

            $flavor = Flavor::find($id);

            if (!$flavor)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Sabor no encontrado'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'flavor' => $flavor
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
    public function update(FlavorRequest $request, $id): JsonResponse
    {
        try {

            $flavor = Flavor::find($id);
        
            if (!$flavor)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Sabor no encontrado'
                ], 404);

            $flavor = $flavor->updateFlavor($request, $flavor);

            return response()->json([
                'success' => true,
                'data' => [
                    'flavor' => Flavor::find($flavor->id)
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

            $flavor = Flavor::find($id);
        
            if (!$flavor)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Sabor no encontrado'
                ], 404);

            $flavor->deleteFlavor($id);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'flavor' => $flavor
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
