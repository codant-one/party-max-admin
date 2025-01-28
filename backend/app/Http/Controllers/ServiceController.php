<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest;
use App\Http\Requests\StatusServiceRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\Service;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\ServiceList;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver servicios|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear servicios|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar servicios|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar servicios|administrador')->only(['delete']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? ($request->limit === 'Todos' ? -1 : $request->limit) : 10;
        
            $query = Service::with([
                            'categories.category', 
                            'images',
                            'user.userDetail',
                            'user.supplier',
                            'state',
                            'tags',
                            'cupcakes.cake_size.cake_type'
                        ])
                        ->order($request->category_id)
                        ->selling()
                        ->salesPrice()
                        ->favorites()
                        // ->comments()
                        ->applyFilters(
                            $request->only([
                                'search',
                                'orderByField',
                                'orderBy',
                                'favourite',
                                'archived',
                                'discarded',
                                'state_id',
                                'category_id',
                                'supplierId'
                            ])
                        )
                        ->withTrashed();
            
            $count = $query->count();
            
            $services = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'services' => $services,
                    'servicesTotalCount' => $count
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
    public function store(ServiceRequest $request): JsonResponse
    {
        try {

            $service = Service::createService($request);

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'services/main/';

                $file_data = uploadFile($image, $path);

                $service->image = $file_data['filePath'];
                $service->update();
            }

            $order_id = Service::latest('order_id')->first()->order_id ?? 0;

            $service->order_id = $order_id + 1;
            $service->update();

            return response()->json([
                'success' => true,
                'data' => [
                    'service' => Service::find($service->id)
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
     * Display the specified resource.
     */
    public function show($id)
    {
        try {

            $service = Service::with([
                'categories.category', 
                'images',
                'user.userDetail',
                'user.supplier',
                'state',
                'tags',
                'cupcakes.cake_size.cake_type'
            ])->find($id);

            if (!$service)
                return response()->json([
                    'sucess' => false,
                    'message' => 'Not found',
                    'message' => 'Servicio no encontrado'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'service' => $service
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
    public function update(ServiceRequest $request, Service $service): JsonResponse
    {
        try {

            $service = Service::with([
                'categories.category', 
                'images',
                'user.userDetail',
                'user.supplier',
                'state',
                'tags'
            ])->find($service->id);

            if (!$service)
                return response()->json([
                    'sucess' => false,
                    'message' => 'Not found',
                    'message' => 'Servicio no encontrado'
                ], 404);

            $service = $service->updateService($request, $service);

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'services/main/';

                $file_data = uploadFile($image, $path, $service->image);

                $service->image = $file_data['filePath'];
                $service->update();
            } 

            return response()->json([
                'success' => true,
                'data' => [ 
                    'service' => Service::find($service->id)
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
     * Remove the specified resource from storage.
     */
    public function delete(Request $request): JsonResponse
    {
        try {

            $service = Service::find($request->ids);

            if (!$service)
                return response()->json([
                    'sucess' => false,
                    'message' => 'Not found',
                    'message' => 'Servicio no encontrado'
                ], 404);

            Service::deleteServices($request->ids);

            return response()->json([
                'success' => true
            ]);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

    public function updateStatus(StatusServiceRequest $request, $id): JsonResponse
    {
        try {

            $service = Service::find($id);
        
            if (!$service)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Servicio no encontrado'
                ], 404);

            $field = $request->has('favourite')
                     ? 'favourite'
                     : ($request->has('discarded') ? 'discarded' : ($request->has('archived') ? 'archived' : null));

            $service->updateStatusService($field, $service); 

            return response()->json([
                'success' => true,
                'data' => [ 
                    'service' => $service
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

    public function uploadImage(Request $request): JsonResponse 
    {
        try {

            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $path = 'services/';

                $file_data = uploadFile($image, $path);

                return response()->json([
                    'success' => true,
                    'url' => $file_data['filePath']
                ], 200);
            }

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }

    }

    public function updateStates(Request $request, $id): JsonResponse
    {
        try {

            $service = Service::find($id);
        
            if (!$service)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Servicio no encontrado'
                ], 404);

            $service->updateStatesService($request, $service); 

            return response()->json([
                'success' => true,
                'data' => [ 
                    'service' => $service
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
     * update the order_id.
     */
    public function updateOrder(Request $request): JsonResponse
    { 
        $countServices = 1;

        foreach($request->all() as $serviceRequest){

            if($serviceRequest['category_id'] != '')
                ServiceList::updateOrCreate(
                    [ 
                        'service_id' => $serviceRequest['id'],
                        'category_id' => $serviceRequest['category_id']
                    ],
                    [ 'order_id' => $countServices++ ]
                );
            else 
                Service::updateOrCreate(
                    [ 
                        'id' => $serviceRequest['id'],
                        'name' => $serviceRequest['name']
                    ],
                    [ 'order_id' => $countServices++ ]
                );
        }

        return response()->json([
            'success' => 1
        ]);
    }
}
