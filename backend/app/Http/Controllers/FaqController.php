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
     * @OA\Get(
     *     path="/faqs",
     *     summary="Lists available FAQS",
     *     description= "Gets all available Faqs resources",
     *     tags={"Faqs"},
     *     security={{"bearerAuth": {} }},
     *     @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="Search Text",
     *          @OA\Schema(
     *              type="string",
     *              format="text",
     *              description="Search text to find users"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="orderByField",
     *          in="query",
     *          description="Order by field the query",
     *          @OA\Schema(
     *              type="string",
     *              format="text",
     *              description="Order by field the query"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="orderBy",
     *          in="query",
     *          description="Default query order 'asc'",
     *          @OA\Schema(
     *              type="string",
     *              format="text",
     *              description="Default query order asc"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          description="Number of rows per page",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *              description="number of rows per page"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Number of page",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *              description="number of page"
     *          )
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=500,
     *         description="an ""unexpected"" error"
     *     ),
     *  )
     *
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
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
        } catch(\Illuminate\Database\QueryException $ex) {
            return response()->json([
              'success' => false,
              'message' => 'database_error',
              'exception' => $ex->getMessage()
            ], 500);
        }
    }


    public function order(Request $request): JsonResponse
    {
        $query = Faq::orderBy('id')
                              ->get();

        foreach ($query as $faq) {
            $data = [
                'id' => $faq->id, 
                'title' => $faq->title, 
                'description' => $faq->description
            ];

            array_push($this->faqs, $data);
        }

        return response()->json([
            'faqs' => $this->faqs
        ]);

    }


    /**
     * @OA\Post(
     *     path="/faqs",
     *     summary="Create a new FAQ",
     *     description= "Create a new Faq",
     *     tags={"Faqs"},
     *     security={{"bearerAuth": {} }},
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"title","description"},
     *                  @OA\Property(
     *                      property="title",
     *                      type="string",
     *                      format= "text",
     *                      description="Faq title."
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      type="string",
     *                      format= "text",
     *                      description="Faq description."
     *                  )
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=400,
     *         description="Some was wrong"
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=500,
     *         description="an ""unexpected"" error"
     *     ),
     *  )
     *
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
     * @OA\Put(
     *     path="/faqs/{id}",
     *     summary="Update a FAQ",
     *     description= "Update a Faq",
     *     tags={"Faqs"},
     *     security={{"bearerAuth": {} }},
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The faq id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *              description="The unique identifier of a faq"
     *          )
     *     ),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"title","description"},
     *                  @OA\Property(
     *                      property="title",
     *                      type="string",
     *                      format= "text",
     *                      description="Faq title."
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      type="string",
     *                      format= "text",
     *                      description="Faq description."
     *                  )
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=400,
     *         description="Some was wrong"
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=500,
     *         description="an ""unexpected"" error"
     *     ),
     *  )
     *
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
     * @OA\Delete(
     *     path="/faqs/{id}",
     *     summary="Delete a FAQ",
     *     description= "Delete a Faq",
     *     tags={"Faqs"},
     *     security={{"bearerAuth": {} }},
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The faq id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *              description="The unique identifier of a faq"
     *          )
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         @OA\MediaType(mediaType="application/json"),
     *         response=500,
     *         description="an ""unexpected"" error"
     *     ),
     *  )
     *
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
}
