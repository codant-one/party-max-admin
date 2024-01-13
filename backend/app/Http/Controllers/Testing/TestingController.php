<?php

namespace App\Http\Controllers\Testing;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

use App\Models\User;

class TestingController extends Controller
{
    public function permissions()
    {
        $permissions = Permission::all();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ], 200);
    }

    public function emails()
    {
        $url = env('APP_DOMAIN').'/register-confirm?&token='.Str::random(60);
        $info = [
            'title' => 'Verificar Correo Electrónico',
            'text' => 'Tu cuenta no está verificada. Confirma tu cuenta con los pasos a seguir para verificarla.',
            'buttonLink' => $url,
            'buttonText' => 'Confirmar'
        ];

        $user = User::find(1);
        
        $data = [
            'title' => $info['title'],
            'user' => $user->name . ' ' . $user->last_name,
            'text' => $info['text'],
            'buttonLink' =>  $info['buttonLink'] ?? null,
            'buttonText' =>  $info['buttonText'] ?? null
        ];

        return view('emails.auth.testing', compact('data'));
    }

}
