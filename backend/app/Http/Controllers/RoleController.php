<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;

use App\Models\Role;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Middlewares\PermissionMiddleware;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
        $this->middleware(PermissionMiddleware::class . ':ver roles|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear roles|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar roles|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar roles|administrador')->only(['destroy']);
        $this->middleware(PermissionMiddleware::class . ':ver usuarios|administrador')->only(['all']);
    }

    /**
     * @OA\Get(
     *     path="/roles",
     *     summary="Lists available Roles",
     *     description= "Gets all available Roles resources",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {} }},
     *     @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="Search Text",
     *          @OA\Schema(
     *              type="string",
     *              format="text",
     *              description="Search text to find users"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="orderByField",
     *          in="query",
     *          description="Order by field the query",
     *          @OA\Schema(
     *              type="string",
     *              format="text",
     *              description="Order by field the query"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="orderBy",
     *          in="query",
     *          description="Default query order 'asc'",
     *          @OA\Schema(
     *              type="string",
     *              format="text",
     *              description="Default query order asc"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          description="Number of rows per page",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *              description="number of rows per page"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Number of page",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *              description="number of page"
     *          )
     *     ),
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
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;

            $query = Role::with('permissions')
                         ->applyFilters(
                            $request->only([
                                'search',
                                'orderByField',
                                'orderBy'
                            ])
                         );

            $roles = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);
            
            $count = $query->count();

            return response()->json([
                'success' => true,
                'data' => [ 
                    'roles' => $roles,
                    'rolesTotalCount' => $count
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
     * @OA\Post(
     *     path="/roles",
     *     summary="Create a new Role",
     *     description= "Create a new Role",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {} }},
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"name","permissions"},
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      format= "text",
     *                      description="Name Role."
     *                  ),
     *                  @OA\Property(
     *                      property="permissions",
     *                      type="array",
     *                      @OA\Items(type="string"),
     *                      description="Assigned permissions array."
     *                  )
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=400,
     *         description="Some was wrong"
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
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request): JsonResponse
    {
        try {

            $role = Role::createRole($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'role' => $role
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
     * @OA\Put(
     *     path="/roles/{id}",
     *     summary="Update a Role",
     *     description= "Update a Role",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {} }},
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The role id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *              description="The unique identifier of a role"
     *          )
     *     ),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"name","permissions"},
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      format= "text",
     *                      description="Name role."
     *                  ),
     *                  @OA\Property(
     *                      property="permissions",
     *                      type="array",
     *                      @OA\Items(type="string"),
     *                      description="Assigned permissions array."
     *                  )
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=400,
     *         description="Some was wrong"
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
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, $id): JsonResponse
    {
        try {

            $role = Role::find($id);

            if (!$role)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Rol no encontrado'
                ], 404);

            $role->updateRole($request, $role); 

            return response()->json([
                'success' => true,
                'data' => [ 
                    'role' => $role
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
     * @OA\Delete(
     *     path="/roles/{id}",
     *     summary="Delete a Role",
     *     description= "Delete a Role",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {} }},
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The role id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *              description="The unique identifier of a role"
     *          )
     *     ),
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
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {

            $role = Role::find($id);
        
            if (!$role)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Rol no encontrado'
                ], 404);

            $role->deleteRole($id);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'role' => $role
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
     * @OA\Get(
     *     path="/roles/role/all",
     *     summary="Lists available Roles Array",
     *     description= "Gets all available Roles Array resources",
     *     tags={"Roles"},
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
     */
    public function all(){

        try {

            return response()->json([
                'success' => true,
                'data' => [ 
                    'roles' => Role::all()->pluck('name')
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
