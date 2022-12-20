<?php

namespace App\Http\Controllers\Datas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Services;
use App\Librarys\Librarys_;
use App\Librarys\ResultRequest;

class HistoryController extends Controller
{
    public function getLoginHistory(Request $request){
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
            $url_base_account .= PATH_GET_LOGIN_HISTORY;
            $body = [
                DATA => $request->input(ACCESS_TOKEN_COOKIE)
            ];

            $data = Librarys_::callApi($url_base_account,true,$body);
            if($data!=null){
                $body = $data[BODY];
                $mergeArray = [];
                foreach($body as $item){
                    $mergeArray = [...$mergeArray,[
                        FIELD_ID => $item[FIELD_ID],
                        FIELD_BROWSER => $item[FIELD_BROWSER],
                        FIELD_IP => $item[FIELD_IP],
                        FIELD_TIME_LOGIN => $item[FIELD_TIME_LOGIN],
                        FIELD_DEVICE => $item[FIELD_DEVICE]
                    ]];
                }
                return ResultRequest::exportResultSuccess($mergeArray,DATA);
            }else{
                return ResultRequest::exportResultSuccess([]);
            }
        }
        else{
            return ResultRequest::exportResultInternalServerError();
        }
    }

    public function getRecentActivity(Request $request){
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
            $url_base_account .= PATH_GET_RECENT_ACTIVITY;
            $body = [
                DATA => $request->input(ACCESS_TOKEN_COOKIE)
            ];

            $data = Librarys_::callApi($url_base_account,true,$body);
            if($data!=null){
                $body = $data[BODY];
                $mergeArray = [];
                foreach($body as $item){
                    $mergeArray = [...$mergeArray,[
                        FIELD_NAME => $item[FIELD_NAME],
                        FIELD_TIME => $item[FIELD_TIME],
                        FIELD_BROWSER => $item[FIELD_BROWSER],
                        FIELD_IP => $item[FIELD_IP],
                        FIELD_DEVICE => $item[FIELD_DEVICE],
                        FIELD_SERVICES => $item[FIELD_SERVICES]
                    ]];
                }
                return ResultRequest::exportResultSuccess($mergeArray,DATA);
            }else{
                return ResultRequest::exportResultSuccess([],DATA);
            }
        }
        else{
            return ResultRequest::exportResultInternalServerError();
        }
    }
    
}
