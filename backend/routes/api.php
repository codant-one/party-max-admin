<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Testing\TestingController;

use App\Http\Controllers\{
    CountryController,
    ProvinceController,
    UsersController,
    RoleController,
    PermissionController,
    UserMenuController,
    CategoryController,
    ProductController,
    BlogController,
    FaqController,
    ColorController,
    HomeController,
    FaqCategoryController,
    ClientController,
    BlogCategoryController,
    GenderController,
    MiscellaneousController,
    BrandController,
    TagController,
    OrderController,
    SupplierController

};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group([
    'prefix' => 'auth',
    'middleware' => 'cors'
], function () {
    Route::post('login', [AuthController::class , 'login'])->name('login');
    Route::post('register', [AuthController::class, 'register']);
    Route::post('forgot-password', [PasswordResetController::class, 'forgot_password'])->name('forgot.password');
    Route::get('password/find/{token}', [PasswordResetController::class, 'find'])->name("find");
    Route::post('change', [PasswordResetController::class, 'change'])->name("change");

    Route::middleware('jwt')->group(function () {
        Route::post('2fa/validate', [AuthController::class, 'validate_double_factor_auth'])->name('2fa.validate');
        Route::post('logout', [AuthController::class , 'logout'])->name('logout');
        Route::post('me', [AuthController::class , 'me'])->name('me');
        Route::get('generateQR', [AuthController::class , 'generateQR'])->name('generateQR');
    });
});

//Private Endpoints
Route::group(['middleware' => ['cors','jwt'] ], function(){
     
    //Resources 
    Route::apiResource('users', UsersController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('blogs', BlogController::class);
    Route::apiResource('faqs', FaqController::class);
    Route::apiResource('faq-categories', FaqCategoryController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('blog-categories', BlogCategoryController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('tags', TagController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('suppliers', SupplierController::class);

    //Users
    Route::group(['prefix' => 'users'], function () {
        Route::get('user/online', [UsersController::class, 'getOnline']);
        Route::post('update/password/{id}', [UsersController::class, 'updatePasswordUser']);
        Route::post('update/password', [UsersController::class, 'updatePassword']);
        Route::post('update/profile',  [UsersController::class, 'updateProfile']);
    });

    //Roles
    Route::group(['prefix' => 'roles'], function () {
        Route::get('role/all', [RoleController::class, 'all']);
    });

    //Permissions
    Route::group(['prefix' => 'permissions'], function () {
        Route::get('permission/all', [PermissionController::class, 'all']);
    });

    //Menu
    Route::group(['prefix' => 'menu'], function () {
        Route::get('/',[UserMenuController::class, 'index']);
        Route::post('/add',[UserMenuController::class, 'store']);
        Route::post('/update',[UserMenuController::class, 'update']);
    });

    //Categories
     Route::group(['prefix' => 'categories'], function () {
        Route::get('list/order', [CategoryController::class, 'order']);
        Route::post('delete', [CategoryController::class, 'delete']);
    });

    //Products
    Route::group(['prefix' => 'products'], function () {
        Route::post('delete', [ProductController::class, 'delete']);
        Route::post('upload-image', [ProductController::class, 'uploadImage']);
        Route::put('updateStatus/{id}', [ProductController::class, 'updateStatus']);
        Route::put('updateStates/{id}', [ProductController::class, 'updateStates']);
    });

    //Faq-categories
    Route::group(['prefix' => 'faq-categories'], function () {
        Route::post('delete', [FaqCategoryController::class, 'delete']);
        Route::get('faqs/all', [FaqCategoryController::class, 'faqs']);
    });

    //Blogs
    Route::group(['prefix' => 'blogs'], function () {
        Route::post('upload-image', [BlogController::class, 'uploadImage']);
    });

     //Blog-categories
    Route::group(['prefix' => 'blog-categories'], function () {
        Route::post('delete', [BlogCategoryController::class, 'delete']);
        Route::get('blogs/all', [BlogCategoryController::class, 'all']);
    });

    //Profile
    Route::group(['prefix' => 'profile'], function () {
       Route::post('/', [ClientController::class, 'profile']);
       Route::post('/changeAvatar', [ClientController::class, 'changeAvatar']);
       Route::post('/changePassword', [ClientController::class, 'changePassword']);
       Route::post('/changePhone', [ClientController::class, 'changePhone']);       
    });
});

//Public Endpoints
Route::apiResource('countries', CountryController::class);
Route::apiResource('provinces', ProvinceController::class);
Route::apiResource('colors', ColorController::class);
Route::apiResource('genders', GenderController::class);

Route::get('home', [HomeController::class, 'home']);
Route::get('miscellaneous/categories/{slug}', [MiscellaneousController::class, 'categories']);
Route::get('miscellaneous/categories', [MiscellaneousController::class, 'categoriesAll']);
Route::get('miscellaneous/products', [MiscellaneousController::class, 'products']);
Route::get('miscellaneous/products/{slug}', [MiscellaneousController::class, 'productDetail']);
Route::get('miscellaneous/faqs/all', [MiscellaneousController::class, 'faqs']);
Route::get('miscellaneous/blogs/populars', [MiscellaneousController::class, 'popularsBlogs']);
Route::get('miscellaneous/blogs/{slug}', [MiscellaneousController::class, 'blogDetail']);
Route::get('miscellaneous/blogs/categories/{slug}', [MiscellaneousController::class, 'blogsByCategory']);


//Testing Endpoints
Route::get('testing', [TestingController::class , 'permissions'])->name('permissions');
Route::get('emails', [TestingController::class , 'emails'])->name('emails');