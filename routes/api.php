<?php

use App\Http\Controllers\Datas\AccountDataController;
use App\Http\Controllers\Datas\HistoryController;
use App\Http\Controllers\Datas\PaymentsController;
use App\Http\Controllers\Encryption\EncryptionController;
use App\Http\Controllers\KeyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Account\AccountController;
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
});
Route::middleware('advenced')->group(function (){
    Route::get('get_all_key',[KeyController::class,'getKeys']);
    Route::get('get_all_key_queues',[KeyController::class,'getKeysQueues']);
    Route::get('get_all_key_useds',[KeyController::class,'getKeysUsed']);
    Route::post('remove_key',[KeyController::class,'removeKey']);
});

Route::get('/', function () {
    return ResultRequest::exportResultInternalServerError();
});
