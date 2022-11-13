<?php

namespace App\Http\Middleware\AuthenticationRequest;

use App\Librarys\Encryptions_;
use App\Librarys\Librarys_;
use App\Librarys\ResultRequest;
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

                $accessToken = $tokenAuthen; //json_decode(base64_decode($tokenAuthen),true);
                //$accessToken = $accessToken[ACCESS_TOKEN_COOKIE];
                $url_base_account = null;
                $cache = json_decode(Cache::get(KEY_CACHE_PRIMARY_KEY_ENCRYPTION),true);
                if ($cache!=null){
                    foreach ($cache[FIELD_CACHE] as $cache_){
                        if ($cache_[FIELD_NAME]==VALUE_ACCOUNT_SERVICE_NAME){
                            $url_base_account = $cache_[FIELD_END_POINT];
                            break;
                        }
                    }
                }

                if ($url_base_account == null){
                    $queryLogin = Services::where([FIELD_NAME=>VALUE_ACCOUNT_SERVICE_NAME])->get();
                    $url_base_account = $queryLogin->value(FIELD_END_POINT);
                }

                if ($url_base_account!=null){
                    $url_base_account .= PATH_CHECK_EXPRIRED_ACCESS_TOKEN;
                    $input = [
                        DATA => $accessToken
                    ];
                    $data = Librarys_::callApi($url_base_account,true,$input);

                    if ($data){
                        if ($data[STATUS]==true){
                            $input = $request->all();
                            $accessToken = json_decode(base64_decode($accessToken),true);
                            $accessToken = $accessToken[ACCESS_TOKEN_COOKIE];
                            $requestAddToken = array_merge($input,[
                                ACCESS_TOKEN_COOKIE => $accessToken
                            ]);

                            $request->replace($requestAddToken);
                            return $next($request);
                        }else{
                            return ResultRequest::exportResultAuthention();
                        }
                    }else{
                        return ResultRequest::exportResultInternalServerError();
                    }
                }
                else{
                    return ResultRequest::exportResultInternalServerError();
                }
            }else{
                return ResultRequest::exportResultAuthention();
            }

        }else{
            return ResultRequest::exportResultAuthention();
        }
    }
}
