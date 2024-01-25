<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Spatie\Permission\Middlewares\PermissionMiddleware;

class AddressController extends Controller
{
    public function __construct()
    {
        //
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;

            $query = Address::with(['type'])
                         ->applyFilters(
                            $request->only([
                                    'search',
                                    'orderByField',
                                    'orderBy',
                                    'client_id'
                                ])
                          );            

            $count = $query->applyFilters(
                        $request->only([
                                'search',
                                'orderByField',
                                'orderBy'
                            ])
                    )->count();

            $addresses = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'addresses' => $addresses,
                    'addressesTotalCount' => $count
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
    public function store(AddressRequest $request): JsonResponse
    {
        try {
            
            $address = Address::createAddress($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'address' => $address
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

            $address = Address::with(['type'])->find($id);
        
            if (!$address)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Dirección no encontrada'
                ], 404);
            
            return response()->json([
                'success' => true,
                'data' => [ 
                    'address' => $address
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
    public function update(AddressRequest $request, $id): JsonResponse
    {
        try {

            $address = Address::find($id);
       
            if (!$address)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Dirección no encontrada'
                ], 404);

            $address = $address->updateAddress($request, $address); 

            return response()->json([
                'success' => true,
                'data' => [ 
                    'address' => $address
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
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {

            $address = Address::find($id);
        
            if (!$address)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Dirección no encontrado'
                ], 404);
            
            $address->deleteAddress($id);

            return response()->json([
                'success' => true
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
