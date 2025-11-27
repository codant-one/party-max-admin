<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

use App\Http\Requests\RegisterClientRequest;
use App\Http\Requests\RegisterSupplierRequest;

use App\Models\Client;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\UserRegisterToken;
use App\Models\Supplier;
use App\Models\Billing;
use App\Models\Order;
use App\Models\Address;
use App\Models\Coupon;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt', ['except' => 
            ['login', 'register', 'find', 'completed', 'sendInfo']
        ]);
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

        if (empty(Auth::user()->email_verified_at) && (Auth::user()->getRoleNames()[0] === 'Cliente') && ($request->panel === 'client')) {
            Auth::logout();

            return response()->json([
                'success' => false,
                'message' => 'not_confirm',
                'errors' => 'Correo electrónico no verificado. Revise su correo electrónico donde se le indica los pasos a seguir para verificar el mismo.'
            ], 400);
        }

        if(Auth::user()->getRoleNames()[0] !== 'Cliente' && ($request->panel === 'client')) {
            Auth::logout();

            return response()->json([
                'success' => false,
                'message' => 'not_role',
                'errors' => 'Usuario no registrado'
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

            if($request->panel) {
                $user->is_2fa =  ($user->is_2fa === 0) ? 1 : 0;
                $user->update();
            }

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

    public function storeDetail()
    {
        try {
            
            $supplier = 
                Supplier::where('user_id', Auth::user()->id)
                        ->first();
        
            return response()->json([
                'success' => true,
                'message' => 'store',
                'data' => [
                    'supplier' => $supplier
                ]
            ], 200);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error '.$ex->getMessage(),
                'exception' => $ex->getMessage()
            ], 500);
        }
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
            $userData = getUserData(Auth::user()->load(['userDetail.province.country', 'userDetail.document_type', 'client.gender', 'supplier']));

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

    public function register(RegisterClientRequest $request)
    {

        try {

            $password = $request->input('password');
            $hashedPassword = Hash::make($password);

            $user = new User();
            $user->name = $request->name;
            // last_name es opcional (útil para registros via Google)
            $user->last_name = $request->last_name ?? null;
            $user->username =  Str::slug(trim($request->name . ' ' . ($request->last_name ?? '')));
            $user->email = strtolower($request->email);
            $user->password = $hashedPassword;
            $user->google_id = $request->google_id ?? null;
            $user->email_verified_at =  $request->google_id ? now() : null;
            $user->save();

            //Crear o Actualizar token.
            $registerConfirm = UserRegisterToken::updateOrCreate(
                ['user_id' => $user->id],
                ['token' => Str::random(60)]
            );

            $userDetails = new UserDetails();
            $userDetails->province_id = 292;
            $userDetails->user_id = $user->id;
            $userDetails->phone = $request->phone;
            $userDetails->save();

            if($request->rolname === 'cliente') {

                $user->assignRole('Cliente');
            
                $client = new Client();
                $client->user_id = $user->id;
                $client->gender_id = 1;
                $client->save();

                $billings = Billing::where('email', $request->email)->get();

                foreach ($billings as $billing) {
                    $address = new Address();
                    $address->client_id = $client->id;
                    $address->addresses_type_id = 1;
                    $address->province_id = $billing->province_id;
                    $address->title = 'Default';
                    $address->phone = $billing->phone;
                    $address->address = $billing->address;
                    $address->street = $billing->street;
                    $address->city = $billing->city;
                    $address->postal_code = $billing->postal_code;
                    $address->default = 1;
                    $address->save();

                    $order = Order::find($billing->order_id);

                    if ($order) {
                        $order->client_id = $client->id;
                        $order->address_id = $address->id;
                        $order->save();
                    }
                }

                $client = Client::with(['user'])->find($client->id);

                $client = Client::where('user_id', $user->id)->first();

                $coupon = new Coupon();
                $coupon->client_id = $client->id;
                $coupon->is_percentage = 1;
                $coupon->description = 'Cupón de bienvenida, disfruta de un 10% OFF en tus compras';
                $coupon->code = 'BIENVENIDO'.$client->id;
                $coupon->amount = 10;
                $coupon->expiration_date = now()->addMonth();;
                $coupon->save();

                $username = $client->user->username;
                $email = $client->user->email;
                
                $info = [
                    'title' => 'Verificar Correo Electrónico',
                    'text' => 'Tu cuenta no está verificada. Confirma tu cuenta con los pasos a seguir para verificarla.',
                    'buttonLink' =>  env('APP_DOMAIN').'/register-confirm?token=' . $registerConfirm['token'],
                    'buttonText' => 'Confirmar',
                    'subject' => 'Bienvenido a PARTYMAX',
                    'email' => 'emails.auth.notifications'
                ];
                
                $responseMail = $request->google_id ? 'Registro exitoso via Google. Por favor, inicie sesión para continuar.' : $this->sendMail($info, $user->id); 
            }

            return response()->json([
                'success' => true,
                'email_response' => $responseMail,
                'data' => [ 
                    'client' => Client::with(['user.userDetail.province.country', 'gender'])->find($client->id)
                ]
            ]);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error '.$ex->getMessage(),
                'exception' => $ex->getMessage()
            ], 500);
        }


    }

    public function sendInfo(RegisterSupplierRequest $request)
    {

        try {
 
            $info = [
                'name' => $request->name,
                'nit' => $request->nit,
                'email_contact' => strtolower($request->email),
                'phone' => $request->phone,
                'subject' => 'Un proveedor te ha contactado.',
                'email' => 'emails.suppliers.send_info'
            ];
                
            $responseMail = $this->sendMail($info); 

            return response()->json([
                'success' => true,
                'email_response' => $responseMail,
                'data' => []
            ]);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error '.$ex->getMessage(),
                'exception' => $ex->getMessage()
            ], 500);
        }


    }

    public function find($token)
    {
        
        try {

            $emailConfirm = UserRegisterToken::where('token', $token)->first();

            if (!$emailConfirm)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Token inválido'
                ], 404);

            if (Carbon::parse($emailConfirm->updated_at)->addMinutes(720)->isPast()) {
                $emailConfirm->delete();

                return response()->json([
                    'success' => false,
                    'feedback' => 'error_token',
                    'message' => 'Token vencido'
                ], 404);
                
            }

            return response()->json([
                'success' => true,
                'message' => 'Confirmación de correo electrónico exitosa',
                'data' => [ 
                    'token' => $token
                ]
            ], 200);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error '.$ex->getMessage(),
                'exception' => $ex->getMessage()
            ], 500);
        }

    }

    public function completed(Request $request)
    {
        try {
        
            $emailConfirm = UserRegisterToken::where('token', $request->token)->first();
            $user = User::where('id', $emailConfirm->user_id)->first();

            if (!$user)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Cliente no registrado'
                ], 404);

            if ($user->email_verified_at == null) {
                $user->email_verified_at = now();
                $user->update();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Tu solicitud se ha procesado satisfactoriamente. Correo electrónico verificado. Le invitamos a que inicie sesion',
            ], 200);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error '.$ex->getMessage(),
                'exception' => $ex->getMessage()
            ], 500);
        }
    } 

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithToken($token)
    {
        $permissions = getPermissionsByRole(Auth::user());
        $userData = getUserData(Auth::user()->load(['userDetail.province.country', 'userDetail.document_type', 'client.gender', 'supplier']));

        return [
            'accessToken' => $token,
            'token_type' => 'bearer',
            'user_data' => $userData,
            'userAbilities' => $permissions
        ];
    }

    private function sendMail($info, $id = 1){

        $user = User::find($id);
        
        $data = [
            'name' => $info['name'] ?? null,
            'nit' => $info['nit'] ?? null,
            'email' => $info['email_contact'] ?? null,
            'phone' => $info['phone'] ?? null,
            'title' => $info['title'] ?? null,
            'user' => $user->name . ' ' . $user->last_name,
            'text' => $info['text'] ?? null,
            'buttonLink' =>  $info['buttonLink'] ?? null,
            'buttonText' =>  $info['buttonText'] ?? null
        ];

        $clientEmail = ($id === 1) ? env('MAIL_TO_CONTACT') : $user->email;
        $subject = $info['subject'];
        
        try {
            \Mail::send($info['email'], $data, function ($message) use ($clientEmail, $subject) {
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    $message->to($clientEmail)->subject($subject);
            });

            return "Tu solicitud se ha procesado satisfactoriamente. Correo electrónico verificado. Le invitamos a que inicie sesion.";
        } catch (\Exception $e){
            return "Error al enviar el correo electrónico. ".$e;
        }        

        return "";

    } 
}
