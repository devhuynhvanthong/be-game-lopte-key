<?php

namespace App\Http\Controllers;

use App\Librarys\Librarys_;
use App\Librarys\ResultRequest;
use App\Models\Category;
use App\Models\Keys;
use App\Models\Queues;
use App\Models\Used;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function getCategory(){

        $category = Category::orderByDesc('id')->get();
        $arr = array();

        if ($category){
            if ($category->count()>0){
                foreach ($category as $item){
                    array_push($arr,[
                        FIELD_CODE => $item->code,
                        FIELD_NAME => $item->name
                    ]);
                }
                return ResultRequest::exportResultSuccess($arr,DATA);
            }else{
                return ResultRequest::exportResultSuccess([],DATA);
            }
        }else{
            return ResultRequest::exportResultInternalServerError();
        }
    }

    public function getAllCategory(Request $request){
        $request->validate([
            'page_offset' => REQUIRED
        ]);
        $page_offset = $request->input('page_offset');
        $checkInt = filter_var($page_offset, FILTER_VALIDATE_INT);
        if(!$checkInt){
            return ResultRequest::exportResultFailed(VALUE_INVLID,401);
        }else{
            if ($checkInt<=0){
                return ResultRequest::exportResultFailed(VALUE_INVLID,401);
            }
        }
        $category = Category::orderByDesc('id')->get();
        if ($category){
            $totalRecord = $category->count();
            $totalPage = (int)($totalRecord / PAGE_SIZE_DEFAULT);
            if ($totalRecord % PAGE_SIZE_DEFAULT > 0){
                $totalPage++;
            }
            if ($checkInt > $totalPage){
                return ResultRequest::exportResultSuccess([]);
            }else{
                $p = ($checkInt-1)*10;
                $max = $p + 9;
                if ($max >= $totalRecord){
                    $max = $totalRecord - 1;
                }

                $merge = [];
                for ($i=$p; $i <= $max; $i++){
                    $merge = [...$merge,[
                        FIELD_CODE => $category[$i]->code,
                        FIELD_NAME => $category[$i]->name,
                        'total_key' => Keys::where([
                            FIELD_ID_CATEGORY => $category[$i]->id
                        ])->get()->count()
                    ]];
                }
                $data = [
                    'total_page' => $totalPage,
                    DATA => $merge
                ];
                return ResultRequest::exportResultSuccess($data,DATA);
            }
        }else{
            return ResultRequest::exportResultInternalServerError();
        }
    }

    public function removeCategory(Request $request){
        $request->validate([
            FIELD_CODE => REQUIRED
        ]);
        $queryRemove = Category::where([
            FIELD_CODE => $request->input(FIELD_CODE)
        ])->delete();

        if ($queryRemove){
            return ResultRequest::exportResultSuccess(true,DATA);
        }else{
            return ResultRequest::exportResultFailed(false,DATA);
        }
    }

    public function updateCategory(Request $request){
        $request->validate([
            FIELD_NAME => REQUIRED,
            FIELD_CODE => REQUIRED
        ]);

        $name = $request->input(FIELD_NAME);
        $code = $request->input(FIELD_CODE);
        if (strlen($name)<=0){
            return ResultRequest::exportResultFailed(FIELD_INVALID,400);
        }
        $queryUpdate = Category::where([
            FIELD_CODE => $code
        ])->update([
            FIELD_NAME => $name
        ]);

        if ($queryUpdate){
            return ResultRequest::exportResultSuccess(UPDATE_DATA_SUCCESS);
        }else{
            return ResultRequest::exportResultFailed(UPDATE_DATA_FAILED);
        }

    }

    public function addCategory(Request $request){
        $request->validate([
            FIELD_NAME => REQUIRED
        ]);

        $name = $request->input(FIELD_NAME);
        if (strlen($name)<=0){
            return ResultRequest::exportResultFailed(FIELD_INVALID,400);
        }

        $queryCheck = Category::where([
            FIELD_NAME => trim($name)
        ])->get();

        if (!$queryCheck){
            return ResultRequest::exportResultInternalServerError();
        }

        if ($queryCheck->count()>0){
            return ResultRequest::exportResultFailed("Game đã tồn tại!");
        }
        $queryUpdate = Category::insert([
            FIELD_CODE => sha1(json_encode([FIELD_NAME => $name, FIELD_TIME_CREATE => Librarys_::getDateTime()])),
            FIELD_NAME => $name
        ]);

        if ($queryUpdate){
            return ResultRequest::exportResultSuccess(UPDATE_DATA_SUCCESS);
        }else{
            return ResultRequest::exportResultFailed(UPDATE_DATA_FAILED);
        }

    }
}
