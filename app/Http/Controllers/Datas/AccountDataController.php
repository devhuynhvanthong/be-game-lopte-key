<?php

namespace App\Http\Controllers\Datas;

use App\Http\Controllers\Controller;
use App\Librarys\Librarys_;
use App\Librarys\ResultRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Services;
class AccountDataController extends Controller
{
    public function getAccountData(Request $request){
        $url_base_account = null;
        $code_authen = null;
        $cache = json_decode(Cache::get(KEY_CACHE_PRIMARY_KEY_ENCRYPTION),true);
        if ($cache!=null){
            foreach ($cache[FIELD_CACHE]as $cache_){
                if ($cache_[FIELD_NAME]==VALUE_ACCOUNT_SERVICE_NAME){
                    $url_base_account = $cache_[FIELD_END_POINT];
                    $code_authen = $cache_[FIELD_CODE];
                    break;
                }
            }
        }

        if ($url_base_account == null || $code_authen == null){
            $queryLogin = Services::where([FIELD_NAME=>VALUE_ACCOUNT_SERVICE_NAME])->get();
            $url_base_account = $queryLogin->value(FIELD_END_POINT);
            $code_authen = $queryLogin->value(FIELD_CODE);
        }

        if ($url_base_account!=null && $code_authen!=null){
            $url_base_account .= PATH_GET_PERSONAL_INFO;
            $body = [
                DATA => $request->input(ACCESS_TOKEN_COOKIE)
            ];

            $data = Librarys_::callApi($url_base_account,true,$body);
            return ResultRequest::exportResultSuccess($data,DATA);
        }
        else{
            return ResultRequest::exportResultInternalServerError();
        }
    }
}
