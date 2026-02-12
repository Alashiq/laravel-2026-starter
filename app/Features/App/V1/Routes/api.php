<?php

use App\Features\App\v1\Controllers\AuthController;
use App\Features\App\v1\Controllers\CatererController;
use App\Features\App\v1\Controllers\CityController;
use App\Features\App\v1\Controllers\HallController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    return 'App API V1 Home';
});


# # # # # # # # # # # # # # # Login # # # # # # # # # # # # # # # 
Route::post('/login', [AuthController::class, 'login']);
Route::post('/activate', [AuthController::class, 'activate']);
Route::get('ccity/{id}', [CityController::class, 'show']);
Route::get('ccity', [CityController::class, 'list']);
Route::get('citypg', [CityController::class, 'index']);
Route::post('citypg', [CityController::class, 'store']);

# # # # # # # # # # # # # # # End Login # # # # # # # # # # # # # # # 





Route::middleware(['auth:sanctum', 'type.user'])->group(function () {

    # # # # # # # # # # # # # # # User # # # # # # # # # # # # # # # 
    Route::group(['prefix' => 'user'], function () {
        Route::post('/signup', [AuthController::class, 'signup']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/name', [AuthController::class, 'name']);
        Route::post('/city', [AuthController::class, 'city']);
    });
    # # # # # # # # # # # # # # # End User # # # # # # # # # # # # # # # 


    # # # # # # # # # # # # # # # Hall # # # # # # # # # # # # # # # 
    Route::group(['prefix' => 'hall'], function () {
        Route::get('/', [HallController::class, 'index']);
        Route::get('/{id}', [HallController::class, 'show']);
        Route::post('/book', [HallController::class, 'book']);

    });
    # # # # # # # # # # # # # # # End Hall # # # # # # # # # # # # # # # 



    # # # # # # # # # # # # # # # Caterer # # # # # # # # # # # # # # # 
    Route::group(['prefix' => 'caterer'], function () {
        Route::get('/', [CatererController::class, 'index']);
        Route::get('/{id}', [CatererController::class, 'show']);
        Route::post('/book', [CatererController::class, 'book']);

    });
    # # # # # # # # # # # # # # # End Hall # # # # # # # # # # # # # # # 




    # # # # # # # # # # # # # # # Caterer # # # # # # # # # # # # # # # 
    Route::group(['prefix' => 'city'], function () {
        Route::get('/', [CityController::class, 'index']);
        Route::get('/list', [CityController::class, 'list']);
        Route::get('/{id}', [CityController::class, 'show']);

    });
    # # # # # # # # # # # # # # # End Hall # # # # # # # # # # # # # # # 


});

// End Auth 