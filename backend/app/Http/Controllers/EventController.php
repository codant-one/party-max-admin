<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Requests\EventRequest;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use Carbon\Carbon;

use App\Models\Event;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Order;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
        $this->middleware(PermissionMiddleware::class . ':ver calendario|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':eliminar calendario|administrador')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {   
        try {

            $user = Auth()->user();
            $permissions = $user->getPermissionsViaRoles()->pluck('name')->toArray();
            $isAdmin = in_array('administrador', $permissions);
            $idAdmin = ($isAdmin) ? $user->id : 0;

            $names = explode(',', $request->calendars);
            $users = explode(',', $request->users);

            $query = Event::with([
                            'category', 
                            'order_detail.service.cupcakes',
                            'order_detail.service.user',
                            'order_detail.flavor',
                            'order_detail.filling',
                            'order_detail.cake_size',
                            'order_detail.order_file',
                            'order_detail.order.client.user.userDetail',
                            'order_detail.order.billing', 
                            'order_detail.order.address.province',
                            'order_detail.order.province',
                            'order_detail.order.payment'
                          ])
                          ->whereHas('category', function ($query) use ($names){
                                foreach ($names as $key => $name) {
                                    if($key === 0)
                                        $query->where('name', 'LIKE', "%{$name}%");
                                    else
                                        $query->orWhere('name', 'LIKE', "%{$name}%"); 
                                }
                                return $query;
                           });

            if($isAdmin)
                $events = $query->whereHas('order_detail', function ($q) use ($users) {
                    $q->whereHas('service', function ($q) use ($users){
                        return $q->whereIn('user_id', $users)
                                 ->orderBy('user_id');
                    });
                })->get();
            else
                $events = $query->whereHas('order_detail', function ($q) {
                    $q->whereHas('service', function ($q) {
                        return $q->where('user_id', Auth()->user()->id)
                                 ->orderBy('user_id');
                    });
                })->get();

            $data = [];

            foreach($events as $event){

                $eventArray = [
                    'id' => $event['id'],
                    'title' => $event['title'],
                    'start' => $event['start_date'],
                    'end' => $event['end_date'],
                    'allDay' => true,
                    'delta' => 0,
                    'extendedProps' => [
                        'state_id' => $event['state_id'],
                        'calendar' => $event['category']['name'],
                        'order_detail' => $event['order_detail'],
                        'description' => $event['description'],
                    ]
                ];

                array_push($data, $eventArray);
            }

            return response()->json([
                'success' => true,
                'isAdmin' => $isAdmin,
                'idAdmin' => $idAdmin,
                'data' => (strlen($request->calendars) === 0) ? [] : $data
            ], 200);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
              'success' => false,
              'message' => 'database_error',
              'exception' => $ex->getMessage()
            ], 500);
        }
    }
    
    public function events(Request $request): JsonResponse
    {   
        try {

            $user = Auth()->user();
            $permissions = $user->getPermissionsViaRoles()->pluck('name')->toArray();
            $isAdmin = in_array('administrador', $permissions);
            $idAdmin = ($isAdmin) ? $user->id : 0;

            $names = explode(',', $request->calendars);

            $query = Event::with([
                            'category', 
                            'order_detail.service.cupcakes',
                            'order_detail.service.user',
                            'order_detail.flavor',
                            'order_detail.filling',
                            'order_detail.cake_size',
                            'order_detail.order_file',
                            'order_detail.order.client.user.userDetail',
                            'order_detail.order.billing', 
                            'order_detail.order.address.province',
                            'order_detail.order.province',
                            'order_detail.order.payment'
                          ])
                          ->whereHas('category', function ($query) use ($names){
                                foreach ($names as $key => $name) {
                                    if($key === 0)
                                        $query->where('name', 'LIKE', "%{$name}%");
                                    else
                                        $query->orWhere('name', 'LIKE', "%{$name}%"); 
                                }
                                return $query;
                           });

            $data = [];

            foreach($events as $event){

                $eventArray = [
                    'id' => $event['id'],
                    'title' => $event['title'],
                    'start' => $event['start_date'],
                    'end' => $event['end_date'],
                    'allDay' => true,
                    'delta' => 0,
                    'extendedProps' => [
                        'state_id' => $event['state_id'],
                        'calendar' => $event['category']['name'],
                        'order_detail_id' => $event['order_detail_id'],
                        'description' => $event['description'],
                    ]
                ];

                array_push($data, $eventArray);
            }

            return response()->json([
                'success' => true,
                'isAdmin' => $isAdmin,
                'idAdmin' => $idAdmin,
                'data' => (strlen($request->calendars) === 0) ? [] : $data
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventRequest $request): JsonResponse
    {
        try{
            
            $event = Event::createEvent($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'event' => Event::with([
                        'category', 
                        'order_detail.service.cupcakes',
                        'order_detail.service.user',
                        'order_detail.flavor',
                        'order_detail.filling',
                        'order_detail.cake_size',
                        'order_detail.order_file',
                        'order_detail.order.client.user.userDetail',
                        'order_detail.order.billing', 
                        'order_detail.order.address.province',
                        'order_detail.order.province',
                        'order_detail.order.payment'
                    ])->find($event->id)
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
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {

            $event = Event::with([
                'category', 
                'order_detail.service.cupcakes',
                'order_detail.service.user',
                'order_detail.flavor',
                'order_detail.filling',
                'order_detail.cake_size',
                'order_detail.order_file',
                'order_detail.order.client.user.userDetail',
                'order_detail.order.billing', 
                'order_detail.order.address.province',
                'order_detail.order.province',
                'order_detail.order.payment'
            ])->find($id);
        
            if (!$event)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Tarea no encontrada'
                ], 404);
            
            return response()->json([
                'success' => true,
                'data' => [ 
                    'event' => $event
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventRequest $request, $id): JsonResponse
    {
        try {

            $event = Event::find($id);
        
            if (!$event)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Tarea no encontrada'
                ], 404);

            $event->updateEvent($request, $event); 

            $event = Event::with([
                'category', 
                'order_detail.service.cupcakes',
                'order_detail.service.user',
                'order_detail.flavor',
                'order_detail.filling',
                'order_detail.cake_size',
                'order_detail.order_file',
                'order_detail.order.client.user.userDetail',
                'order_detail.order.billing', 
                'order_detail.order.address.province',
                'order_detail.order.province',
                'order_detail.order.payment'
            ])->find($id);

            $eventArray = [
                'id' => $event['id'],
                'title' => $event['title'],
                'start' => $event['start_date'],
                'end' => $event['end_date'],
                'allDay' => true,
                'delta' => 0,
                'extendedProps' => [
                    'state_id' => $event['state_id'],
                    'calendar' => $event['category']['name'],
                    'order_detail_id' => $event['order_detail_id'],
                    'description' => $event['description'],
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => [ 
                    'event' => $eventArray
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
    public function destroy($id): JsonResponse {
        try {

            Event::deleteEvents($id);

            return response()->json([
                'success' => true
            ], 200);

        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }
    }

    public function deleteBatch(Request $request): JsonResponse
    {
        $batch = $request->params['batch'];

        foreach($batch as $id){
            Event::deleteEvents($id);
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function getUsers(): JsonResponse
    {
        $availableUsers = [];
        $users = User::with(['userDetail', 'supplier'])->get();

        foreach($users as $key => $user){
            $permissions = $user->getPermissionsViaRoles()->pluck('name')->toArray();
            $isCalendar = (in_array('administrador', $permissions) || in_array('ver calendario', $permissions));

            unset($users[$key]['roles']);

            if($isCalendar){
                array_push($availableUsers, $users[$key]);
            }
        }

        return response()->json([
            'success' => true,
            'users' => $availableUsers
        ]);
    }
}
