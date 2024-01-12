<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\Client;
use App\Models\User;
use App\Models\UserRegisterToken;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver clientes|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear clientes|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar clientes|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar clientes|administrador')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;
        
            $query = Client::with(['user.userDetail.province.country', 'gender'])
                        ->applyFilters(
                            $request->only([
                                'search',
                                'orderByField',
                                'orderBy'
                            ])
                        );

            $count = $query->applyFilters(
                        $request->only([
                            'search',
                            'orderByField',
                            'orderBy'
                        ])
                    )->count();

            $clients = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'clients' => $clients,
                    'clientsTotalCount' => $count
                ]
            ]);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
              'success' => false,
              'message' => 'database_error',
              'exception' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClientRequest $request)
    {
        try {
            $password = Str::random(8);
            $request->merge([ 'password' => $password ]);

            $client = Client::createClient($request);
            $client = Client::with(['user'])->find($client->id);

            //Crear o Actualizar token.
            $registerConfirm = UserRegisterToken::updateOrCreate(
                ['user_id' => $client->user->id],
                ['token' => Str::random(60)]
            );

            $username = $client->user->username;

            $email = $client->user->email;
            $subject = 'Bienvenido a PARTYMAX';
            $url = 'https://staging.partymax.co/';
            
            $data = [
                'title' => 'Cuenta creada satisfactoriamente!!!',
                'user' => $client->user->name . ' ' . $client->user->last_name,
                'user_name' =>$username,
                'password'=>$password,
            ];
            
            try {
                \Mail::send(
                    'emails.auth.client_created'
                    , ['data' => $data]
                    , function ($message) use ($email, $subject) {
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                        $message->to($email)->subject($subject);
                });

                $message = 'send_email';
                $responseMail = 'Correo electrónico enviado al cliente satisfactoriamente.';
            } catch (\Exception $e){
                $message = 'error';
                $responseMail = $e->getMessage();
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

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {

            $client = Client::with(['user.userDetail.province.country', 'gender'])->find($id);

            if (!$client)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Cliente no encontrado'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'client' => $client
                ]
            ]);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClientRequest $request, $id): JsonResponse
    {
        try {

            $client = Client::with(['user.userDetail.province.country', 'gender'])->find($id);
        
            if (!$client)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Cliente no encontrado'
                ], 404);

            $client->updateClient($request, $client); 

            return response()->json([
                'success' => true,
                'data' => [ 
                    'client' => $client
                ]
            ], 200);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {

            $client = Client::find($id);
        
            if (!$client)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Cliente no encontrado'
                ], 404);
            
            $client->deleteClient($id);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'client' => $client
                ]
            ], 200);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }
}
