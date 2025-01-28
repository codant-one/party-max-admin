<?php

namespace App\Http\Controllers\Shopping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

use Carbon\Carbon;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductLike;
use App\Models\Service;
use App\Models\ServiceLike;

class FavoriteController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 5;

            $favoritesProduct = ProductLike::with(['user', 'product'])
                ->where('user_id', $request->user_id)
                ->get()
                ->map(function ($item) {
                    $product = $item->product;
                    $product->is_product = 1;
                    $product->created_at = Carbon::parse($item->created_at);
                    return $product;
                });

            $favoritesService = ServiceLike::with(['user', 'service.cupcakes'])
                ->where('user_id', $request->user_id)
                ->get()
                ->map(function ($item) {
                    $service = $item->service;
                    $service->is_product = 0;
                    $service->created_at = Carbon::parse($item->created_at);
                    return $service;
                });

            $favorites = $favoritesProduct->merge($favoritesService)
                ->sortByDesc(function ($item) {
                    return $item->created_at->timestamp;
                })
                ->values();

            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $pagedFavorites = $favorites->slice(($currentPage - 1) * $limit, $limit)->values();

            $favoritesPaginated = new LengthAwarePaginator(
                $pagedFavorites,
                $favorites->count(),
                $limit,
                $currentPage,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'favorites' => $favoritesPaginated,
                    'favoritesTotalCount' => $favorites->count()
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

    public function add(Request $request): JsonResponse
    {

        try {

            if($request->product_id)
                $favorite = ProductLike::addFavorite($request);
            else
                $favorite = ServiceLike::addFavorite($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'favorite' => $favorite
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

    public function delete(Request $request): JsonResponse
    {
        try {

            if($request->product_id)
                $favorite = ProductLike::deleteFavorite($request);
            else
                $favorite = ServiceLike::deleteFavorite($request);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'favorite' => $favorite
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

    public function show(Request $request): JsonResponse
    {

        try {

            if($request->product_id)
                $favorite = ProductLike::where([
                    ['user_id', $request->user_id],
                    ['product_id', $request->product_id]]
                )->first();
            else
                $favorite = ServiceLike::where([
                    ['user_id', $request->user_id],
                    ['service_id', $request->service_id]]
                )->first();

            return response()->json([
                'success' => true,
                'data' => [ 
                    'favorite' => $favorite ? true : false
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
