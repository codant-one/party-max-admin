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
        $subject = 'Solicitud de Renovación de Contraseña';
        $url = env('APP_DOMAIN').'/reset-password?token='.$passwordReset['token'].'&user='.$email;
        
        $data = [
            'title' => 'Hemos recibido una solicitud para renovar Contraseña',
            'user' => $user->name . ' ' . $user->last_name,
            'text' => 'PARTYMAX te informa, que hemos recibido tu solicitud para renovar tu Contraseña.
            <br><br>
            Por favor confirma dicha solicitud haciendo clic en el enlace a continuación: ',
            'buttonLink' =>  $url ?? null,
            'buttonText' => 'Confirmar Renovación de Contraseña' 
        ];
        
        try {
            \Mail::send(
                'emails.auth.forgot_pass_confirmation'
                , $data
                , function ($message) use ($email, $subject) {
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    $message->to($email)->subject($subject);
            });

            $message = 'send_email';
            $responseMail = 'Tu solicitud se ha procesado satisfactoriamente.';
        } catch (\Exception $e){
            $message = 'error';
            $responseMail = 'Correo electrónico y usuario no registrados';//.$e->getMessage();
        }        

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => ["register_success" => $responseMail]
        ], 200);
    }

    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();
        
        if (!$passwordReset)
            return response()->json([
                'success' => false,
                'message' => 'not_found',
                'errors' => 'El token de reestablecimiento de contraseña es invalido'
            ], 404);
            
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return response()->json([
                'success' => false,
                'message' => 'not_found',
                'errors' => 'El token de reestablecimiento de contraseña es invalido'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'result' => $passwordReset,
            'message' => 'token_success'
            
        ], 200);
    }

    public function change(Request $request)
    {
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

        return response()->json([
            'success' => false,
            'message' => 'reset-password',
            'data' => 'La Contraseña ha sido actualizada'
        ], 200);

    }
}
