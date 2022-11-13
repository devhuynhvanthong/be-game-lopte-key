<?php

use App\Http\Controllers\Datas\AccountDataController;
use App\Http\Controllers\Encryption\EncryptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
    Route::post("register",[AccountController::class,"insert"]);
    Route::post("login",[AccountController::class,"checkLogin"]);
    Route::post("get_domain",[AccountController::class,"getDomain"]);
});
Route::middleware('advenced')->group(function (){
    Route::post("get_personal_info",[AccountDataController::class,"getAccountData"]);
});

Route::get("test",[EncryptionController::class,"getTest"]);

Route::get('/', function () {
    return ResultRequest::exportResultInternalServerError();
});
