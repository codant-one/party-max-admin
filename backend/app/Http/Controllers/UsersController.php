<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Http\Requests\UserRequest;

use App\Models\User;
use App\Models\Client;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Spatie\Permission\Middlewares\PermissionMiddleware;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
        $this->middleware(PermissionMiddleware::class . ':ver usuarios|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear usuarios|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar usuarios|administrador')->only(['update','updatePasswordUser']);
        $this->middleware(PermissionMiddleware::class . ':eliminar usuarios|administrador')->only(['destroy']);
    }

    /**
     * @OA\Get(
     *     path="/users",
     *     summary="Lists available Users",
     *     description= "Gets all available Users resources",
     *     tags={"Users"},
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
            
            $limit = $request->has('limit') ? $request->limit : 10;;

            $query = User::with(['roles','userDetail.province.country'])
                         ->whereHas('roles', function ($query) {
                            $query->where('name', 'SuperAdmin')
                                  ->orWhere('name', 'Administrador');
                         })
                         ->applyFilters(
                            $request->only([
                                'search',
                                'orderByField',
                                'orderBy'
                            ])
                        );

            $count = $query->whereHas('roles', function ($query) {
                                $query->where('name', 'SuperAdmin')
                                      ->orWhere('name', 'Administrador');
                            })
                           ->applyFilters(
                                $request->only([
                                    'search',
                                    'orderByField',
                                    'orderBy'
                                ])
                            )->count();

            $users = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'users' => $users,
                    'usersTotalCount' => $count
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
     *     path="/users",
     *     summary="Create a new User",
     *     description= "Create a new User",
     *     tags={"Users"},
     *     security={{"bearerAuth": {} }},
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"name","last_name","email","password","roles","province_id","phone","address","username"},
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      format= "text",
     *                      description="Name user."
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      type="string",
     *                      format= "text",
     *                      description="User Last Name"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      format= "email",
     *                      description="Email user."
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                      format= "text",
     *                      description="Password user."
     *                  ),
     *                  @OA\Property(
     *                      property="roles",
     *                      type="array",
     *                      @OA\Items(type="string"),
     *                      description="Assigned roles array."
     *                  ),
     *                  @OA\Property(
     *                      property="province_id",
     *                      type="integer",
     *                      format= "text",
     *                      description="Province identificator"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      type="string",
     *                      format= "text",
     *                      description="User Phone"
     *                  ),
     *                  @OA\Property(
     *                      property="address",
     *                      type="string",
     *                      format= "text",
     *                      description="User Address"
     *                  ),
     *                  @OA\Property(
     *                      property="username",
     *                      type="string",
     *                      format= "text",
     *                      description="User username",
     *                      required={"true"},
     *                  ),
     *                  @OA\Property(
     *                      property="document",
     *                      type="string",
     *                      format= "text",
     *                      description="User document"
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
    public function store(UserRequest $request): JsonResponse
    {
        try{
            
            $user = User::createUser($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'user' => $user
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
     *     path="/users/{id}",
     *     summary="Update a User",
     *     description= "Update a User",
     *     tags={"Users"},
     *     security={{"bearerAuth": {} }},
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The user id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *              description="The unique identifier of a user"
     *          )
     *     ),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"name","last_name","email","roles","province_id","phone","address","username"},
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      format= "text",
     *                      description="Name user."
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      type="string",
     *                      format= "text",
     *                      description="User Last Name"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      format= "email",
     *                      description="Email user."
     *                  ),
     *                  @OA\Property(
     *                      property="roles",
     *                      type="array",
     *                      @OA\Items(type="string"),
     *                      description="Assigned roles array."
     *                  ),
     *                  @OA\Property(
     *                      property="province_id",
     *                      type="integer",
     *                      format= "text",
     *                      description="Province identificator"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      type="string",
     *                      format= "text",
     *                      description="User Phone"
     *                  ),
     *                  @OA\Property(
     *                      property="address",
     *                      type="string",
     *                      format= "text",
     *                      description="User Address"
     *                  ),
     *                  @OA\Property(
     *                      property="username",
     *                      type="string",
     *                      format= "text",
     *                      description="User username",
     *                      required={"true"},
     *                  ),
     *                  @OA\Property(
     *                      property="document",
     *                      type="string",
     *                      format= "text",
     *                      description="User document"
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
    public function update(UserRequest $request, $id): JsonResponse
    {
        try {

            $user = User::find($id);
        
            if (!$user)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Usuario no encontrado'
                ], 404);

            $user->updateUser($request, $user); 

            return response()->json([
                'success' => true,
                'data' => [ 
                    'user' => $user
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
     *     path="/users/{id}",
     *     summary="Delete a User",
     *     description= "Delete a User",
     *     tags={"Users"},
     *     security={{"bearerAuth": {} }},
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The user id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *              description="The unique identifier of a user"
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

            $user = User::find($id);
        
            if (!$user)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Usuario no encontrado'
                ], 404);
            
            $user->deleteUser($id);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'user' => $user
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
     *     path="/users/update/profile",
     *     summary="Update Profile",
     *     description= "Update user information",
     *     tags={"Users"},
     *     security={{"bearerAuth": {} }},
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"province_id","phone","address","last_name","image"},
     *                  @OA\Property(
     *                      property="province_id",
     *                      type="integer",
     *                      format= "text",
     *                      description="Province identificator"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      type="string",
     *                      format= "text",
     *                      description="User Phone"
     *                  ),
     *                  @OA\Property(
     *                      property="address",
     *                      type="string",
     *                      format= "text",
     *                      description="User Address"
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      type="string",
     *                      format= "text",
     *                      description="User Last Name"
     *                  ),
     *                  @OA\Property(
     *                      property="image",
     *                      type="file",
     *                      format= "text",
     *                      description="User Avatar"
     *                  ),
     *                  @OA\Property(
     *                      property="username",
     *                      type="string",
     *                      format= "text",
     *                      description="User username"
     *                  ),
     *                  @OA\Property(
     *                      property="document",
     *                      type="string",
     *                      format= "text",
     *                      description="User document"
     *                  )
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=200,
     *         description="successful operation",
     *     ),
     *    @OA\Response(
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(ProfileRequest $request): JsonResponse
    {

        try {

            $user = Auth::user()->load(['userDetail.province.country']);
            $user->updateProfile($request, $user);

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'avatars/';

                $file_data = uploadFile($image, $path, $user->avatar);

                $user->avatar = $file_data['filePath'];
                $user->update();
            } 

            $userData = getUserData($user->load(['userDetail.province.country']));

            return response()->json([
                'success' => true,
                'data' => [ 
                    'user_data' => $userData
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
     *     path="/users/update/password",
     *     summary="Update Password",
     *     description= "Update a user's password",
     *     tags={"Users"},
     *     security={{"bearerAuth": {} }},
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"password"},
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                      format= "text",
     *                      description="Password"
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'password' => 'required'
        ]);
    
        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'feedback' => 'params_validation_failed',
                'message' => $validate->errors()
            ], 400);
        }

        try {

            $user = Auth::user()->load(['userDetail.province.country']);
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'success' => true,
                'data' => [ 
                    'user_data' => getUserData($user)
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
     *     path="/users/update/password/{id}",
     *     summary="Update Password",
     *     description= "Update a user's password by id",
     *     tags={"Users"},
     *     security={{"bearerAuth": {} }},
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The user id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *              description="The unique identifier of a user"
     *          )
     *     ),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"password"},
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                      format= "text",
     *                      description="Password"
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePasswordUser(Request $request, string $id): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'feedback' => 'params_validation_failed',
                'message' => $validate->errors()
            ], 400);
        }

        try {

            $user = User::with(['userDetail.province.country'])->find($id);
        
            if (!$user)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Usuario no encontrado'
                ], 404);

            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'success' => true,
                'data' => [ 
                    'user_data' => getUserData($user)
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
     *     path="/users/user/online",
     *     summary="Get onlined users",
     *     description= "Get onlined users",
     *     tags={"Users"},
     *     security={{"bearerAuth": {} }},
     *     @OA\Parameter(
     *          name="ids",
     *          in="query",
     *          description="The user ids separated by commas",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              description="user ids separated by commas"
     *          )
     *     ),
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
     */
    public function getOnline(Request $request): JsonResponse
    {
        try{
            
            $users = User::getOnline($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'users' => $users
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
