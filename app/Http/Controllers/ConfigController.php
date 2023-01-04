<?php

namespace App\Http\Controllers;

use App\Librarys\Librarys_;
use App\Librarys\ResultRequest;
use App\Models\Category;
use App\Models\Configs;
use App\Models\Keys;
use App\Models\Queues;
use App\Models\Used;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function getConfig(){
        $queryConfig = Configs::get();
        $arr = array();
        if ($queryConfig){
            foreach ($queryConfig as $item){
                array_push($arr,[
                   FIELD_CODE => $item->value(FIELD_CODE),
                   FIELD_NAME => $item->value(FIELD_NAME),
                   FIELD_VALUE => $item->value(FIELD_VALUE)
                ]);
            }
            return ResultRequest::exportResultSuccess($arr,DATA);
        }else{
            return ResultRequest::exportResultInternalServerError();
        }
    }
    public function updateCategory(Request $request){
        $request->validate([
            FIELD_VALUE => REQUIRED,
            FIELD_CODE => REQUIRED
        ]);

        $value = $request->input(FIELD_VALUE);
        $code = $request->input(FIELD_CODE);
        if (strlen($value)<=0){
            return ResultRequest::exportResultFailed(FIELD_INVALID,400);
        }
        $queryUpdate = Configs::where([
            FIELD_CODE => $code
        ])->update([
            FIELD_VALUE => $value
        ]);

        if ($queryUpdate){
            return ResultRequest::exportResultSuccess(UPDATE_DATA_SUCCESS);
        }else{
            return ResultRequest::exportResultFailed(UPDATE_DATA_FAILED);
        }
    }
}
