<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Province;

class ProvinceController extends Controller
{
    /**
    * @OA\Get(
    *     path="/provinces",
    *     summary="Provinces",
    *     description= "Get all Provinces",
    *     tags={"Provinces"},
    *     @OA\Response(
    *         @OA\MediaType(mediaType="application/json"),
    *         response=200,
    *         description="successful operation",
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
    public function index(Request $request)
    {
        $provinces = Province::all();

        return response()->json([
            'success' => true,
            'data' => $provinces
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
    public function store(Request $request)
    {
        //
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
    public function update(Request $request, string $id)
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

}
