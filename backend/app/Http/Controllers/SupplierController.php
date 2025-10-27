<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\SupplierRequest;

use Illuminate\Support\Str;

use Spatie\Permission\Middlewares\PermissionMiddleware;


use App\Models\User;
use App\Models\UserRegisterToken;
use App\Models\Supplier;
use App\Models\SupplierAccount;
use App\Models\UserDetails;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver proveedores|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear proveedores|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar proveedores|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar proveedores|administrador')->only(['destroy']);
    }

    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;
        
            $query = Supplier::with(['user.userDetail.province.country', 'user.products', 'gender', 'document.type', 'account'])
                        ->productsCount()
                        ->servicesCount()
                        ->sales()
                        ->services()
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

            $suppliers = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'suppliers' => $suppliers,
                    'suppliersTotalCount' => $count
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
    public function store(SupplierRequest $request)
    {
        try {

            $password = Str::random(8);
            $request->merge(['password' => $password]);

            $supplier = Supplier::createSupplier($request);

            UserRegisterToken::updateOrCreate(
                ['user_id' => $supplier->user_id],
                ['token' => Str::random(60)]
            );

            $email = $supplier->user->email;
            $subject = 'Bienvenido a PARTYMAX';
            $url = env('APP_DOMAIN');
            
            $data = [
                'title' => 'Cuenta creada satisfactoriamente!!!',
                'user' => $supplier->user->name . ' ' . $supplier->user->last_name,
                'email'=> $email,
                'user_name' => $supplier->user->username,
                'password' => $password,
                'url'=> env("APP_DOMAIN_ADMIN").'/login',
                'text-url'=>'Panel administrativo'
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
                $responseMail = 'Correo electrónico enviado al proveedor satisfactoriamente.';
            } catch (\Exception $e){
                $message = 'error';
                $responseMail = $e->getMessage();
            } 

            return response()->json([
                'success' => true,
                'email_response' => $responseMail,
                'data' => [ 
                    'supplier' => Supplier::with(['user.userDetail.province.country', 'user.products', 'gender', 'document.type', 'account'])->find($supplier->id)
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

            $supplier = Supplier::with([
                                    'user.userDetail.province.country',
                                    'user.userDetail.document_type', 
                                    'user.products', 
                                    'gender', 
                                    'document.type', 
                                    'account'
                                ])
                                ->productsCount()
                                ->servicesCount()
                                ->sales()
                                ->services()
                                ->retailSales()
                                ->wholesaleSales()
                                ->salesNotInvoice()
                                ->retailSalesNotInvoice()
                                ->wholesaleSalesNotInvoice()
                                ->servicesNotInvoice()
                                ->salesInvoice()
                                ->retailSalesInvoice()
                                ->wholesaleSalesInvoice()
                                ->servicesInvoice()
                                ->salesInvoicePaid()
                                ->retailSalesInvoicePaid()
                                ->wholesaleSalesInvoicePaid()
                                ->servicesInvoicePaid()
                                ->salesInvoiceNotPaid()
                                ->retailSalesInvoiceNotPaid()
                                ->wholesaleSalesInvoiceNotPaid()
                                ->servicesInvoiceNotPaid()
                                ->find($id);

            if (!$supplier)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Proveedor no encontrado'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'supplier' => $supplier
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
    public function update(SupplierRequest $request, $id): JsonResponse
    {
        try {

            $supplier = Supplier::with(['user.userDetail.province.country', 'user.products', 'gender', 'document.type', 'account'])->find($id);
        
            if (!$supplier)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Proveedor no encontrado'
                ], 404);

            $supplier->updateSupplier($request, $supplier); 

            return response()->json([
                'success' => true,
                'data' => [ 
                    'supplier' => $supplier
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

            $supplier = Supplier::find($id);
        
            if (!$supplier)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Proveedor no encontrado'
                ], 404);
            
            $supplier->deleteSupplier($id);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'supplier' => $supplier
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

    public function updateCommission(Request $request, $id)
    {
        try {
            $supplier = Supplier::find($id);

            if (!$supplier)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Proveedor no encontrado'
                ], 404);
            
            $supplier->updateCommission($request, $supplier);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'supplier' => $supplier
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

    public function updateBalance(Request $request, $id)
    {
        try {
            $supplierAccount = SupplierAccount::where('supplier_id',$id)->first();

            if (!$supplierAccount)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Proveedor no encontrado'
                ], 404);
            
            $supplierAccount->updateBalance($request, $supplierAccount);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'supplierAccount' => $supplierAccount
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
