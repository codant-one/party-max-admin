<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\Quote;
use App\Models\QuoteDetail;

class QuoteController extends Controller
{

    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver cotizaciones|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':eliminar cotizaciones|administrador')->only(['delete']);
    }

    /**
    * Display a listing of the resource.
    */
    public function index(Request $request)
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;

            $query = Quote::with(['document_type']);
                   
            $count = $query->count();

            $quotes = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'quotes' => $quotes,
                    'quotesTotalCount' => $count
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $quote = Quote::createQuote($request);
            
            return response()->json([
                'success' => true,
                'data' => [ 
                    'quote' => Quote::find($quote->id)
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
    public function show(string $id)
    {
        //
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
    public function update(Request $request, Quote $quote)
    {
       //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

     /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request): JsonResponse
    {
        try {

            $quote = Quote::find($request->ids);

            if (!$quote)
                return response()->json([
                    'sucess' => false,
                    'message' => 'Not found',
                    'message' => 'Cotización no encontrada'
                ], 404);

            Quote::deleteQuotes($request->ids);
            
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
}
