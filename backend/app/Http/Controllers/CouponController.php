<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\Coupon;

class CouponController extends Controller
{
    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver cupones|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':eliminar cupones|administrador')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;
        
            $query = Coupon::with(['client.user', 'order'])
                        ->applyFilters(
                            $request->only([
                                'search',
                                'orderByField',
                                'orderBy'
                            ])
                        );

            $count = $query->count();

            $coupons = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'coupons' => $coupons,
                    'couponsTotalCount' => $count
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
    public function show($id): JsonResponse
    {
        try {

            $coupon = Coupon::with([
                'client.user', 
                'order'
            ])->find($id);

            if (!$coupon)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Cupón no encontrado'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'coupon' => $coupon
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
 
    }

    /*
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        //
    }

    public function couponsbyclient(Request $request, $id): JsonResponse
    {

        try {

            $limit = $request->has('limit') ? $request->limit : 5;

            $query = Coupon::with(['client', 'order'])
                            ->where('client_id', $id)
                            ->applyFilters(
                                $request->only([
                                    'search',
                                    'orderByField',
                                    'orderBy',
                                    'clientId'
                                ])
                            );

            $count = $query->count();

            $coupons = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

  
            return response()->json([
                'success' => true,
                'data' => [ 
                    'coupons' => $coupons,
                    'couponsTotalCount' => $count
                ]
            ], 200);

        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

    public function couponbyCode($code): JsonResponse
    {
        try {

            $coupon = Coupon::with(['client', 'order'])
                           ->where('code', $code)
                           ->first();
            
            return response()->json([
                'success' => true,
                'data' => [ 
                    'coupon' => $coupon
                ]
            ], 200);

        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

}
