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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

use Illuminate\Http\Request;

use App\Models\User;


class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth page.
     */
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle the callback from Google.
     */
    public function callback()
    {
        try {
            // Get the user information from Google
            $user = Socialite::driver('google')->stateless()->user();
        } catch (Throwable $e) {
           // Build the complete frontend URL with token
           $appDomain = env('APP_DOMAIN');
            
           // Ensure APP_DOMAIN has a proper scheme (http:// or https://)
           if (!preg_match('/^https?:\/\//', $appDomain)) {
               $appDomain = (request()->secure() ? 'https://' : 'http://') . $appDomain;
           }
           $message = 'Google authentication failed.';
           $frontendUrl = $appDomain . '/callback?error=' . urlencode($e);
           return redirect($frontendUrl); 
        }

        // Check if the user already exists in the database
        $existingUser = User::where('email', $user->email)->first();

        if ($existingUser) {
            // Si el usuario no tiene google_id o es diferente, actualizarlo
            if($existingUser->google_id !== $user->id){
                $existingUser->google_id = $user->id;
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
                
                // Download and save Google avatar if available
                if ($user->avatar && $userCreated) {
                    $avatarPath = $this->downloadGoogleAvatar($user->avatar, $userCreated);
                    if ($avatarPath) {
                        $userCreated->avatar = $avatarPath;
                        $userCreated->save();
                    }
                }
                
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

    /**
     * Download and save Google avatar image
     * 
     * @param string $avatarUrl
     * @param User $user
     * @return string|null
     */
    private function downloadGoogleAvatar($avatarUrl, $user)
    {
        try {
            // Download the image from Google
            $response = Http::timeout(10)->get($avatarUrl);
            
            if ($response->successful()) {
                $imageContent = $response->body();
                $imageInfo = getimagesizefromstring($imageContent);
                
                if ($imageInfo === false) {
                    return null;
                }
                
                // Determine file extension from MIME type
                $mimeType = $imageInfo['mime'];
                $extension = match($mimeType) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                    default => 'jpg'
                };
                
                // Generate unique filename
                $fileName = Str::random(25) . '.' . $extension;
                $path = 'avatars/' . $fileName;
                
                // Delete old avatar if exists
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                
                // Save the image
                Storage::disk('public')->put($path, $imageContent);
                
                return $path;
            }
        } catch (Throwable $e) {
            // Log error but don't fail the registration
            \Log::warning('Failed to download Google avatar: ' . $e->getMessage());
        }
        
        return null;
    }
}
