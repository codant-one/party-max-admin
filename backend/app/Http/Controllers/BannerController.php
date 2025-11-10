<?php

namespace App\Http\Controllers;

use App\Http\Requests\BannerRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Spatie\Permission\Middlewares\PermissionMiddleware;

use App\Models\Banner;
use App\Models\User;

class BannerController extends Controller
{
    protected $banners;  

    public function __construct()
    {
        $this->banners = [];

        $this->middleware(PermissionMiddleware::class . ':ver banners|administrador')->only(['index']);
        $this->middleware(PermissionMiddleware::class . ':crear banners|administrador')->only(['store']);
        $this->middleware(PermissionMiddleware::class . ':editar banners|administrador')->only(['update']);
        $this->middleware(PermissionMiddleware::class . ':eliminar banners|administrador')->only(['delete']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $limit = $request->has('limit') ? $request->limit : 10;
        
            $query = Banner::applyFilters(
                                    $request->only([
                                        'search',
                                        'orderByField',
                                        'orderBy'
                                    ])
                                );
            
            $count = $query->applyFilters(
                                $request->only([
                                    'search',
                                    'orderByField',
                                    'orderBy'
                                ])
                            )->count();

            $banners = ($limit == -1) ? $query->paginate($query->count()) : $query->paginate($limit);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'banners' => $banners,
                    'bannersTotalCount' => $count
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
    public function store(BannerRequest $request): JsonResponse
    {
        try {

            $bannerModel = Banner::createBanner($request);

            if ($request->hasFile('banner')) {
                $bannerFile = $request->file('banner');

                $path = 'banners/';

                $file_data = uploadFile($bannerFile, $path);

                $bannerModel->banner = $file_data['filePath'];
                $bannerModel->update();
            } 

            if ($request->hasFile('banner_2')) {
                $banner_2 = $request->file('banner_2');

                $path = 'banners/';

                $file_data = uploadFile($banner_2, $path);

                $bannerModel->banner_2 = $file_data['filePath'];
                $bannerModel->update();
            } 

            if ($request->hasFile('banner_3')) {
                $banner_3 = $request->file('banner_3');

                $path = 'banners/';

                $file_data = uploadFile($banner_3, $path);

                $bannerModel->banner_3 = $file_data['filePath'];
                $bannerModel->update();
            } 

            if ($request->hasFile('banner_4')) {
                $banner_4 = $request->file('banner_4');

                $path = 'banners/';

                $file_data = uploadFile($banner_4, $path);

                $bannerModel->banner_4 = $file_data['filePath'];
                $bannerModel->update();
            } 

            return response()->json([
                'success' => true,
                'data' => [
                    'banner' => Banner::find($bannerModel->id)
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
    public function show($id)
    {
        try {
            
            $banner = Banner::find($id);

            if (!$banner)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Banner no encontrado'
                ], 404);

            return response()->json([
                'success' => true,
                'data' => [ 
                    'banner' => $banner
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
     * Update the specified resource in storage.
     */
    public function update(BannerRequest $request, $id): JsonResponse
    {
        try {

            $banner = Banner::find($id);

            if (!$banner)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Banner no encontrado'
                ], 404);

            $bannerModel = $banner->updateBanner($request, $banner);

            if ($request->hasFile('banner')) {
                $bannerFile = $request->file('banner');

                $path = 'banners/';

                $file_data = uploadFile($bannerFile, $path, $bannerModel->banner);

                $bannerModel->banner = $file_data['filePath'];
                $bannerModel->save();
            }

            if ($request->hasFile('banner_2')) {
                $banner_2 = $request->file('banner_2');

                $path = 'banners/';

                $file_data = uploadFile($banner_2, $path, $bannerModel->banner_2);

                $bannerModel->banner_2 = $file_data['filePath'];
                $bannerModel->save();
            }

            if ($request->hasFile('banner_3')) {
                $banner_3 = $request->file('banner_3');

                $path = 'banners/';

                $file_data = uploadFile($banner_3, $path, $bannerModel->banner_3);

                $bannerModel->banner_3 = $file_data['filePath'];
                $bannerModel->save();
            }

            if ($request->hasFile('banner_4')) {
                $banner_4 = $request->file('banner_4');

                $path = 'banners/';

                $file_data = uploadFile($banner_4, $path, $bannerModel->banner_4);

                $bannerModel->banner_4 = $file_data['filePath'];
                $bannerModel->save();
            }


            return response()->json([
                'success' => true,
                'data' => [ 
                    'banner' => Banner::find($bannerModel->id)
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
    public function delete(Request $request): JsonResponse
    {
        try {

            $banner = Banner::find($request->ids);

            if (!$banner)
                return response()->json([
                    'sucess' => false,
                    'feedback' => 'not_found',
                    'message' => 'Banner no encontrado'
                ], 404);
                
            Banner::deleteBanners($request->ids);

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
