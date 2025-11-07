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
use App\Models\Client;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth page.
     */
    public function redirect()
    {
        // Scopes básicos siempre incluidos
        $scopes = [
            'openid',
            'email',
            'profile'
        ];

        // Scope de birthday solo si está habilitado y la app está verificada
        // Para habilitarlo, agrega GOOGLE_REQUEST_BIRTHDAY=true en tu .env
        if (env('GOOGLE_REQUEST_BIRTHDAY', false)) {
            $scopes[] = 'https://www.googleapis.com/auth/user.birthday.read';
        }

        return Socialite::driver('google')
            ->stateless()
            ->scopes($scopes)
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
            
            // Intentar obtener birthday adicional desde People API si el scope está habilitado
            // Esto es necesario porque el scope básico no incluye birthday
            if (env('GOOGLE_REQUEST_BIRTHDAY', false) && isset($googleUser->token)) {
                $this->fetchBirthdayFromPeopleAPI($googleUser);
            }
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

            // Actualizar perfil del cliente con birthday de Google si está disponible
            $this->updateClientProfileFromGoogle($existingUser, $googleUser);

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

        // Actualizar perfil del cliente con birthday de Google si está disponible
        $this->updateClientProfileFromGoogle($userCreated, $googleUser);

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

    /**
     * Update client profile with Google data (birthday)
     * 
     * @param User $user
     * @param \Laravel\Socialite\Two\User $googleUser
     * @return void
     */
    private function updateClientProfileFromGoogle($user, $googleUser)
    {
        try {
            // Cargar la relación client si no está cargada
            if (!$user->relationLoaded('client')) {
                $user->load('client');
            }

            // Intentar obtener birthday de Google
            // NOTA: Sin el scope 'user.birthday.read', Google NO proporciona el birthday
            // El scope requiere verificación de la app, por eso está deshabilitado por defecto
            $birthday = null;
            $rawUser = $googleUser->user ?? null;
            
            // Método 1: Acceso directo al campo birthday (formato más común)
            if (isset($rawUser['birthday'])) {
                $birthdayValue = $rawUser['birthday'];
                
                // Si viene como string (formato: "1990-05-15" o "05/15/1990")
                if (is_string($birthdayValue)) {
                    $parsedDate = strtotime($birthdayValue);
                    if ($parsedDate !== false) {
                        $birthday = date('Y-m-d', $parsedDate);
                    }
                }
                // Si viene como objeto con year, month, day
                elseif (is_array($birthdayValue) && isset($birthdayValue['year'], $birthdayValue['month'], $birthdayValue['day'])) {
                    $birthday = sprintf('%04d-%02d-%02d', 
                        $birthdayValue['year'], 
                        $birthdayValue['month'], 
                        $birthdayValue['day']
                    );
                }
            }
            // Método 2: Acceso a través del array birthdays (People API)
            elseif (isset($rawUser['birthdays']) && is_array($rawUser['birthdays'])) {
                foreach ($rawUser['birthdays'] as $birthdayData) {
                    if (isset($birthdayData['date'])) {
                        $date = $birthdayData['date'];
                        // Formato: {"year": 1990, "month": 5, "day": 15}
                        if (isset($date['year'], $date['month'], $date['day'])) {
                            $birthday = sprintf('%04d-%02d-%02d', $date['year'], $date['month'], $date['day']);
                            break; // Usar el primer birthday encontrado
                        }
                    }
                }
            }
            // Método 3: Intentar acceder directamente desde el objeto googleUser
            elseif (isset($googleUser->birthday)) {
                $parsedDate = strtotime($googleUser->birthday);
                if ($parsedDate !== false) {
                    $birthday = date('Y-m-d', $parsedDate);
                }
            }

            // Obtener el cliente existente o null
            $client = $user->client;
            $existingGenderId = $client ? $client->gender_id : null;
            $existingBirthday = $client ? $client->birthday : null;

            // Preparar el birthday: priorizar el de Google si está disponible, sino mantener el existente
            $finalBirthday = $birthday ?: $existingBirthday;

            // Siempre actualizar cuando el usuario se loguea
            // Si hay birthday de Google, usarlo; si no, mantener el existente
            $request = new Request();
            
            // Para el birthday: priorizar el de Google si está disponible, sino mantener el existente
            $birthdayToSave = $finalBirthday ?: $existingBirthday;
            
            $request->merge([
                'gender_id' => $existingGenderId, // Mantener el género existente si existe
                'birthday' => $birthdayToSave // Puede ser null, el método lo maneja correctamente
            ]);

            // Actualizar o crear el perfil del cliente
            Client::updateOrCreateClientProfile($request, $user);
        } catch (Throwable $e) {
            \Log::warning('Failed to update client profile from Google: ' . $e->getMessage());
        }
    }

    /**
     * Fetch birthday from Google People API using access token
     * 
     * @param \Laravel\Socialite\Two\User $googleUser
     * @return void
     */
    private function fetchBirthdayFromPeopleAPI($googleUser)
    {
        try {
            if (!isset($googleUser->token)) {
                return;
            }

            // Llamar a People API para obtener información adicional incluyendo birthday
            $response = Http::withToken($googleUser->token)
                ->timeout(10)
                ->get('https://people.googleapis.com/v1/people/me', [
                    'personFields' => 'birthdays'
                ]);

            if ($response->successful()) {
                $peopleData = $response->json();
                
                // Agregar birthday al objeto googleUser si está disponible
                if (isset($peopleData['birthdays']) && is_array($peopleData['birthdays'])) {
                    foreach ($peopleData['birthdays'] as $birthdayData) {
                        if (isset($birthdayData['date'])) {
                            $date = $birthdayData['date'];
                            if (isset($date['year'], $date['month'], $date['day'])) {
                                $birthday = sprintf('%04d-%02d-%02d', 
                                    $date['year'], 
                                    $date['month'], 
                                    $date['day']
                                );
                                
                                // Agregar al array user del objeto googleUser
                                if (!isset($googleUser->user)) {
                                    $googleUser->user = [];
                                }
                                $googleUser->user['birthday'] = $birthday;
                                $googleUser->user['birthdays'] = $peopleData['birthdays'];
                                
                                \Log::info('Birthday obtenido desde People API', [
                                    'birthday' => $birthday
                                ]);
                                break;
                            }
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            \Log::warning('Failed to fetch birthday from People API: ' . $e->getMessage());
        }
    }
}