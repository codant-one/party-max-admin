<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Carbon\Carbon;

use App\Models\PasswordReset;
use App\Models\User;

class PasswordResetController extends Controller
{
    
    /**
     * create reset password mail.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function forgot_password(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user)
            return response()->json([
                'success' => false,
                'message' => 'not_found',
                'errors' => 'Correo electrónico no registrado'
            ], 404);

        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $request->email],
            ['token' => Str::random(60)]
        );
        
        $email = $user->email;
        $subject = 'Cambio de contraseña';
        $domain = ($user->getRoleNames()[0] === 'Cliente') ? env('APP_DOMAIN') : env('APP_DOMAIN_ADMIN');
        $url = $domain.'/reset-password?token='.$passwordReset['token'].'&user='.$email;
        
        $info = [
            'buttonLink' =>  $url ?? null,
            'email' => 'emails.auth.forgot_pass_confirmation'
        ];     
        
        $responseMail = $this->sendMail($user->id, $info); 

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => [ "register_success" => $responseMail ]
        ], 200);
    }

    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();
        
        if (!$passwordReset)
            return response()->json([
                'success' => false,
                'message' => 'not_found',
                'errors' => 'El token de restablecimiento de contraseña no es válido'
            ], 404);
            
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return response()->json([
                'success' => false,
                'message' => 'not_found',
                'errors' => 'El token de restablecimiento de contraseña no es válido'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'result' => $passwordReset,
            'message' => 'token_success'
            
        ], 200);
    }

    public function change(Request $request) {
        if ($this->find($request->token)->status() != 200)
            return response()->json([
                'success' => false,
                'message' => 'not_found',
                'errors' => 'El token es invalido!'
            ], 404);

        $tokenValidated = json_decode($this->find($request->token)->content());
        $email = $tokenValidated->result->email;
        $user = User::where('email', $email)->first();

        if (!$user)
            return response()->json([
                'success' => false,
                'message' => 'not_found',
                'errors' => 'Correo electrónico no registrado'
            ], 404);

        $user->password = Hash::make($request->password);
        $user->token_2fa = null;
        $user->update();

        $info = [
            'buttonLink' => ($user->getRoleNames()[0] === 'Cliente') ? env('APP_DOMAIN') : env('APP_DOMAIN_ADMIN'),
            'email' => 'emails.auth.reset_password'
        ];     
        
        $responseMail = $this->sendMail($user->id, $info); 

        return response()->json([
            'success' => false,
            'message' => 'reset-password',
            'data' => 'La Contraseña ha sido actualizada'
        ], 200);

    }

    private function sendMail($id, $info ){

        $user = User::find($id);
        
        $data = [
            'title' => $info['title'],
            'user' => $user->name . ' ' . $user->last_name,
            'text' => $info['text'],
            'buttonLink' =>  $info['buttonLink'] ?? null,
            'buttonText' =>  $info['buttonText'] ?? null
        ];

        $clientEmail = $user->email;
        $subject = $info['subject'];
        
        try {
            \Mail::send($info['email'], $data, function ($message) use ($clientEmail, $subject) {
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    $message->to($clientEmail)->subject($subject);
            });

            return "Tu solicitud se ha procesado satisfactoriamente.";
        } catch (\Exception $e){
            return "Ocurrió un error, no se pudo enviar el correo electrónico. ".$e;
        }        

        return "";

    } 
}
