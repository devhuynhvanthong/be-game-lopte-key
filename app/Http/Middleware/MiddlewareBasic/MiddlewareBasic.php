<?php

namespace App\Http\Middleware\MiddlewareBasic;

use App\Librarys\Encryptions_;
use App\Librarys\ResultRequest;
use App\Models\Queues;
use Closure;
use Illuminate\Http\Request;

class MiddlewareBasic
{
    public function handle($request, Closure $next)
    {

        $input = json_decode(base64_decode($request->header(IP_MD5)),true);
        if($input){
            $ip = $input[FIELD_IP];
            $time = $input[FIELD_TIME];
            $request->replace([
                FIELD_IP => $ip,
                FIELD_TIME => $time
            ]);
        }
        return $next($request);
    }
}
