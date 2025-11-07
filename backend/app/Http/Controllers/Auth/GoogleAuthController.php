<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
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
                'prompt' => 'consent select_account', // fuerza confirmación y elección de cuenta
                'include_granted_scopes' => 'false',   // no reutilizar permisos previos
                'access_type' => 'online',             // o 'offline' si quieres refresh_token
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
            return $this->postMessageAndClose([], 'Google authentication failed.');
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

            $tokenData = [
                'token' => $token,
                'accessToken' => $token,
                'token_type' => 'bearer',
                'user_data' => $userData,
                'userAbilities' => $permissions
            ];

            // Popup: enviar datos a opener y cerrar. Fallback: redirigir al frontend /callback.
            return $this->postMessageAndClose($tokenData);
        }

        // Registrar usuario nuevo si no existe
        $registerReq = new RegisterClientRequest();
        $registerReq->name = $googleUser->name;
        $registerReq->email = $googleUser->email;
        $registerReq->password = Str::random(32);  // evita passwords débiles
        $registerReq->phone = '----';
        $registerReq->rolname = 'cliente';
        $registerReq->google_id = $googleUser->id;

        $authController = new AuthController();
        $response = $authController->register($registerReq);
        $data = json_decode($response->getContent(), true);

        if (!($data['success'] ?? false)) {
            return $this->postMessageAndClose([], 'Register failed.');
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

        $tokenData = [
            'token' => $token,
            'accessToken' => $token,
            'token_type' => 'bearer',
            'user_data' => $userData,
            'userAbilities' => $permissions
        ];

        return $this->postMessageAndClose($tokenData);
    }

    /**
     * Devuelve una página mínima que postMessage los datos al opener y cierra el popup.
     * Si no hay opener (p.ej. dispositivos que bloquean opener), redirige al frontend.
     */
    private function postMessageAndClose(array $payload = [], ?string $error = null)
    {
        $type = $error ? 'google-auth-error' : 'google-auth-success';

        // Fallback redirect para cuando no hay opener (móvil, bloqueo navegador, etc.)
        $app = $this->appDomain();
        if ($error) {
            $fallback = $app . '/callback?error=' . urlencode($error);
        } else {
            $encoded = base64_encode(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $fallback = $app . '/callback?data=' . urlencode($encoded);
        }

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $html = <<<HTML
<!doctype html>
<meta http-equiv="referrer" content="no-referrer">
<title>Signing in…</title>
<script>
(function () {
  try {
    var msg = { type: '{$type}', payload: {$json} };
    if (window.opener && window.opener !== window) {
      window.opener.postMessage(msg, '*');
      window.close();
      return;
    }
  } catch (e) {}
  // Fallback si no hay opener
  location.replace('{$fallback}');
})();
</script>
HTML;

        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    private function appDomain(): string
    {
        $appDomain = env('APP_DOMAIN');
        if (!preg_match('/^https?:\/\//', $appDomain)) {
            $appDomain = (request()->secure() ? 'https://' : 'http://') . $appDomain;
        }
        return rtrim($appDomain, '/');
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
                $extension = match($mimeType) {
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