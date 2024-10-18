<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReferralRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\Referral;
use App\Models\ReferralDetail;
use App\Models\Product;

class ReferralController extends Controller
{
    public function __construct()
    {
        $this->middleware(PermissionMiddleware::class . ':ver remisiones|administrador')->only(['index', 'show']);
        $this->middleware(PermissionMiddleware::class . ':ver stock|administrador')->only(['products']);
        $this->middleware(PermissionMiddleware::class . ':editar remisiones|administrador')->only(['update']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;

            $query = Referral::with(['user.supplier.document.type'])->withCount(['details']);
                   
            $count = $query->count();

            $referrals = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'referrals' => $referrals,
                    'referralsTotalCount' => $count
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

    public function products(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;

            $query = Product::with([
                        'colors.categories.category', 
                        'colors.images', 
                        'colors.color', 
                        'colors.referral.referral',
                        'detail', 
                        'user.userDetail',
                        'user.supplier',
                        'state',
                        'tags'
                    ])
                    ->whereHas('colors.referral.referral', function ($q) {
                        $q->where('delivered', 0);
                    })
                    ->order($request->category_id)
                    ->applyFilters(
                        $request->only([
                            'search',
                            'orderByField',
                            'orderBy',
                            'category_id',
                            'supplierId'
                        ])
                    );

            $count = $query->applyFilters(
                        $request->only([
                            'search',
                            'orderByField',
                            'orderBy'
                        ])
                    )
                    ->count();

            $referrals = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'referrals' => $referrals,
                    'referralsTotalCount' => $count
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
    public function store(ReferralRequest $request): JsonResponse
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /*
     *
     * Update the specified resource in storage.
     */
    public function update(ReferralRequest $request, $id): JsonResponse
    {
        //
    }


    /*
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
       //
    }

    public function updateProducts(Request $request)
    {
        try {

            ReferralDetail::updateOrCreate(
                [   
                    'referral_id' => $request->id,
                    'product_color_id' => $request->product_color_id
                ],
                [ 
                    'quantity' => $request->quantity
                ]
            );  

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

    public function userDetails(Request $request)
    {
        try {
            $products = 
                Referral::with([
                        'user.supplier.document.type',
                        'details.color.product.user.userDetail',
                        'details.color.color',
                        'details.color.images'
                    ])
                    ->withSum('details', 'quantity')
                    ->find($request->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $products,
                    'total' => $products->details_sum_quantity
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

    public function uploadProducts(Request $request)
    {
        try {

            foreach(json_decode($request->products, true) as $product) {
                ReferralDetail::updateOrCreate(
                    [   
                        'referral_id' => $request->id,
                        'product_color_id' => $product['product_color_id']
                    ],
                    [ 
                        'quantity' => intval($product['quantity']),
                        'delivered' => intval($product['quantity'])
                    ]
                );

                $product_ = Product::find($product['color']['product']['id']);
                $product_->stock = $product_->stock + intval($product['quantity']);
                $product_->in_stock = 1;
                $product_->update();
            }

            $referral = Referral::find($request->id);
            $referral->delivered = 1;
            $referral->save();

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
