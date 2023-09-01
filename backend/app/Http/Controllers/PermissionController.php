<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Middlewares\PermissionMiddleware;

class PermissionController extends Controller
{
    public function __construct(){
        $this->middleware(PermissionMiddleware::class . ':ver roles|administrador')->only(['index']);
    }

     /**
     * @OA\Get(
     *     path="/permissions/permission/all",
     *     summary="Lists available Permissions Array",
     *     description= "Gets all available Permissions Array resources",
     *     tags={"Permissions"},
     *     security={{"bearerAuth": {} }},
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=500,
     *         description="an ""unexpected"" error"
     *     ),
     *  )
     *
     * Display a listing of the resource.
     */
    public function all(){

        try {

            return response()->json([
                'success' => true,
                'data' => [ 
                    'permissions' => Permission::all()->pluck('name')
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
