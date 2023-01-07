<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Librarys\Librarys_;
use App\Librarys\ResultRequest;
use App\Models\Category;
use App\Models\ConfigVisit;
use App\Models\Configs;
use App\Models\Keys;
use App\Models\Queues;
use App\Models\Used;
use Carbon\Carbon;
use DateInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Nette\Utils\DateTime;
use Termwind\Components\Li;

class KeyController extends Controller
{
    public function getKeyByVerify(Request $request){
        $request->validate([
            FIELD_CODE => REQUIRED,
            FIELD_INFO => REQUIRED
        ]);
        $mac = $request->input(FIELD_INFO);
        if (strlen($mac)<15){
            return ResultRequest::exportResultAuthention();
        }
        if($mac[1]!='a' || $mac[3]!='i' || $mac[5]!='g' || $mac[7]!='o' || $mac[9]!='o' || $mac[11]!='x'){
            return ResultRequest::exportResultAuthention();
        }
        $network = $request->ip();
        $ip = $mac.".".$network;
        $code = $request->input(FIELD_CODE);
        $time = Carbon::parse(Librarys_::getDateTime());
        $checkCode = Keys::with('category:id,code,name')
        ->where([
            'alias_code' => $code
        ])->get()->first();
        if ($checkCode){
            $queryQueues = Queues::with('key:id,code')
                ->where([
                    FIELD_IP => $ip,
                    FIELD_ID_KEY => $checkCode[FIELD_ID]
                ])->get();
        }else{
            return ResultRequest::exportResultFailed(KEY_EXPIRED);
        }

        if($queryQueues){
            if($queryQueues->count()>0){
                $queryQueues = $queryQueues->first();
                $timeCreate = $queryQueues[FIELD_TIME_CREATE];
                if(date(FORMAT_DATE_TIME,$time->subMinutes(5)->getTimestamp()) > $timeCreate){
                    $queryInsertKey = Queues::where([
                        FIELD_ID => $queryQueues[FIELD_ID]
                    ])
                        ->delete();
                    if($queryInsertKey){
                        return ResultRequest::exportResultFailed(KEY_EXPIRED);
                    }else{
                        return ResultRequest::exportResultInternalServerError();
                    }
                }else{
                    $data = [
                        FIELD_KEY => $queryQueues[FIELD_KEY][FIELD_CODE]
                    ];
                    $queryDelete = Keys::where([
                        FIELD_ID => $queryQueues[FIELD_ID_KEY]
                    ])->delete();

                    if($queryDelete){
                        $queryInsertUsed = Used::insert([
                            FIELD_CODE => $queryQueues[FIELD_KEY][FIELD_CODE],
                            FIELD_TIME => Librarys_::getDateTime(),
                            FIELD_IP => $ip,
                            FIELD_ID_CATEGORY => $checkCode->category->id
                        ]);
                        if($queryInsertUsed){
                            return ResultRequest::exportResultSuccess($data,DATA);
                        }else{
                            return ResultRequest::exportResultInternalServerError();
                        }
                    }
                }
            }else{
                return ResultRequest::exportResultFailed(PERMISSION_DEVICE_FAILED);
            }
        }else{
            return ResultRequest::exportResultInternalServerError();
        }
    }
    public function verifyKey(Request $request){
        $request->validate([
            FIELD_INFO => REQUIRED,
            FIELD_CATEGORY => REQUIRED
        ]);
        $mac = $request->input(FIELD_INFO);
        $category = $request->input(FIELD_CATEGORY);
        if (strlen($mac)<15){
            return ResultRequest::exportResultAuthention();
        }
        if($mac[1]!='a' || $mac[3]!='i' || $mac[5]!='g' || $mac[7]!='o' || $mac[9]!='o' || $mac[11]!='x'){
            return ResultRequest::exportResultAuthention();
        }
        $network = $request->ip();
        $ip = $mac.".".$network;
        $queryCategory = Category::where([
            FIELD_CODE => $category
        ])->get()->first();
        if (!$queryCategory){
            return ResultRequest::exportResultInternalServerError();
        }

        $queryCheckUsedCategory = ConfigVisit::with(['category_key:id,code'])
        ->where([
            FIELD_ID_CATEGORY => $queryCategory->id
        ])->get();
        if (!$queryCheckUsedCategory){
            return ResultRequest::exportResultInternalServerError();
        }
        if ($queryCheckUsedCategory->count()>0){
            $queryConfig = Configs::where([FIELD_CODE => 'visits'])->get()->first();
            if (!$queryConfig){
                return ResultRequest::exportResultInternalServerError();
            }
            if (explode('_',$queryConfig->value)[2] == "HOUR"){
                $date = new DateTime(Librarys_::getDate());
                $queryCheckUsed = Used::where('time','>=',$date->sub(new DateInterval('PT2H')))
                    ->where([
                        FIELD_ID_CATEGORY => $queryCategory->id
                    ])
                    ->where('ip','LIKE','%.'.$network)->get();
            }
            else{
                $queryCheckUsed = Used::where('time','LIKE',Librarys_::getDate().' %')
                    ->where([
                        FIELD_ID_CATEGORY => $queryCategory->id
                    ])
                    ->where('ip','LIKE','%.'.$network)->get();
            }
            if (!$queryCheckUsed){
                return ResultRequest::exportResultInternalServerError();
            }
            switch ($queryConfig->value){
                case "ONE_KEY_HOUR":
                case "ONE_KEY_DAY":
                    if ($queryCheckUsed->count()>0){
                        return ResultRequest::exportResultFailed(KEY_OUT_TO_DAY);
                    }
                    break;
                case "TWO_KEY_HOUR":
                case "TWO_KEY_DAY":
                    if ($queryCheckUsed->count()>1){
                        return ResultRequest::exportResultFailed(KEY_OUT_TO_DAY);
                    }
            }

        }

        $queryQueues = Queues::with('key:id,code')
            ->where([
                FIELD_IP => $ip,
            ])->get();

        if ($queryQueues){
            if($queryQueues->count()>0){
                $queryUpdateTime = Queues::where([
                    FIELD_ID => $queryQueues->value(FIELD_ID)
                ])
                    ->update([
                        FIELD_TIME_CREATE =>Librarys_::getDateTime()
                    ]);

                if($queryUpdateTime){
                    $queryVerify = Queues::with('key:id,alias_code')
                        ->where([
                            FIELD_ID => $queryQueues->first()[FIELD_ID]
                        ])->get()->first();
                    if($queryVerify){
                        return ResultRequest::exportResultSuccess([
                            FIELD_CODE => $queryVerify->key->alias_code
                        ],VALIDATE,201);
                    }else{
                        return ResultRequest::exportResultFailed(VERIFY_KEY_FAILD);
                    }

                }else{
                    return ResultRequest::exportResultInternalServerError();
                }
            }else{
                $queryCategory = Category::where([
                    FIELD_CODE => $category
                ])->get();
                if (!$queryCategory){
                    return ResultRequest::exportResultInternalServerError();
                }
                if ($queryCategory->count()<=0){
                    return ResultRequest::exportResultFailed("The game doesn't exist");
                }
                $queryKey = Keys::where([
                    FIELD_ID_CATEGORY => $queryCategory->value(FIELD_ID)
                ])->get();
                if ($queryKey){
                    foreach ($queryKey as $item){
                        $queryQueExist = Queues::where([
                            FIELD_ID_KEY => $item->id
                        ])->get();
                        if($queryQueExist){
                            if($queryQueExist->count()<=0){
                                $queryVerify = Queues::insert([
                                    FIELD_IP => $ip,
                                    FIELD_ID_KEY => $item->id,
                                    FIELD_TIME_CREATE => Librarys_::getDateTime()
                                ]);
                                if($queryVerify){
                                    return ResultRequest::exportResultSuccess([
                                        FIELD_CODE => $item->alias_code
                                    ],VALIDATE,201);
                                }else{
                                    return ResultRequest::exportResultFailed(VERIFY_KEY_FAILD);
                                }
                            }
                        }else{
                            return ResultRequest::exportResultInternalServerError();
                        }
                    }
                    return ResultRequest::exportResultFailed(OUT_OF_KEY);
                }else{
                    return ResultRequest::exportResultInternalServerError();
                }
            }
        }else{
            return ResultRequest::exportResultInternalServerError();
        }
    }

