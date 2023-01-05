<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\KeyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Librarys\ResultRequest;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware("basic")->group(function (){
    Route::post("get_key",[KeyController::class,"getKeyByVerify"]);
    Route::post("verify_key",[KeyController::class,"verifyKey"]);
    Route::get('get_categorys',[CategoryController::class,"getCategory"]);
    Route::get('get_config',[ConfigController::class,'getConfig']);
});
Route::middleware('advenced')->group(function (){
    Route::post('add_key',[KeyController::class,'addKey']);
    Route::get('get_all_key',[KeyController::class,'getKeys']);
    Route::get('get_all_key_queues',[KeyController::class,'getKeysQueues']);
    Route::get('get_all_key_useds',[KeyController::class,'getKeysUsed']);
    Route::post('remove_key',[KeyController::class,'removeKey']);
    Route::post('remove_category',[CategoryController::class,'removeCategory']);
    Route::post('edit_category',[CategoryController::class,'updateCategory']);
    Route::get('get_all_category',[CategoryController::class,'getAllCategory']);
    Route::post('add_categogy',[CategoryController::class,'addCategory']);
    Route::post('update_config',[ConfigController::class,'updateConfig']);
});

Route::get('/', function () {
    return ResultRequest::exportResultInternalServerError();
});
