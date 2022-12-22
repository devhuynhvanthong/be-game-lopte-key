<?php

namespace App\Http\Controllers\Datas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Services;
use App\Librarys\Librarys_;
use App\Librarys\ResultRequest;

class PaymentsController extends Controller
{
    public function getListPayments(Request $request){
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
            $url_base_account .= PATH_GET_PAYMENT_METHOD;
            $body = [
                DATA => json_encode([
                    ACCCESS_TOKEN => $request->input(ACCESS_TOKEN_COOKIE)
                ])
            ];

            $data = Librarys_::callApi($url_base_account,true,$body);
            if($data!=null){
                $body = $data[BODY];
                $mergeArray = [];
                foreach($body as $item){
                    $mergeArray = [...$mergeArray,[
                        FIELD_ID => $item[FIELD_ID],
                        FIELD_CODE => $item[FIELD_CODE],
                        FIELD_NAME => $item[FIELD_NAME],
                        FIELD_BANK => $item[FIELD_BANK],
                        FIELD_BRANCH => $item[FIELD_BRANCH]
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

    public function setListPayments(Request $request){
        $request->validate([
            FIELD_CODE => REQUIRED,
            FIELD_NAME => REQUIRED,
            FIELD_BANK => REQUIRED
        ]);
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
            $url_base_account .= PATH_ADD_PAYMENT_METHOD;
            $branch = null;
            if($request->input(FIELD_BRANCH) !== null){
                $branch = $request->input(FIELD_BRANCH);
            }
            $body = [
                DATA => json_encode([
                    ACCCESS_TOKEN => $request->input(ACCESS_TOKEN_COOKIE),
                    FIELD_CODE => $request->input(FIELD_CODE),
                    FIELD_NAME => $request->input(FIELD_NAME),
                    FIELD_BANK => $request->input(FIELD_BANK),
                    FIELD_BRANCH => $branch
                ])
            ];

            $data = Librarys_::callApi($url_base_account,true,$body);
            if($data){
                if($data[STATUS] == SUCCESS){
                    $body = $data[BODY][DATA];
                    $mergeArray = [];
                    foreach($body as $item){
                        $mergeArray = [...$mergeArray,[
                            FIELD_ID => $item[FIELD_ID],
                            FIELD_CODE => $item[FIELD_CODE],
                            FIELD_NAME => $item[FIELD_NAME],
                            FIELD_BANK => $item[FIELD_BANK],
                            FIELD_BRANCH => $item[FIELD_BRANCH]
                        ]];
                    }
                    return ResultRequest::exportResultSuccess($mergeArray,DATA,201);
                }else{
                    return ResultRequest::exportResultFailed($data[MESSAGE]);
                }
            }else{
                return ResultRequest::exportResultInternalServerError();
            }
        }
        else{
            return ResultRequest::exportResultInternalServerError();
        }
    }
}
