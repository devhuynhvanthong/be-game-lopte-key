<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Librarys\Librarys_;
use App\Librarys\ResultRequest;
use App\Models\Keys;
use App\Models\Queues;
use App\Models\Used;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Termwind\Components\Li;

class KeyController extends Controller
{
    public function getKeyByVerify(Request $request){
        $request->validate([
            FIELD_IP => REQUIRED,
            FIELD_CODE => REQUIRED
        ]);
        $ip = $request->input(FIELD_IP);
        $code = $request->input(FIELD_CODE);
        $time = Carbon::parse(Librarys_::getDateTime());
        $checkCode = Keys::where([
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
                            FIELD_IP => $ip
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
            FIELD_IP => REQUIRED
        ]);
        $ip = $request->input(FIELD_IP);
        $queryQueues = Queues::with('key:id,code')
            ->where([
                FIELD_IP => $ip,
            ])->get();

        if ($queryQueues){
            if($queryQueues->count()>0){
                $queryUpdateTime = Queues::where([
                    FIELD_ID => $queryQueues->first()[FIELD_ID]
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
                        return ResultRequest::exportResultInternalServerError();
                    }
                    return ResultRequest::exportResultSuccess(VERIFY_KEY);
                }else{
                    return ResultRequest::exportResultInternalServerError();
                }
            }else{

                $queryKey = Keys::get();
                if ($queryKey){

                    foreach ($queryKey as $item){

                        $queryQueExist = Queues::where([
                            FIELD_ID_KEY => $item
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
                                    return ResultRequest::exportResultInternalServerError();
                                }
                            }
                        }else{

                            return ResultRequest::exportResultInternalServerError();
                        }
                    }
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
        $queryKeys = Keys::get();
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
                    $merge = [...$merge,$queryKeys[$i]];
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
        $queryKeys = Queues::with('key:id,code,time_create')->get();
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
        $queryKeys = Used::get();
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
            FIELD_CODE => REQUIRED
        ]);

        $code = $request->input(FIELD_CODE);
        if (strlen($code)<=0){
            return ResultRequest::exportResultFailed("Độ dày key được để trống!");
        }
        $queryCheck = Keys::where([FIELD_CODE=>$code])->get();
        if ($queryCheck){
            if ($queryCheck->count()>0){
                return ResultRequest::exportResultFailed("Key đã tồn tại!");
            }else{
                $queryInsert = Keys::insert([
                    FIELD_CODE => $code,
                    FIELD_TIME_CREATE => Librarys_::getDateTime(),
                    'alias_code' => sha1(json_encode([FIELD_CODE => $code, FIELD_TIME_CREATE => Librarys_::getDateTime()]))
                ]);
                if ($queryInsert){
                    return ResultRequest::exportResultSuccess(ADD_DATA_SUCCESS);
                }else{
                    return ResultRequest::exportResultSuccess(ADD_DATA_FAILED);
                }
            }
        }else{
            return ResultRequest::exportResultInternalServerError();
        }
    }
}
