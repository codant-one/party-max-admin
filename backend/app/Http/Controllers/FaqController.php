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
        $this->middleware(PermissionMiddleware::class . ':eliminar faqs|administrador')->only(['delete']);
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->has('limit') ? $request->limit : 10;
    
        $query = Faq::applyFilters(
                                $request->only([
                                    'search',
                                    'orderByField',
                                    'orderBy'
                                ])
                            );

        $faqs = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);
        
        $count = $query->count();

        return response()->json([
            'faqs' => $faqs,
            'faqsTotalCount' => $count
        ]);
    }


    public function order(Request $request): JsonResponse
    {
        $query = Faq::orderBy('id')
                              ->get();

        foreach ($query as $faq) {
            $data = [
                'id' => $faq->id, 
                'name' => $faq->title, 
                'description' => $faq->description
            ];

            array_push($this->faqs, $data);
        }

        return response()->json([
            'faqs' => $this->faqs
        ]);

    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(FaqRequest $request): JsonResponse
    {
        $faq = Faq::createFaq($request);

        return response()->json([
            'faq' => Faq::find($faq->id),
            'success' => true
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $faq = Faq::find($id);

        if (!$faq)
            return response()->json([
                'sucess' => false,
                'message' => 'Not found'
            ], 404);

        return response()->json([
            'success' => true,
            'faq' => $faq,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FaqRequest $request, Faq $faq): JsonResponse
    {
        $faq = $faq->updateFaq($request, $faq);

        return response()->json([
            'faq' => Faq::find($faq->id),
            'success' => true
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request): JsonResponse
    {
        Faq::deleteFaq($request->ids);

        return response()->json([
            'success' => true
        ]);
    }
}
