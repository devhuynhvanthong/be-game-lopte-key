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
            FIELD_IP => REQUIRED
        ]);
        $ip = $request->input(FIELD_IP);
        $time = Carbon::parse(Librarys_::getDateTime());
        $queryQueues = Queues::with('key:id,code')
        ->where([
            FIELD_IP => $ip
        ])->get();
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
                FIELD_IP => $ip
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
                    return ResultRequest::exportResultSuccess(VERIFY_KEY);
                }else{
                    return ResultRequest::exportResultInternalServerError();
                }
            }else{
                $queryKey = Keys::get();
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
                                    return ResultRequest::exportResultSuccess(VERIFY_KEY,VALIDATE,201);
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
        dd($checkInt);
    }
}
