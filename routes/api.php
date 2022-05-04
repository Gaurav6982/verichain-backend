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
    Route::get('get_documents/{user_id}',[ApiController::class,"get_documents"]);
    Route::get('verify_document/{doc_id}',[ApiController::class,"verify_document"]);
    Route::get('reject_document/{doc_id}',[ApiController::class,"reject_document"]);
    Route::get('delete_document/{doc_id}',[ApiController::class,"delete_document"]);
    Route::post('upload_profile_image',[ApiController::class,"upload_profile_image"]);
    Route::post('logout', [ApiController::class, 'logout']);
    Route::post('update_user_data', [ApiController::class, 'update_user_data']);
    Route::post('get_user_data', [ApiController::class, 'get_user_data']);
    Route::get('get_user', [ApiController::class, 'get_user']);
    Route::post('send_mail/{student_id}', [ApiController::class, 'send_mail']);
});
