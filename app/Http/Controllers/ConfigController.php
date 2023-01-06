<?php

namespace App\Http\Controllers;

use App\Librarys\Librarys_;
use App\Librarys\ResultRequest;
use App\Models\Category;
use App\Models\Configs;
use App\Models\ConfigVisit;
use App\Models\Keys;
use App\Models\Queues;
use App\Models\Used;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function getConfigCategory(){
        $query = ConfigVisit::with(
            'category_key:id,code,name'
        )->get();
        if ($query){
            $arr = [];
            foreach ($query as $item){
                $arr = [...$arr,[
                   FIELD_CODE => $item->category_key->code,
                   FIELD_NAME => $item->category_key->name
                ]];
            }
            return ResultRequest::exportResultSuccess($arr,DATA);
        }else{
            return ResultRequest::exportResultInternalServerError();
        }
    }
    public function getConfig(){
        $queryConfig = Configs::get();
        $arr = array();
        if ($queryConfig){
            foreach ($queryConfig as $item){
                $arr = [...$arr,[
                    FIELD_CODE => $item->code,
                    FIELD_NAME => $item->name,
                    FIELD_VALUE => $item->value
                ]];
            }
            return ResultRequest::exportResultSuccess($arr,DATA);
        }else{
            return ResultRequest::exportResultInternalServerError();
        }
    }
    public function updateConfig(Request $request){
        $request->validate([
            FIELD_VALUE => REQUIRED,
            FIELD_CODE => REQUIRED,
        ]);

        $value = $request->input(FIELD_VALUE);
        $code = $request->input(FIELD_CODE);
        if (strlen($value)<=0){
            return ResultRequest::exportResultFailed(FIELD_INVALID,400);
        }
        $arrayCategory = $request->input(FIELD_CATEGORY);
        Configs::where([
            FIELD_CODE => $code
        ])->update([
            FIELD_VALUE => $value
        ]);

        if ($code == 'visits'){

            ConfigVisit::truncate();
            foreach ($arrayCategory as $item){
                $query = Category::where([
                    FIELD_CODE => $item
                ])->get()->first();
                if ($query){
                    ConfigVisit::insert([
                        FIELD_ID_CATEGORY => $query->id
                    ]);
                }
            }
            return ResultRequest::exportResultSuccess(UPDATE_DATA_SUCCESS);
        }else{
            return ResultRequest::exportResultSuccess(UPDATE_DATA_SUCCESS);
        }
    }
}
