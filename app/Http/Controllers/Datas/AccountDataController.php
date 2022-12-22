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
            $isBalance = false;
            if($request->input(BALANCE)){
                if($request->input(BALANCE)){
                    $isBalance = true;
                }
            }

            $body = [
                DATA => json_encode([
                    ACCCESS_TOKEN => $request->input(ACCESS_TOKEN_COOKIE),
                    BALANCE => $isBalance
                ])
            ];

            $data = Librarys_::callApi($url_base_account,true,$body);
            if($data){
                if($data[STATUS] == SUCCESS){
                    $output = [
                        FIELD_INFO => [
                            FIELD_NAME=>$data[BODY][FIELD_INFO][FIELD_NAME],
                            FIELD_NUMBER_PHONE=>$data[BODY][FIELD_INFO][FIELD_NUMBER_PHONE],
                            FIELD_EMAIL=>$data[BODY][FIELD_INFO][FIELD_EMAIL],
                            FIELD_ADDRESS=>$data[BODY][FIELD_INFO][FIELD_ADDRESS],
                            FIELD_AVATAR=>$data[BODY][FIELD_INFO][FIELD_AVATAR],
                            FILED_GENDER=>(boolean)$data[BODY][FIELD_INFO][FILED_GENDER],
                            FIELD_BIRTH_DAY=>$data[BODY][FIELD_INFO][FIELD_BIRTH_DAY],
                            FIELD_CODE=>$data[BODY][FIELD_INFO][FIELD_CODE],
                        ]
                    ];

                    if($isBalance){
                        $balance = [
                            BALANCE => [
                                FILED_TOTAL_BALANCE => $data[BODY][BALANCE][FILED_TOTAL_BALANCE],
                                FILED_CURRENT_BALANCE => $data[BODY][BALANCE][FILED_CURRENT_BALANCE],
                                FILED_AVAILABLE_BALANCE => $data[BODY][BALANCE][FILED_AVAILABLE_BALANCE],
                                FILED_LOCKED_BALANCE => $data[BODY][BALANCE][FILED_LOCKED_BALANCE],
                                FIELD_USED => $data[BODY][BALANCE][FIELD_USED],
                                FIELD_EARNED => $data[BODY][BALANCE][FIELD_EARNED]
                            ]
                        ];
                        $output = array_merge($output,$balance);
                    }
                    return ResultRequest::exportResultSuccess($output,DATA);
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
