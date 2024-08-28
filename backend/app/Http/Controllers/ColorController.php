<?php

namespace App\Http\Controllers;
use App\Http\Requests\ColorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Color;

class ColorController extends Controller
{
    /**
    * Display a listing of the resource.
    */
    public function index(Request $request)
    {
        $colors = Color::all();

        return response()->json([
            'success' => true,
            'data' => $colors
        ], 200);
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
    public function store(ColorRequest $request): JsonResponse
    {
        try {

            $color = Color::createColor($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'color' => Color::find($color->id)
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
        try {

            $color = Color::find($id);

            if (!$color)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Color no encontrado'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'color' => $color
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            $color = Color::find($id);
        
            if (!$color)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Color no encontrado'
                ], 404);

            $color = $color->updateColor($request, $color);

            return response()->json([
                'success' => true,
                'data' => [
                    'color' => Color::find($color->id)
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
    public function destroy(string $id)
    {
        try {

            $color = Color::find($id);
        
            if (!$color)
                return response()->json([
                    'success' => false,
                    'feedback' => 'not_found',
                    'message' => 'Color no encontrado'
                ], 404);

            $color->deleteColor($id);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'color' => $color
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
