<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\ClientIp;
use App\Models\ClientRegistration;

class IpController extends Controller
{
    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver ips|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':bloquear ips|administrador')->only(['updateStates']);
        $this->middleware(PermissionMiddleware::class . ':eliminar ips|administrador')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;
        
            $query = ClientIp::with(['registrations'])
                        ->applyFilters(
                            $request->only([
                                'search',
                                'orderByField',
                                'orderBy'
                            ])
                        );

            $count = $query->count();

            $ips = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'ips' => $ips,
                    'ipsTotalCount' => $count
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
    public function store(Request $request): JsonResponse
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {

            $ip = ClientIp::with(['registrations' => function($query) {
                $query->orderBy('date', 'desc');
            }])->find($id);

            if (!$ip)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Ip no encontrada'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'ip' => $ip
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
    public function update(Request $request, $id): JsonResponse
    {
        //
    }


    /*
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {

            $ip = ClientIp::find($id);
        
            if (!$ip)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Ip no encontrada'
                ], 404);

            $ip->deleteIp($id);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'ip' => $ip
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

    public function updateStates(Request $request, $id): JsonResponse
    {
        try {

            $ip = ClientIp::find($id);
        
            if (!$ip)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Ip no encontrada'
                ], 404);

            $ip->updateStatesIp($request, $ip); 

            return response()->json([
                'success' => true,
                'data' => [ 
                    'ip' => $ip
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
