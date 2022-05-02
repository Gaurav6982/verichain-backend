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
Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('fetch_students',[ApiController::class,"fetch_students"]);
    Route::post('store_document',[ApiController::class,"store_document"]);
    Route::post('get_documents/{user_id}',[ApiController::class,"store_document"]);
    Route::post('upload_profile_image',[ApiController::class,"upload_profile_image"]);
    Route::post('logout', [ApiController::class, 'logout']);
    Route::post('update_user_data', [ApiController::class, 'update_user_data']);
    Route::post('get_user_data', [ApiController::class, 'get_user_data']);
    Route::get('get_user', [ApiController::class, 'get_user']);
});
