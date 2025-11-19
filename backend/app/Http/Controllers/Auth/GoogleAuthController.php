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
            'profile',
        ];

        // Scope de birthday solo si está habilitado y la app está verificada
        // Para habilitarlo, agrega GOOGLE_REQUEST_BIRTHDAY=true en tu .env
        if (env('GOOGLE_REQUEST_BIRTHDAY', false)) {
            $scopes[] = 'https://www.googleapis.com/auth/user.birthday.read';
        }

        // Scope de phone solo si está habilitado y la app está verificada
        // Para habilitarlo, agrega GOOGLE_REQUEST_PHONE=true en tu .env
        if (env('GOOGLE_REQUEST_PHONE', false)) {
            $scopes[] = 'https://www.googleapis.com/auth/user.phonenumbers.read';
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
            
            // Intentar obtener datos adicionales (birthday / phone) desde People API si el scope está habilitado
            // Esto es necesario porque el scope básico no incluye estos datos
            if ((env('GOOGLE_REQUEST_BIRTHDAY', false) || env('GOOGLE_REQUEST_PHONE', false)) && isset($googleUser->token)) {
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

        // Separar nombre y apellido a partir del nombre completo de Google
        [$firstName, $lastName] = $this->splitGoogleName($googleUser->name);
        $registerReq->name = $firstName;
        $registerReq->last_name = $lastName;
        $registerReq->email = $googleUser->email;
        $registerReq->password = Str::random(32);  // evita passwords débiles
        // Intentar obtener el teléfono desde los datos de Google
        $googlePhone = $this->getPhoneFromGoogle($googleUser);
        $registerReq->phone = $googlePhone ?: null;
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
     * Extrae el teléfono desde el objeto de usuario de Google, si está disponible.
     *
     * @param \Laravel\Socialite\Two\User $googleUser
     * @return string|null
     */
    private function getPhoneFromGoogle($googleUser): ?string
    {
        try {
            $phone = null;

            // 1) Campo directo en el objeto Socialite (por si algún driver lo mapea así)
            if (isset($googleUser->phone) && !empty($googleUser->phone)) {
                $phone = $googleUser->phone;
            }

            // 2) Revisar el array raw user que entrega Google (lo que llenamos desde People API)
            $rawUser = $googleUser->user ?? null;

            if (!$phone && is_array($rawUser)) {
                if (!empty($rawUser['phone'])) {
                    $phone = $rawUser['phone'];
                } elseif (!empty($rawUser['phoneNumber'])) {
                    $phone = $rawUser['phoneNumber'];
                } elseif (!empty($rawUser['phone_number'])) {
                    $phone = $rawUser['phone_number'];
                } elseif (!empty($rawUser['phoneNumbers']) && is_array($rawUser['phoneNumbers'])) {
                    // Recorremos todos los phoneNumbers buscando value o canonicalForm
                    foreach ($rawUser['phoneNumbers'] as $p) {
                        if (!empty($p['value'])) {
                            $phone = $p['value'];
                            break;
                        }
                        if (!empty($p['canonicalForm'])) {
                            $phone = $p['canonicalForm'];
                            break;
                        }
                    }
                }
            }

            if (is_string($phone)) {
                $phone = trim($phone);
            }

            return $phone ?: null;
        } catch (Throwable $e) {
            \Log::warning('Failed to get phone from Google user: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Separa un nombre completo en nombre y apellido.
     * Ej: "Juan Pérez Gómez" => ["Juan Pérez", "Gómez"]
     *
     * @param string|null $fullName
     * @return array{0:string,1:string}
     */
    private function splitGoogleName(?string $fullName): array
    {
        $fullName = trim((string) $fullName);
        if ($fullName === '') {
            return ['', ''];
        }

        $parts = preg_split('/\s+/', $fullName);
        if (!$parts || count($parts) === 1) {
            return [$fullName, ''];
        }

        $lastName = array_pop($parts);
        $firstName = implode(' ', $parts);

        return [$firstName, $lastName];
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

            // Si existe cliente usamos su género, si no, por defecto 1 para evitar error de integridad
            $existingGenderId = ($client && $client->gender_id) ? $client->gender_id : 1;
            $existingBirthday = $client ? $client->birthday : null;

            // Preparar el birthday: priorizar el de Google si está disponible, sino mantener el existente
            $finalBirthday = $birthday ?: $existingBirthday;

            // Siempre actualizar cuando el usuario se loguea
            // Si hay birthday de Google, usarlo; si no, mantener el existente
            $request = new Request();
            
            // Para el birthday: priorizar el de Google si está disponible, sino mantener el existente
            $birthdayToSave = $finalBirthday ?: $existingBirthday;
            
            $request->replace([
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

            // Llamar a People API para obtener información adicional (birthday / phone)
            // Pedimos siempre birthdays y phoneNumbers; la API solo devolverá lo que esté permitido por los scopes
            $fields = ['birthdays', 'phoneNumbers'];

            $response = Http::withToken($googleUser->token)
                ->timeout(10)
                ->get('https://people.googleapis.com/v1/people/me', [
                    'personFields' => implode(',', $fields)
                ]);

            if ($response->successful()) {
                $peopleData = $response->json();
                
                // Asegurarnos de tener el array user inicializado
                if (!isset($googleUser->user) || !is_array($googleUser->user)) {
                    $googleUser->user = [];
                }

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
                                
                                $googleUser->user['birthday'] = $birthday;
                                $googleUser->user['birthdays'] = $peopleData['birthdays'];
                                
                                break;
                            }
                        }
                    }
                }

                // Agregar phone al objeto googleUser si está disponible
                if (isset($peopleData['phoneNumbers']) && is_array($peopleData['phoneNumbers'])) {
                    foreach ($peopleData['phoneNumbers'] as $phoneData) {
                        // Formato típico: { "value": "+54 9 11 1234-5678", "type": "mobile", ... }
                        $candidate = $phoneData['value'] ?? ($phoneData['canonicalForm'] ?? null);
                        if (!empty($candidate)) {
                            $phone = trim($candidate);
                            $googleUser->user['phone'] = $phone;
                            $googleUser->user['phoneNumbers'] = $peopleData['phoneNumbers'];

                            break;
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            \Log::warning('Failed to fetch birthday from People API: ' . $e->getMessage());
        }
    }
}