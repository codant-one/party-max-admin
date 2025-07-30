<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use App\Models\Category;
use App\Models\Product;
use App\Models\FaqCategory;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Tag;
use App\Models\ProductLike;
use App\Models\ProductTag;
use App\Models\Color;
use App\Models\Service;
use App\Models\ServiceTag;
use App\Models\Flavor;
use App\Models\Filling;
use App\Models\CakeType;
use App\Models\CakeSize;
use App\Models\ClientIp;

class MiscellaneousController extends Controller
{
    public function categories($slug): JsonResponse
    {
        try {

            $products = [];

            $category = Category::with(['banner1', 'banner2', 'banner3', 'banner4', 'children'])
                                ->where('slug', $slug)
                                ->first();
    
            if($category)
                $products = 
                    Product::with(['user.userDetail', 'user.supplier', 'colors.categories', 'firstColor:id,product_id,in_stock,stock'])
                           ->whereHas('colors.categories', function($query) use ($category) {
                                $query->where('category_id', $category->id);
                           })
                           ->orderBy('created_at', 'desc')
                           ->limit(5)
                           ->get();

            return response()->json([
                'success' => true,
                'data' => [            
                    'category' => $category,
                    'products' => $products
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

    public function categoriesAll(): JsonResponse
    {
        try {

            $categories = Category::whereNull('category_id')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => $categories
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

    public function products(Request $request): JsonResponse
    {
        try {
            $limit = $request->has('limit') ? $request->limit : 12;

            // Cache keys
            $baseKey = md5(serialize($request->except('page')));
            $pageKey = 'products_page_' . $request->get('page', 1) . '_' . $baseKey;
            $countKey = 'products_total_' . $baseKey;

            // Filtros
            $filters = $request->only([
                'searchPublic',
                'orderByField',
                'orderBy',
                'category',
                'subcategory',
                'fathercategory',
                'colorId',
                'min',
                'max',
                'wholesalers',
                'sortBy',
                'rating'
            ]);

            // Cache del conteo total (sin paginación)
            $count = Cache::remember($countKey, now()->addMinutes(2), function () use ($filters) {
                return Product::where('products.state_id', 3)
                    ->applyFilters($filters)
                    ->isFavorite()
                    ->store()
                    ->company()
                    ->userProduct()
                    ->distinct('products.id')
                    ->count();
            });

            // Cache de productos paginados
            $products = Cache::remember($pageKey, now()->addMinutes(1), function () use ($filters, $limit) {
                return Product::select(
                        'products.id', 'products.user_id', 'products.wholesale_price', 'products.price_for_sale', 
                        'products.name', 'products.image', 'products.rating', 'products.single_description',
                        'products.slug', 'products.wholesale_min', 'products.order_id'
                    )
                    ->with(['firstColor:id,product_id,in_stock,stock'])
                    ->where('products.state_id', 3)
                    ->applyFilters($filters)
                    ->isFavorite()
                    ->store()
                    ->company()
                    ->userProduct()
                    ->distinct('products.id')
                    ->paginate($limit); // se mantiene paginate()
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'colors' => Color::where('name', '<>', 'Ninguno')->get(),
                    'products' => $products,
                    'productsTotalCount' => $count
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

    public function productDetail($slug): JsonResponse
    {
        try {

            $product = Product::with([
                                'user.userDetail', 
                                'user.supplier',
                                'brand', 
                                'colors.color', 
                                'colors.images',
                                'colors.categories.category', 
                                'tags.tag',
                                'detail',
                                'videos',
                                'reviews.client.user'
                              ])
                              ->where('slug', $slug)
                              ->first();

            $recommendations = 
                Product::with([
                            'user:id,name,last_name',
                            'user.userDetail:user_id,store_name', 
                            'user.supplier:user_id,company_name',
                            'firstColor:id,product_id,in_stock,stock'
                        ])
                       ->where('favourite', true)
                       ->orderBy('created_at', 'desc')
                       ->limit(5)
                       ->get();

            // Validate if the user is authenticated
            if (Auth::check()) {

                $productTag = ProductTag::where('product_id', $product->id)->first();
                    
                if ($productTag) {
                    $tag_id = $productTag->tag_id;

                    $recommendations = 
                            Product::with([
                                        'user:id,name,last_name',
                                        'user.userDetail:user_id,store_name', 
                                        'user.supplier:user_id,company_name',
                                        'tags', 
                                        'firstColor:id,product_id,in_stock,stock'
                                    ])
                                   ->whereHas('tags', function($query) use ($tag_id) {
                                        $query->where('tag_id', $tag_id);
                                   })
                                   ->orderBy('created_at', 'desc')
                                   ->limit(5)
                                   ->get();
                }

            }

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => $product,
                    'recommendations' => $recommendations
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

    public function productDetailMeta($slug): JsonResponse
    {
        try {

            $product = Product::select('id', 'name', 'image', 'slug', 'price_for_sale')->where('slug', $slug)->first();

            $keywords = ($product) ?
                $product->colors
                    ->flatMap(fn($color) => $color->categories->pluck('category'))
                    ->unique('id')
                    ->pluck('keywords')
                    ->flatten()
                    ->unique()
                    ->values()
                : '';

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => $product,
                    'keywords' => $keywords
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

    public function faqs(): JsonResponse
    {
        try {

            $categories = 
                FaqCategory::with(['faqs'=> 
                        function ($query) {
                            $query->orderBy('order_id', 'asc');
                        }
                    ])->get();
        
            return response()->json([
                'success' => true,
                'data' => [ 
                    'categories' => $categories
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

    public function popularsBlogs(Request $request): JsonResponse
    {
        try {

            $category = $request->has('category') ? $request->category : false;
            $search = $request->has('search') ? $request->search : false;
            $query = Blog::with(['user']);

            if($category) {
                $query = $query->whereHas('category', function ($q) use ($category) {
                            $q->where('slug', $category );
                         })->orderBy('order_id', 'asc');
            } else 
                $query = $query->orderBy('is_popular_blog', 'DESC');

            if($search)
                $query = $query->where('title', 'LIKE', '%' . $search . '%')
                               ->orWhere('description', 'LIKE', '%' . $search . '%');

            $blogs = $query->get();
                
            $categories = BlogCategory::withCount('blogs')->get();

            $latestBlogs = Blog::orderBy('created_at', 'desc')
                               ->limit(5)          
                               ->get();
        
            return response()->json([
                'success' => true,
                'data' => [ 
                    'blogs' => $blogs,
                    'categories' => $categories,
                    'latestBlogs' => $latestBlogs
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

    public function blogDetail($slug): JsonResponse
    {
        try {

            $blog = Blog::with(['category', 'user', 'tags.tag'])
                        ->where('slug',$slug)
                        ->first();

            $categories = BlogCategory::withCount('blogs')->get();

            $latestBlogs = Blog::orderBy('created_at', 'desc')
                               ->limit(5)          
                               ->get();
        
            return response()->json([
                'success' => true,
                'data' => [ 
                    'blog' => $blog,
                    'categories' => $categories,
                    'latestBlogs' => $latestBlogs
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

    public function colors(Request $request): JsonResponse
    {
        try {

            return response()->json([
                'success' => true,
                'data' => [
                    'colors' => Color::where('name', '<>', 'Ninguno')->get(),                 
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

    public function services(Request $request): JsonResponse
    {
        try {
            $limit = $request->has('limit') ? $request->limit : 12;

            // Cache keys
            $baseKey = md5(serialize($request->except('page')));
            $pageKey = 'services_page_' . $request->get('page', 1) . '_' . $baseKey;
            $countKey = 'services_total_' . $baseKey;

            // Filtros
            $filters = $request->only([
                'orderByField',
                'orderBy',
                'category',
                'subcategory',
                'fathercategory',
                'min',
                'max',
                'sortBy',
                'rating'
            ]);

            // Cache del conteo total
            $count = Cache::remember($countKey, now()->addMinutes(2), function () use ($filters) {
                return Service::where('services.state_id', 3)
                    ->applyFilters($filters)
                    ->isFavorite()
                    ->store()
                    ->company()
                    ->userService()
                    ->distinct('services.id')
                    ->count();
            });

            // Cache del listado paginado
            $services = Cache::remember($pageKey, now()->addMinutes(1), function () use ($filters, $limit) {
                return Service::select(
                        'services.id', 'services.user_id', 'services.image', 'services.price', 
                        'services.name', 'services.rating', 'services.single_description', 
                        'services.slug', 'services.order_id'
                    )
                    ->with(['firstCupcake:id,service_id,price'])
                    ->where('services.state_id', 3)
                    ->applyFilters($filters)
                    ->isFavorite()
                    ->store()
                    ->company()
                    ->userService()
                    ->distinct('services.id')
                    ->paginate($limit);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'services' => $services,
                    'servicesTotalCount' => $count
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

    public function serviceDetail($slug): JsonResponse
    {
        try {
            $service = Service::with([
                                'user.userDetail', 
                                'user.supplier',
                                'brand',  
                                'images',
                                'categories.category', 
                                'tags.tag',
                                'videos',
                                'cupcakes.cake_size.cake_type'
                              ])
                              ->where('slug', $slug)
                              ->first();

            $recommendations = 
                Service::with(['user.userDetail', 'user.supplier', 'cupcakes.cake_size.cake_type'])
                       ->where('favourite', true)
                       ->orderBy('created_at', 'desc')
                       ->limit(5)
                       ->get();

            // Validate if the user is authenticated
            if (Auth::check()) {

                $serviceTag = ServiceTag::where('service_id', $service->id)->first();
                    
                if ($serviceTag) {
                    $tag_id = $serviceTag->tag_id;

                    $recommendations = 
                        Service::with(['user.userDetail', 'user.supplier', 'tags', 'cupcakes.cake_size.cake_type'])
                               ->whereHas('tags', function($query) use ($tag_id) {
                                    $query->where('tag_id', $tag_id);
                               })
                               ->orderBy('created_at', 'desc')
                               ->limit(5)
                               ->get();
                }

            }

            return response()->json([
                'success' => true,
                'data' => [
                    'service' => $service,
                    'recommendations' => $recommendations
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

    public function cupcakes(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'flavors' => Flavor::all(),
                    'fillings' => Filling::all(),
                    'cakeTypes' => CakeType::all(),
                    'cakeSizes' => CakeSize::all()                 
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

    public function ips(Request $request): JsonResponse
    {
        try {

            $ips = ClientIp::select(['ip'])->where('is_blocked', 1)->get()->pluck('ip');

            return response()->json([
                'success' => true,
                'data' => [
                    'ips' => $ips                  
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

    public function contactUs(Request $request): JsonResponse
    {
        try {

            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => env('RECAPTCHA_SECRET_KEY'),
                'response' => $request->recaptcha_token
            ]);
        
            if (!$response->json('success')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de verificación reCAPTCHA.'
                ], 422);
            }

            $subject = 'Nuevo mensaje de contacto';
            $text = '<strong>Nombre:</strong> ' . $request->name . '<br>';
            $text .= '<strong>Email:</strong> ' . $request->email. '<br>';
            $text .= '<strong>Mensaje:</strong> ' . $request->message;

            $data = [
                'email' => $request->email,
                'title' => 'Nuevo mensaje de contacto',
                'text' => $text,
                'buttonText' => 'Responder ahora'
            ];

            \Mail::send(
                'emails.admin.contact'
                , ['data' => $data]
                , function ($message) use ($subject) {
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    $message->to(env('MAIL_TO_CONTACT'))->subject($subject);
            });

            return response()->json([
                'success' => true,
                'response' => $response->json()
            ], 200);

        } catch (\Exception $e){
            $responseMail = $e->getMessage();

            return response()->json([
                'success' => false,
                'message' => 'email_error',
                'exception' => $responseMail
            ], 500);
        } 
    }
    
}
