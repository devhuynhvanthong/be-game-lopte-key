<?php

namespace App\Http\Middleware\AuthenticationRequest;

use App\Librarys\Encryptions_;
use App\Librarys\Librarys_;
use App\Librarys\ResultRequest;
use App\Models\Queues;
use App\Models\Services;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Cache;

class AuthenticationRequest extends Middleware
{

    public function handle($request, Closure $next, ...$guards)
    {
        $tokenReceive = $request->header(AUTHORIZATION);

        if ($tokenReceive!=null){

            if (substr($tokenReceive,0,7) == BEARER){
                $tokenAuthen = str_replace(BEARER,"",$request->header(AUTHORIZATION));

                $accessToken = $tokenAuthen;
                // $cache = json_decode(Cache::get(KEY_CACHE_SERVICE),true);

                // $cache = array_filter($cache, function ($item) {
                //     return $item[FIELD_NAME] == VALUE_ACCOUNT_PRODUCT;
                // })[0];
                // $url_base_account = Encryptions_::decryptionAESMyData($cache[FIELD_END_POINT]);
                // $url_base_account .= PATH_CHECK_EXPIRED_ACCESS_TOKEN;
                $url_base_account = 'https://api-account.aigoox.com/api/check_expired_session';
                $data = Librarys_::callApi($url_base_account,true,[],[
                    "Authorization: Bearer " . $accessToken
                ]);
                if ($data){
                    if ($data[STATUS]==SUCCESS){
                        if ($data[BODY]['group_account']=="MANAGER_SHARED" ||
                            $data[BODY]['group_account']=="OWNER" ||
                            $data[BODY]['group_account']=="MANAGER"
                        ){
                            $input = $request->all();
                            $requestAddToken = array_merge($input,[
                                ACCESS_TOKEN_COOKIE => $accessToken
                            ]);
                            $request->replace($requestAddToken);
                            return $next($request);
                        }else{
                            return ResultRequest::exportResultFailed(PERMISSION_INVALID,401);
                        }

                    }else{
                        if ($data[CATEGORY]==AUTHENTICATION){
                            return ResultRequest::exportResultAuthention($data[MESSAGE]);
                        }else{
                            return ResultRequest::exportResultFailed($data[MESSAGE]);
                        }
                    }
                }else{
                    return ResultRequest::exportResultAuthention();
                }
            }else{
                return ResultRequest::exportResultAuthention();
            }

        }
        return ResultRequest::exportResultAuthention();
    }
}
