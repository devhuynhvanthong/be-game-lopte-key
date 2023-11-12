<?php

namespace App\Http\Middleware\AuthenticationRequest;

use App\Librarys\Encryptions_;
use App\Librarys\Librarys_;
use App\Librarys\ResultRequest;
use App\Models\Queues;
use App\Models\Services;
use App\Models\Session;
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

                $query = Session::where([
                    FIELD_TOKEN => $accessToken
                ])->get()->first();

                if ($query) {
                    $input = $request->all();
                    $merge = array_merge($input, [
                        FIELD_TOKEN => $tokenAuthen
                    ]);
                    $request->replace($merge);
                    return $next($request);
                }
            }

            return ResultRequest::exportResultAuthention();

        }
        return ResultRequest::exportResultAuthention();
    }
}
