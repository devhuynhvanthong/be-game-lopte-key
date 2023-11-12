<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\KeyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Librarys\ResultRequest;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware("basic")->group(function (){
    Route::post("get_key",[KeyController::class,"getKeyByVerify"]);
    Route::post("verify_key",[KeyController::class,"verifyKey"]);
    Route::get('get_category',[CategoryController::class,"getCategory"]);
    Route::get('get_config',[ConfigController::class,'getConfig']);
    Route::post("register", [AccountController::class, "add"]);
    Route::post("login", [AccountController::class, "login"]);
});
Route::middleware('advanced')->group(function (){
    Route::post('add_key',[KeyController::class,'addKey']);
    Route::put('update_password',[AccountController::class,'update']);
    Route::get('account',[AccountController::class,'get']);
    Route::get('get_all_key',[KeyController::class,'getKeys']);
    Route::post('logout',[AccountController::class,'logout']);
    Route::get('get_all_key_queues',[KeyController::class,'getKeysQueues']);
    Route::get('get_all_key_used',[KeyController::class,'getKeysUsed']);
    Route::post('remove_key',[KeyController::class,'removeKey']);
    Route::post('remove_category',[CategoryController::class,'removeCategory']);
    Route::post('edit_category',[CategoryController::class,'updateCategory']);
    Route::get('get_all_category',[CategoryController::class,'getAllCategory']);
    Route::post('add_category',[CategoryController::class,'addCategory']);
    Route::post('update_config',[ConfigController::class,'updateConfig']);
    Route::get('get_config_visit',[ConfigController::class,'getConfigCategory']);
});

Route::get('/', function () {
    return ResultRequest::exportResultInternalServerError();
});