    public function getKeys(Request $request){

        $request->validate([
            'page_offet' => REQUIRED
        ]);
        $page_offet = $request->input('page_offet');
        $checkInt = filter_var($page_offet, FILTER_VALIDATE_INT);
        if(!$checkInt){
            return ResultRequest::exportResultFailed(VALUE_INVLID,401);
        }else{
            if ($checkInt<=0){
                return ResultRequest::exportResultFailed(VALUE_INVLID,401);
            }
        }
        $queryKeys = Keys::with([
            'category:id,name'
        ])->orderByDesc('time_create')->get();
        if ($queryKeys){
            $totalRecord = $queryKeys->count();
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
                        'alias_code' => $queryKeys[$i]->alias_code,
                        FIELD_CODE => $queryKeys[$i]->code,
                        FIELD_ID => $queryKeys[$i]->id,
                        FIELD_TIME_CREATE => $queryKeys[$i]->time_create,
                        'category' => $queryKeys[$i]->category->name
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

    public function getKeysQueues(Request $request){
        $request->validate([
            'page_offet' => REQUIRED
        ]);
        $page_offet = $request->input('page_offet');
        $checkInt = filter_var($page_offet, FILTER_VALIDATE_INT);
        if(!$checkInt){
            return ResultRequest::exportResultFailed(VALUE_INVLID,401);
        }else{
            if ($checkInt<=0){
                return ResultRequest::exportResultFailed(VALUE_INVLID,401);
            }
        }
        $queryKeys = Queues::with('key:id,code,time_create')->orderByDesc('time_create')->get();
        if ($queryKeys){
            $totalRecord = $queryKeys->count();
            $totalPage = $totalRecord / PAGE_SIZE_DEFAULT;
            if ($totalRecord % PAGE_SIZE_DEFAULT > 0){
                $totalPage++;
            }
            if ($checkInt > $totalPage){
                return ResultRequest::exportResultSuccess([]);
            }else{
                $p = ($checkInt-1)*10;
                $max = $p + 9;
                if ($max > $totalRecord){
                    $max = $totalRecord - 1;
                }
                $merge = [];
                for ($i=$p; $i <= $max; $i++){
                    $merge = [...$merge,[
                        FIELD_ID => $queryKeys[$i][FIELD_ID],
                        FIELD_IP => $queryKeys[$i][FIELD_IP],
                        'time_queues' => $queryKeys[$i][FIELD_TIME_CREATE],
                        FIELD_CODE => $queryKeys[$i][FIELD_KEY][FIELD_CODE],
                        FIELD_TIME_CREATE => $queryKeys[$i][FIELD_KEY][FIELD_TIME_CREATE]
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

    public function getKeysUsed(Request $request){
        $request->validate([
            'page_offet' => REQUIRED
        ]);
        $page_offet = $request->input('page_offet');
        $checkInt = filter_var($page_offet, FILTER_VALIDATE_INT);
        if(!$checkInt){
            return ResultRequest::exportResultFailed(VALUE_INVLID,401);
        }else{
            if ($checkInt<=0){
                return ResultRequest::exportResultFailed(VALUE_INVLID,401);
            }
        }
        $queryKeys = Used::orderByDesc('time')->get();
        if ($queryKeys){
            $totalRecord = $queryKeys->count();
            $totalPage = (int)($totalRecord / PAGE_SIZE_DEFAULT);
            if ($totalRecord % PAGE_SIZE_DEFAULT > 0){
                $totalPage++;
            }
            if ($checkInt > $totalPage){
                return ResultRequest::exportResultSuccess([]);
            }else{
                $p = ($checkInt-1)*10;
                $max = $p + 9;
                if ($max > $totalRecord){
                    $max = $totalRecord - 1;
                }
                $merge = [];
                for ($i=$p; $i <= $max; $i++){
                    $merge = [...$merge,[
                        FIELD_ID => $queryKeys[$i][FIELD_ID],
                        FIELD_IP => $queryKeys[$i][FIELD_IP],
                        FIELD_CODE => $queryKeys[$i][FIELD_CODE],
                        FIELD_TIME_CREATE => $queryKeys[$i][FIELD_TIME]
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

    public function removeKey(Request $request){
        $request->validate([
            'id_key' => REQUIRED
        ]);
        $page_offet = $request->input('id_key');
        $checkInt = filter_var($page_offet, FILTER_VALIDATE_INT);
        if(!$checkInt){
            return ResultRequest::exportResultFailed(VALUE_INVLID,401);
        }else{
            if ($checkInt<=0){
                return ResultRequest::exportResultFailed(VALUE_INVLID,401);
            }
        }
        $queryDelete = Keys::where([
            FIELD_ID => $checkInt
        ])->delete();
        if ($queryDelete){
            return ResultRequest::exportResultSuccess(DELETE_DATA_SUCCESS);
        }else{
            return ResultRequest::exportResultFailed(DELETE_DATA_FAILED);
        }
    }

    public function addKey(Request $request){

        $request->validate([
            FIELD_CODE => REQUIRED,
            FIELD_CATEGORY => REQUIRED
        ]);

        $totalError = 0;
        $totalProcess = 0;
        $totalExist = 0;
        $codeArr = json_decode($request->input(FIELD_CODE));

        if ($codeArr == null){
            return ResultRequest::exportResultFailed("Định dạng gửi lên bắt buộc mãng (Array)!",400);
        }
        $category = $request->input(FIELD_CATEGORY);
        if ($category == "" || $category == null){
            return ResultRequest::exportResultFailed(FIELD_EMPTY);
        }
        $queryCategory = Category::where([
            FIELD_CODE => $category
        ])->get()->first();
        if ($queryCategory== null){
            return ResultRequest::exportResultFailed(FIELD_INVALID);
        }

        $totalKey = count($codeArr);
        $arrayData = array();
        foreach ($codeArr as $code){
            $totalProcess++;
            if (strlen($code)<=0){
                $totalError++;
                continue;
            }
            $queryCheck = Keys::where([FIELD_CODE=>$code])->get();
            if ($queryCheck){
                if ($queryCheck->count()>0){
                    $totalExist++;
                }else{
                    array_push($arrayData,[
                        FIELD_CODE => $code,
                        FIELD_TIME_CREATE => Librarys_::getDateTime(),
                        'alias_code' => sha1(json_encode([FIELD_CODE => $code, FIELD_TIME_CREATE => Librarys_::getDateTime()])),
                        FIELD_ID_CATEGORY => $queryCategory->id
                    ]);
                }
            }else{
                $totalError++;
            }
        }
        $queryInsert = Keys::insert(
            $arrayData
        );

        if ($queryInsert){
            return ResultRequest::exportResultSuccess([
                MESSAGE => ADD_DATA_SUCCESS,
                DATA => [
                    "total_key" => $totalKey,
                    "total_success" => $totalProcess - $totalExist - $totalError,
                    "total_processed" => $totalProcess,
                    "total_error" => [
                        "total_exist" => $totalExist,
                        "total_error" => $totalError
                    ]
                ]
            ],DATA,201);
        }else{
            return ResultRequest::exportResultSuccess(ADD_DATA_FAILED);
        }
    }

}
