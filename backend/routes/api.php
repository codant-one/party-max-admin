<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Testing\TestingController;

use App\Http\Controllers\Auth\{
    AuthController,
    PasswordResetController
};

use App\Http\Controllers\Shopping\{
    CartController,
    FavoriteController,
    PaymentController
};

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
    SupplierController,
    AddressController,
    DocumentTypeController,
    ProxyController,
    ReviewController,
    ServiceController,
    FlavorController,
    FillingController,
    CakeTypeController,
    CakeSizeController
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
    Route::post('sendInfo', [AuthController::class, 'sendInfo']);
    Route::get('find/{token}', [AuthController::class, 'find'])->name('find');
    Route::post('completed', [AuthController::class, 'completed'])->name('completed');
    Route::post('forgot-password', [PasswordResetController::class, 'forgot_password'])->name('forgot.password');
    Route::get('password/find/{token}', [PasswordResetController::class, 'find'])->name("find");
    Route::post('change', [PasswordResetController::class, 'change'])->name("change");

    Route::middleware('jwt')->group(function () {
        Route::post('2fa/validate', [AuthController::class, 'validate_double_factor_auth'])->name('2fa.validate');
        Route::post('logout', [AuthController::class , 'logout'])->name('logout');
        Route::post('me', [AuthController::class , 'me'])->name('me');
        Route::get('generateQR', [AuthController::class , 'generateQR'])->name('generateQR');
        Route::get('store', [AuthController::class , 'storeDetail'])->name('storeDetail');
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
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('addresses', AddressController::class);
    Route::apiResource('reviews', ReviewController::class);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('flavors', FlavorController::class);    
    Route::apiResource('fillings', FillingController::class);
    Route::apiResource('cake-types', CakeTypeController::class);
    Route::apiResource('cake-sizes', CakeSizeController::class);

    //Users
    Route::group(['prefix' => 'users'], function () {
        Route::get('user/online', [UsersController::class, 'getOnline']);
        Route::post('update/password/{id}', [UsersController::class, 'updatePasswordUser']);
        Route::post('update/password', [UsersController::class, 'updatePassword']);
        Route::post('update/profile',  [UsersController::class, 'updateProfile']);
        Route::post('update/store', [UsersController::class, 'updateStore']);
        Route::get('user/profile',  [UsersController::class, 'getProfile']);
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
        Route::get('/', [UserMenuController::class, 'index']);
        Route::post('/add', [UserMenuController::class, 'store']);
        Route::post('/update', [UserMenuController::class, 'update']);
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
        Route::post('order_id', [ProductController::class, 'updateOrder']);
    });

     //Services
     Route::group(['prefix' => 'services'], function () {
        Route::post('delete', [ServiceController::class, 'delete']);
        Route::post('upload-image', [ServiceController::class, 'uploadImage']);
        Route::put('updateStatus/{id}', [ServiceController::class, 'updateStatus']);
        Route::put('updateStates/{id}', [ServiceController::class, 'updateStates']);
        Route::post('order_id', [ServiceController::class, 'updateOrder']);
    });

    //Faqs
    Route::group(['prefix' => 'faqs'], function () {
        Route::post('order_id', [FaqController::class, 'updateOrder']);
    });

    //Faq-categories
    Route::group(['prefix' => 'faq-categories'], function () {
        Route::post('delete', [FaqCategoryController::class, 'delete']);
        Route::get('faqs/all', [FaqCategoryController::class, 'faqs']);
    });

    //Blogs
    Route::group(['prefix' => 'blogs'], function () {
        Route::post('upload-image', [BlogController::class, 'uploadImage']);
        Route::post('order_id', [BlogController::class, 'updateOrder']);
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

    //Favorites
    Route::group(['prefix' => 'favorites'], function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('add', [FavoriteController::class, 'add']);
        Route::post('delete', [FavoriteController::class, 'delete']);
        Route::post('show', [FavoriteController::class, 'show']);
    });

    //Orders
    Route::group(['prefix' => 'orders'], function () {
        Route::put('/updatePaymentState/{id}', [OrderController::class, 'updatePaymentState']);
        Route::get('show/{id}', [OrderController::class, 'ordersbyclient']);
        Route::get('show-order/{id}', [OrderController::class, 'orderbyID']);
        Route::get('send/{id}', [OrderController::class, 'send']);
    });

    //Reviews
    Route::group(['prefix' => 'reviews'], function () {
        Route::post('delete', [ReviewController::class, 'delete']);
        Route::get('/show/{id}', [ReviewController::class, 'reviewbyclient']);
    });

    //Suppliers
    Route::group(['prefix' => 'suppliers'], function () {
        Route::put('/updateCommission/{id}', [SupplierController::class, 'updateCommission']);
        Route::put('/updateBalance/{id}', [SupplierController::class, 'updateBalance']);
    });

});

//Public Endpoints
Route::apiResource('countries', CountryController::class);
Route::apiResource('provinces', ProvinceController::class);
Route::apiResource('colors', ColorController::class);
Route::apiResource('genders', GenderController::class);
Route::apiResource('document-types', DocumentTypeController::class);

Route::get('home', [HomeController::class, 'home']);

Route::group([
    'prefix' => 'miscellaneous',
    'middleware' => 'throttle:10,1'
    ], function () {
    Route::get('categories/{slug}', [MiscellaneousController::class, 'categories']);
    Route::get('categories', [MiscellaneousController::class, 'categoriesAll']);
    Route::get('products', [MiscellaneousController::class, 'products']);
    Route::get('colors', [MiscellaneousController::class, 'colors']);
    Route::get('products/{slug}', [MiscellaneousController::class, 'productDetail']);
    Route::get('faqs/all', [MiscellaneousController::class, 'faqs']);
    Route::get('blogs/populars', [MiscellaneousController::class, 'popularsBlogs']);
    Route::get('blogs/{slug}', [MiscellaneousController::class, 'blogDetail']);
    Route::get('services', [MiscellaneousController::class, 'services']);
    Route::get('services/{slug}', [MiscellaneousController::class, 'serviceDetail']);
    Route::get('cupcakes', [MiscellaneousController::class, 'cupcakes']);
});

//Shopping-Cart
Route::group(['prefix' => 'shopping-cart'], function () {
    Route::get('/', [CartController::class, 'index']);
    Route::get('checkAvailability', [CartController::class, 'checkAvailability']);
});

//PAYU
Route::group(['prefix' => 'payments'], function () {
    Route::get('/signature', [PaymentController::class, 'signature']);
    Route::get('/', [PaymentController::class, 'redirectToPayUTesting']);
    Route::post('/', [PaymentController::class, 'redirectToPayU']);
    Route::get('response', [PaymentController::class, 'response']);
    Route::post('confirmation', [PaymentController::class, 'confirmation']);
});

//ORDERS
Route::apiResource('orders', OrderController::class);

//PROXY
Route::get('/proxy-image',[ProxyController::class, 'getImage']);

//Testing Endpoints
Route::get('testing', [TestingController::class , 'permissions'])->name('permissions');
Route::get('emails', [TestingController::class , 'emails'])->name('emails');
Route::get('paymentSummaryEmail', [TestingController::class , 'paymentSummaryEmail'])->name('paymentSummaryEmail');
Route::get('infoOrder', [TestingController::class , 'infoOrder'])->name('infoOrder');
Route::get('confirmationOrderPayU', [TestingController::class , 'confirmationOrderPayU'])->name('confirmationOrderPayU');
Route::get('littleProductExistence', [TestingController::class , 'littleProductExistenceEmail'])->name('littleProductExistence');
Route::get('outOfStockEmail', [TestingController::class , 'outOfStockEmail'])->name('outOfStockEmail');
Route::get('sendOrder', [TestingController::class , 'sendOrder'])->name('sendOrder');
Route::get('sendInfo', [TestingController::class , 'sendInfo'])->name('sendInfo');
Route::get('minus-stock/{order}', [TestingController::class , 'minus_stock'])->name('minus_stock');
Route::get('sum-sales/{order}', [TestingController::class , 'sum_sales'])->name('sum_sales');