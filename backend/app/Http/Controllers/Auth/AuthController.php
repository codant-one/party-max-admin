<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt', ['except' => ['login']]);
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="User login",
     *     description= "Give a user access to the page",
     *     tags={"AUTH"},
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"email","password"},
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      format= "email",
     *                      description="The E-mail for register an account."
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                      format= "password",
     *                      description="Alphanumeric Password"
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
     * Store a newly created resource in storage
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'missing_params',
                'errors' => $validator->errors()
            ], 400);
        }

        $credentials = request(['email', 'password']);

        $expired = now()->addHours(24);
            
        if (!$token = Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'invalid_credentials',
                'errors' => 'Usuario o contraseña invalida'
            ], 400);
        }

        $user = Auth::user();
        $user->online = Carbon::now();
        $user->save();

        if (env('APP_DEBUG') || ($user->is_2fa === 0)) {
            return response()->json([
                'success' => true,
                'message' => 'login_success',
                'data' => $this->respondWithToken($token)
            ], 200);
        }

        if (empty($user->token_2fa)) {
            $google2fa = app('pragmarx.google2fa');
            $token2FA = $google2fa->generateSecretKey();

            $user->token_2fa = $token2FA;
            $user->update();

            $qr = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $token2FA
            );

            $data = [
                'qr' => $qr,
                'token' => $token2FA
            ];

            return response()->json([
                'success' => true,
                'message' => '2fa-generate',
                'data' => array_merge($data, $this->respondWithToken($token))
            ], 200);

        } else {

            $data = [
                'token' => $user->token_2fa
            ];

            return response()->json([
                'success' => true,
                'message' => '2fa',
                'data' => array_merge($data, $this->respondWithToken($token))
            ], 200);
        }
    }

    public function validate_double_factor_auth(Request $request)
    {
        $user = auth()->user();
        $google2fa = app('pragmarx.google2fa');

        if ($google2fa->verifyKey($user->token_2fa, $request->token_2fa)) {
            session()->put('2fa', '1');
            $user->is_2fa =  ($user->is_2fa === 0) ? 1 : 0;
            $user->update();

            return response()->json([
                'success' => true,
                'message' => 'login_success'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'invalid_code',
            'errors' => 'Código de verificación incorrecto'
        ], 400);
    }

    public function generateQR()
    {
        $user = Auth::user();
        $google2fa = app('pragmarx.google2fa');
        $token2FA = '';

        if (empty($user->token_2fa)) {
            $token2FA = $google2fa->generateSecretKey();

            $user->token_2fa = $token2FA;
            $user->update();
        }

        $qr = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            empty($user->token_2fa) ? $token2FA : $user->token_2fa
        );

        $data = [
            'qr' => $qr,
            'is_2fa' => ($user->is_2fa === 0) ? false : true,
            'token' => empty($user->token_2fa) ? $token2FA : $user->token_2fa
        ];

        return response()->json([
            'success' => true,
            'message' => 'generate-qr',
            'data' => $data
        ], 200);

        return response()->json([
            'success' => false,
            'message' => 'invalid_code',
            'errors' => 'Código de verificación incorrecto'
        ], 400);
    }

    /**
     * @OA\Post(
     *     path="/auth/me",
     *     summary="Verify User",
     *     description= "Check if the user is still logged in through a password hash",
     *     tags={"AUTH"},
     *     security={{"bearerAuth": {} }},
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"hash"},
     *                  @OA\Property(
     *                      property="hash",
     *                      type="string",
     *                      format= "text",
     *                      description="Password hash."
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
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $hash = $request->hash;

        $user = Auth::user();
        $user->online = Carbon::now();
        $user->save();

        if($user->password === $hash){

            $permissions = getPermissionsByRole(Auth::user());
            $userData = getUserData(Auth::user()->load(['userDetail.province.country']));

            return response()->json([
                'success' => true,
                'data' => [ 
                    'user_data' => $userData,
                    'userAbilities' => $permissions
                ]
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => 'params_validation_failed',
                'error' => 'Data does not match'
            ], 400);
        }

    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Logout",
     *     description= "Logout User",
     *     tags={"AUTH"},
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
     * Log the user out (Invalidate the token).
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(): JsonResponse
    {
        Auth::logout();

        return response()->json([
            'success' => true,
            'message' => 'Log out successfully'
        ], 200);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $permissions = getPermissionsByRole(Auth::user());
        $userData = getUserData(Auth::user()->load(['userDetail.province.country']));

        return [
            'accessToken' => $token,
            'token_type' => 'bearer',
            'user_data' => $userData,
            'userAbilities' => $permissions
        ];
    }
}
