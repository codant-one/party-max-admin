<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Testing\TestingController;

use App\Http\Controllers\{
    CountryController,
    ProvinceController,
    UsersController,
    RoleController,
    PermissionController,
    UserMenuController
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

    Route::middleware('jwt')->group(function () {
        Route::post('logout', [AuthController::class , 'logout'])->name('logout');
        Route::post('me', [AuthController::class , 'me'])->name('me');
    });
});

//Private Endpoints
Route::group(['middleware' => ['cors','jwt'] ], function(){
     
    //Resources 
    Route::apiResource('users', UsersController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);

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

});

//Public Endpoints
Route::apiResource('countries', CountryController::class);
Route::apiResource('provinces', ProvinceController::class);

//Testing Endpoints
Route::get('testing', [TestingController::class , 'permissions'])->name('permissions');
