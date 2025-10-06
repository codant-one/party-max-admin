<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\Review;
use App\Models\Product;
use App\Models\Service;
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

            if ($request->product_id) {                
                Product::calculateRating($request->product_id);

                $details = OrderDetail::with(['product_color.product'])
                    ->where('order_id', $request->order_id)
                    ->whereHas('product_color.product', function ($query) use ($request) {
                        $query->where('id', $request->product_id);
                    })
                    ->first();

                if ($details) {
                    $details->update(['is_rating' => 1]);
                }
            }

            if ($request->service_id) {
                Service::calculateRating($request->service_id);

                $details = OrderDetail::where('order_id', $request->order_id)
                    ->where('service_id', $request->service_id)
                    ->first();
                if ($details) {
                    $details->update(['is_rating' => 1]);
                }
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
            
            if ($request->has('product_id') && $request->product_id) {
                Product::calculateRating($request->product_id);

                $details = OrderDetail::with(['product_color.product'])
                    ->where('order_id', $request->order_id)
                    ->whereHas('product_color.product', function ($query) use ($request) {
                        $query->where('id', $request->product_id);
                    })
                    ->first();

                if ($details) {
                    $details->update(['is_rating' => 1]);
                }
            }

            if ($request->has('service_id') && $request->service_id) {
                Service::calculateRating($request->service_id);

                $details = OrderDetail::where('order_id', $request->order_id)
                    ->where('service_id', $request->service_id)
                    ->first();
                if ($details) {
                    $details->update(['is_rating' => 1]);
                }
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

            $reviews = Review::find($request->ids);

            if (!$reviews)
                return response()->json([
                    'sucess' => false,
                    'message' => 'Not found',
                    'message' => 'Review no encontrado'
                ], 404);

            $productIds = [];
            $serviceIds = [];

            foreach ($reviews as $review) {
                if ($review->product_id) {
                    $productIds[] = $review->product_id;
                }
                if ($review->service_id) {
                    $serviceIds[] = $review->service_id;
                }
            }

            Review::deleteReviews($request->ids);

            // Actualiza el rating de cada producto afectado
            foreach (array_unique($productIds) as $productId) {
                Product::calculateRating($productId);
            }

            // Actualiza el rating de cada servicio afectado
            foreach (array_unique($serviceIds) as $serviceId) {
                Service::calculateRating($serviceId);
            }
            
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
            $service = Service::find($id); // <-- busca el servicio por ID

            $review = Review::where(function ($query) use ($id, $request) {
                $query->where('client_id', $request->client_id)
                    ->where(function ($q) use ($id) {
                        $q->where('product_id', $id)
                            ->orWhere('service_id', $id);
                    });
            })->first();


            return response()->json([
                'success' => true,
                'data' => [ 
                    'review' => $review,
                    'product' => $product,
                    'service' => $service
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
