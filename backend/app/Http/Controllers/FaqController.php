<?php

namespace App\Http\Controllers;

use App\Http\Requests\FaqRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\Faq;

class FaqController extends Controller
{
    protected $faqs;

    public function __construct()
    {
        $this->faqs = [];

        $this->middleware(PermissionMiddleware::class . ':ver faqs|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear faqs|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar faqs|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar faqs|administrador')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;
        
            $query = Faq::with(['category'])
                        ->applyFilters(
                            $request->only([
                                'search',
                                'orderByField',
                                'orderBy',
                                'category_id'
                            ])
                        );

            $count = $query->applyFilters(
                        $request->only([
                            'search',
                            'orderByField',
                            'orderBy',
                            'category_id'
                        ])
                    )->count();

            $faqs = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'faqs' => $faqs,
                    'faqsTotalCount' => $count
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
    public function store(FaqRequest $request): JsonResponse
    {
        try {

            $faq = Faq::createFaq($request);

            $order_id = Faq::where('faq_category_id', $faq->faq_category_id)
                           ->latest('order_id')
                           ->first()
                           ->order_id ?? 0;

            $faq->order_id = $order_id + 1;
            $faq->update();

            return response()->json([
                'success' => true,
                'data' => [ 
                    'faq' => Faq::with(['category'])->find($faq->id)
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

            $faq = Faq::with(['category'])->find($id);

            if (!$faq)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Faq no encontrado'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'faq' => $faq
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

    /*
     *
     * Update the specified resource in storage.
     */
    public function update(FaqRequest $request, $id): JsonResponse
    {
        try {

            $faq = Faq::find($id);
        
            if (!$faq)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Faq no encontrado'
                ], 404);

            $faq = $faq->updateFaq($request, $faq);

            return response()->json([
                'success' => true,
                'data' => [
                    'faq' => Faq::with(['category'])->find($faq->id)
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


    /*
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {

            $faq = Faq::find($id);
        
            if (!$faq)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Faq no encontrado'
                ], 404);

            $faq->deleteFaq($id);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'faq' => $faq
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
        $countFaqs = 1;

        foreach($request->all() as $faqRequest){
            Faq::updateOrCreate(
                [ 
                    'id' => $faqRequest['id'],
                    'faq_category_id' => $faqRequest['faq_category_id']
                ],
                [ 'order_id' => $countFaqs++ ]
            );
        }

        return response()->json([
            'success' => 1
        ]);
    }
}
