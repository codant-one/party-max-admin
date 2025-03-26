<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\Review;
use App\Models\Product;
use App\Models\OrderDetail;

class ReviewController extends Controller
{
    /**
    * Display a listing of the resource.
    */
    public function index(Request $request)
    {
        //
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

            $review = Review::createReview($request);

            Product::calculateRating($request->product_id);

            $details = 
                OrderDetail::with(['product_color.product'])
                        ->where('order_id', $request->order_id)
                        ->whereHas('product_color.product', function ($query) use ($request) {
                            $query->where('id', $request->product_id);
                        })
                        ->first();
                        
            if ($details) {
                $details->update(['is_rating' => 1]);
            }
            
            return response()->json([
                'success' => true,
                'data' => [ 
                    'review' => Review::find($review->id)
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
    public function update(Request $request, Review $review)
    {
        try {

            $review = Review::find($review->id);

            if (!$review)
                return response()->json([
                    'sucess' => false,
                    'message' => 'Not found',
                    'message' => 'Review no encontrado'
                ], 404);

            $review = $review->updateReview($request, $review);
            Product::calculateRating($request->product_id);

            $details = 
                OrderDetail::with(['product_color.product'])
                        ->where('order_id', $request->order_id)
                        ->whereHas('product_color.product', function ($query) use ($request) {
                            $query->where('id', $request->product_id);
                        })
                        ->first();

            if ($details) {
                $details->update(['is_rating' => 1]);
            }

            return response()->json([
                'success' => true,
                'data' => [ 
                    'review' => Review::find($review->id)
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
        //
    }

     /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request): JsonResponse
    {
        try {

            $review = Review::find($request->ids);

            if (!$review)
                return response()->json([
                    'sucess' => false,
                    'message' => 'Not found',
                    'message' => 'Review no encontrado'
                ], 404);

            Review::deleteReviews($request->ids);
            Product::calculateRating($request->product_id);
            
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

    public function reviewbyclient(Request $request, $id): JsonResponse
    {

        try {
            
            $product = Product::find($id);

            $review = Review::where([
                ['product_id', $id],
                ['client_id', $request->client_id]
            ])->first();

            return response()->json([
                'success' => true,
                'data' => [ 
                    'review' => $review,
                    'product' => $product
                ]
            ], 200);

        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'database_error',
                'exception' => $ex->getMessage()
            ], 500);
        }

    }

}
