<?php

namespace App\Http\Controllers;

use App\Librarys\ResultRequest;
use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheController extends Controller
{
    public function setDefaultCache(Request $request)
    {
        $queryService = Services::all();
        if (count($queryService)) {
            Cache::delete(KEY_CACHE_SERVICE);
            Cache::put(KEY_CACHE_SERVICE, json_encode($queryService));
            Log::info("Done write cache service");
            return ResultRequest::exportResultSuccess([
                MESSAGE => SET_CACHE_DEFAULT_SUCCESS
            ]);
        } else {
            return ResultRequest::exportResultInternalServerError();
        }
    }
}
