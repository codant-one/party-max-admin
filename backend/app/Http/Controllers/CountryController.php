<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;

class CountryController extends Controller
{
    /**
    * @OA\Get(
    *     path="/countries",
    *     summary="Countries",
    *     description= "Get all Countries",
    *     tags={"Countries"},
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
        $countries = Country::all();

        return response()->json([
            'success' => true,
            'data' => $countries
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
