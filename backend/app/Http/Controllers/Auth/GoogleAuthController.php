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
        return Socialite::driver('google')
            ->stateless()
            ->scopes(['openid','email','profile'])
            ->with([
                'prompt' => 'select_account',       // o 'consent select_account'
                'include_granted_scopes' => 'false',
                'access_type' => 'online',
            ])
            ->redirect();
    }

    /**
     * Handle the callback from Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();
        } catch (Throwable $e) {
            \Log::warning('Google auth failed: ' . $e->getMessage());
            return $this->redirectToFrontend([], 'Google authentication failed.');
        }

        // Buscar usuario por email
        $existingUser = User::where('email', $googleUser->email)->first();

        if ($existingUser) {
            if ($existingUser->google_id !== $googleUser->id) {
                $existingUser->google_id = $googleUser->id;
            }
            $existingUser->online = Carbon::now();
            $existingUser->save();

            $token = JWTAuth::fromUser($existingUser);
            $permissions = getPermissionsByRole($existingUser);
            $userData = getUserData($existingUser->load([
                'userDetail.province.country',
                'userDetail.document_type',
                'client.gender',
                'supplier'
            ]));

            return $this->redirectToFrontend([
                'token' => $token,
                'accessToken' => $token,
                'token_type' => 'bearer',
                'user_data' => $userData,
                'userAbilities' => $permissions,
            ]);
        }

        // Crear usuario nuevo
        $registerReq = new RegisterClientRequest();
        $registerReq->name = $googleUser->name;
        $registerReq->email = $googleUser->email;
        $registerReq->password = Str::random(32);   // evita passwords débiles
        $registerReq->phone = '----';
        $registerReq->rolname = 'cliente';
        $registerReq->google_id = $googleUser->id;

        $authController = new AuthController();
        $response = $authController->register($registerReq);
        $data = json_decode($response->getContent(), true);

        if (!($data['success'] ?? false)) {
            return $this->redirectToFrontend([], 'Register failed.');
        }

        $client = $data['data']['client'];
        $userCreated = User::find($client['user_id']);

        // Descargar avatar de Google si existe
        if ($googleUser->avatar && $userCreated) {
            $avatarPath = $this->downloadGoogleAvatar($googleUser->avatar, $userCreated);
            if ($avatarPath) {
                $userCreated->avatar = $avatarPath;
                $userCreated->save();
            }
        }

        $token = JWTAuth::fromUser($userCreated);
        $permissions = getPermissionsByRole($userCreated);
        $userData = getUserData($userCreated->load([
            'userDetail.province.country',
            'userDetail.document_type',
            'client.gender',
            'supplier'
        ]));

        return $this->redirectToFrontend([
            'token' => $token,
            'accessToken' => $token,
            'token_type' => 'bearer',
            'user_data' => $userData,
            'userAbilities' => $permissions,
        ]);
    }

    private function appDomain(): string
    {
        $appDomain = env('APP_DOMAIN');
        if (!preg_match('/^https?:\/\//', $appDomain)) {
            $appDomain = (request()->secure() ? 'https://' : 'http://') . $appDomain;
        }
        return rtrim($appDomain, '/');
    }

    private function redirectToFrontend(array $payload = [], ?string $error = null)
    {
        $app = $this->appDomain();

        if ($error) {
            return redirect($app . '/callback?error=' . urlencode($error));
        }

        $encoded = base64_encode(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        return redirect($app . '/callback?data=' . urlencode($encoded));
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
            $response = Http::timeout(10)->get($avatarUrl);

            if ($response->successful()) {
                $imageContent = $response->body();
                $imageInfo = getimagesizefromstring($imageContent);

                if ($imageInfo === false) {
                    return null;
                }

                $mimeType = $imageInfo['mime'];
                $extension = match ($mimeType) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                    default => 'jpg'
                };

                $fileName = Str::random(25) . '.' . $extension;
                $path = 'avatars/' . $fileName;

                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }

                Storage::disk('public')->put($path, $imageContent);

                return $path;
            }
        } catch (Throwable $e) {
            \Log::warning('Failed to download Google avatar: ' . $e->getMessage());
        }

        return null;
    }
}