<?php

namespace App\Http\Middleware\MiddlewareBasic;

use App\Librarys\ResultRequest;
use App\Models\Services;
use Closure;
use Illuminate\Http\Request;

class MiddlewareBasic
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
