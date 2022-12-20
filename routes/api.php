<?php

use App\Http\Controllers\Datas\AccountDataController;
use App\Http\Controllers\Datas\HistoryController;
use App\Http\Controllers\Datas\PaymentsController;
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
    Route::get("get_domain",[AccountController::class,"getDomain"]);
});
Route::middleware('advenced')->group(function (){
    Route::get("get_personal_info",[AccountDataController::class,"getAccountData"]);
    Route::get("get_login_history",[HistoryController::class,"getLoginHistory"]);
    Route::get("get_recent_activity",[HistoryController::class,"getRecentActivity"]);
    Route::post("check_exprired_access_token",[AccountController::class,"checkExpriredAccessToken"]);
    Route::get("get_payment_method",[PaymentsController::class,"getListPayments"]);
    Route::post("add_payment_method",[PaymentsController::class,"setListPayments"]);
    Route::post("edit_payment_method",[PaymentsController::class,"editListPayments"]);
    Route::post("delete_payment_method",[PaymentsController::class,"deleteListPayments"]);
    Route::post("add_payment",[PaymentsController::class,"setPayment"]);
});

Route::get("test",[EncryptionController::class,"getTest"]);

Route::get('/', function () {
    return ResultRequest::exportResultInternalServerError();
});
