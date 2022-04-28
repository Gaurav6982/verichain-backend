<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('login', [ApiController::class, 'authenticate']);
Route::post('register', [ApiController::class, 'register']);
Route::get('fetch_students',[ApiController::class,"fetch_students"]);
Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('logout', [ApiController::class, 'logout']);
    Route::post('update_user_data', [ApiController::class, 'update_user_data']);
    Route::post('get_user_data', [ApiController::class, 'get_user_data']);
    Route::get('get_user', [ApiController::class, 'get_user']);
});
