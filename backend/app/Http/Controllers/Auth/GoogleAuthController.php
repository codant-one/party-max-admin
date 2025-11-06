<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Requests\RegisterClientRequest;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

use Illuminate\Http\Request;

use App\Models\User;


class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google’s OAuth page.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google.
     */
    public function callback()
    {
        try {
            // Get the user information from Google
            $user = Socialite::driver('google')->user();
        } catch (Throwable $e) {
           // Build the complete frontend URL with token
           $appDomain = env('APP_DOMAIN');
            
           // Ensure APP_DOMAIN has a proper scheme (http:// or https://)
           if (!preg_match('/^https?:\/\//', $appDomain)) {
               $appDomain = (request()->secure() ? 'https://' : 'http://') . $appDomain;
           }
           $message = 'Google authentication failed.';
           $frontendUrl = $appDomain . '/callback?error=' . urlencode($message);
           return redirect($frontendUrl); 
        }

        // Check if the user already exists in the database
        $existingUser = User::where('email', $user->email)->first();

        if ($existingUser) {
            if($existingUser->google_id !== $user->id){
                // Build the complete frontend URL with token
                $appDomain = env('APP_DOMAIN');
                    
                // Ensure APP_DOMAIN has a proper scheme (http:// or https://)
                if (!preg_match('/^https?:\/\//', $appDomain)) {
                    $appDomain = (request()->secure() ? 'https://' : 'http://') . $appDomain;
                }
                $message = 'El usuario registrado no ha ingresado con Google Authentication.';
                $frontendUrl = $appDomain . '/callback?error=' . urlencode($message);
                return redirect($frontendUrl);
            }
            // Update user online status
            $existingUser->online = Carbon::now();
            $existingUser->save();
            
            // Generate JWT token without credentials
            $token = JWTAuth::fromUser($existingUser);
            
            $permissions = getPermissionsByRole($existingUser);
            $userData = getUserData($existingUser->load(['userDetail.province.country', 'userDetail.document_type', 'client.gender', 'supplier']));

            $tokenData = [
                'token' => $token,
                'accessToken' => $token,
                'token_type' => 'bearer',
                'user_data' => $userData,
                'userAbilities' => $permissions
            ];

            // Build the complete frontend URL with token
            $appDomain = env('APP_DOMAIN');
            
            // Ensure APP_DOMAIN has a proper scheme (http:// or https://)
            if (!preg_match('/^https?:\/\//', $appDomain)) {
                $appDomain = (request()->secure() ? 'https://' : 'http://') . $appDomain;
            }

            // Encode the entire array as JSON and then Base64 for URL safety
            $encodedData = base64_encode(json_encode($tokenData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            
            $frontendUrl = $appDomain . '/callback?data=' . urlencode($encodedData);
            
            return redirect($frontendUrl); 
        } else {
            // Otherwise, create a new user and log them in

            $registerClientRequest = new RegisterClientRequest();
            $registerClientRequest->name = $user->name;
            $registerClientRequest->email = $user->email;
            $registerClientRequest->password = '1234';
            $registerClientRequest->phone = '----';
            $registerClientRequest->rolname = 'cliente';
            $registerClientRequest->google_id = $user->id;


            $authController = new AuthController();
            $response = $authController->register($registerClientRequest);
            $content = $response->getContent();
            $data = json_decode($content, true);
            if($data['success']){
                $client = $data['data']['client'];
                $userCreated = User::find($client['user_id']);
                
                
                // Generate JWT token without credentials
                $token = JWTAuth::fromUser($userCreated);
                
                $permissions = getPermissionsByRole($userCreated);
                $userData = getUserData($userCreated->load(['userDetail.province.country', 'userDetail.document_type', 'client.gender', 'supplier']));

                $tokenData = [
                    'token' => $token,
                    'accessToken' => $token,
                    'token_type' => 'bearer',
                    'user_data' => $userData,
                    'userAbilities' => $permissions
                ];

                // Build the complete frontend URL with token
                $appDomain = env('APP_DOMAIN');
                
                // Ensure APP_DOMAIN has a proper scheme (http:// or https://)
                if (!preg_match('/^https?:\/\//', $appDomain)) {
                    $appDomain = (request()->secure() ? 'https://' : 'http://') . $appDomain;
                }

                // Encode the entire array as JSON and then Base64 for URL safety
                $encodedData = base64_encode(json_encode($tokenData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                
                $frontendUrl = $appDomain . '/callback?data=' . urlencode($encodedData);
                
                return redirect($frontendUrl); 
                
            }
        }
    }
}
