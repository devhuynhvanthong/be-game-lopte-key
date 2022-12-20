<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Librarys\ResultRequest;
use App\Librarys\Encryptions_;
use App\Librarys\Librarys_;
use App\Models\Encryption;
use App\Models\Account;
use League\CommonMark\Extension\Table\Table;

class AccountController extends Controller
{
    public function insert(Request $request)
    {
        $request->validate(
            [
                FIELD_USERNAME => REQUIRED,
                FIELD_PASSWORD => REQUIRED
            ]
        );
        $username = trim($request->input(FIELD_USERNAME));
        $password = trim($request->input(FIELD_PASSWORD));
        if (strlen($password)<8){
            return ResultRequest::exportResultFailed(FORMAT_LENGHT_PASSOWRD);
        }
        $cache = json_decode(Cache::get(KEY_CACHE_PRIMARY_KEY_ENCRYPTION),true);
        $url_login = null;
        $code_authen = null;
        if ($cache!=null){
            foreach ($cache as $cache_){
                if ($cache_[FIELD_NAME]==VALUE_ACCOUNT_SERVICE_NAME){
                    $url_login = $cache_[FIELD_END_POINT];
                    $code_authen = $cache_[FIELD_CODE];
                    break;
                }
            }
        }

        if ($url_login == null || $code_authen == null){
            $queryLogin = Services::where([FIELD_NAME=>VALUE_ACCOUNT_SERVICE_NAME])->get();
            $url_login = $queryLogin->value(FIELD_END_POINT);
            $code_authen = $queryLogin->value(FIELD_CODE);
        }

        if ($url_login!=null && $code_authen!=null){
            $data = [
                FIELD_USERNAME => $username,
                FIELD_PASSWORD => $password
            ];
            $url_login .= PATH_REGISTER;
            dd([
                DATA => $data,
                'url' => $url_login
            ]);
            $data = Librarys_::callApi($url_login,true,$data);
            return $data;
        }
        else{
            return ResultRequest::exportResultInternalServerError();
        }
    }

    public function checkLogin(Request $request){
        $request->validate(
            [
                FIELD_USERNAME => REQUIRED,
                FIELD_PASSWORD => REQUIRED,
                FIELD_BROWSER => REQUIRED,
                FIELD_IP => REQUIRED,
                FIELD_DEVICE =>REQUIRED
            ]
        );
        $username = trim($request->input(FIELD_USERNAME));
        $password = trim($request->input(FIELD_PASSWORD));
        $cache = json_decode(Cache::get(KEY_CACHE_PRIMARY_KEY_ENCRYPTION),true);
        $url_login = null;
        $code_authen = null;
        if ($cache!=null){

            foreach ($cache[FIELD_CACHE] as $cache_){
                if ($cache_[FIELD_NAME]==VALUE_ACCOUNT_SERVICE_NAME){
                    $url_login = $cache_[FIELD_END_POINT];
                    $code_authen = $cache_[FIELD_CODE];
                    break;
                }
            }
        }

        if ($url_login == null || $code_authen == null){
            $queryLogin = Services::where([FIELD_NAME=>VALUE_ACCOUNT_SERVICE_NAME])->get();
            $url_login = $queryLogin->value(FIELD_END_POINT);
            $code_authen = $queryLogin->value(FIELD_CODE);
        }

        if ($url_login!=null && $code_authen!=null){
            $data = [
                FIELD_USERNAME => $username,
                FIELD_PASSWORD => $password,
                FIELD_DEVICE => $request->input(FIELD_DEVICE),
                FIELD_IP => $request->input(FIELD_IP),
                FIELD_BROWSER => $request->input(FIELD_BROWSER)
            ];
            $url_login .= PATH_LOGIN;
            $data = Librarys_::callApi($url_login,true,$data);
            $data = [
                MESSAGE => $data[BODY][MESSAGE],
                ACCCESS_TOKEN => $data[BODY][DATA][ACCCESS_TOKEN]
            ];
            return ResultRequest::exportResultSuccess($data,DATA);
        }
        else{
            return ResultRequest::exportResultInternalServerError();
        }
    }

    public function getDomain(Request $request){
        $request->validate([
            FIELD_CODE_SERVICE => REQUIRED
        ]);


        $code_service = base64_decode($request->input(FIELD_CODE_SERVICE));
        if ($code_service!=null){
            $query = Services::where([FIELD_NAME => $code_service."_product"])->get();
            if ($query->count() > 0){
                return ResultRequest::exportResultSuccess($query->value(FIELD_END_POINT));
            }else{
                return ResultRequest::exportResultFailed(false);
            }
        }else{
            return ResultRequest::exportResultFailed(false);
        }
    }

}
