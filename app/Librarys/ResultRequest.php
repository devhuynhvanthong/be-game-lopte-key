<?php

namespace App\Librarys;

class ResultRequest
{
    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    static function exportResultSuccess($result)
    {
        return response()->json([
            STATUS => SUCCESS,
            DATA => $result,
            CATEGORY => VALIDATE
        ],201);
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    static function exportResultFailed($message){
        return response()->json([
            STATUS => FAILED,
            DATA => $message,
            CATEGORY => VALIDATE
        ],201);
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    static function exportResultAuthention(){
        return response()->json([
            STATUS => FAILED,
            DATA => MESSAGE_AUTHENTICATION,
            CATEGORY => AUTHENTICATION
        ],201);
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    static function exportResultInternalServerError(){
        return response()->json([
            STATUS => FAILED,
            MESSAGE=>INTERNAL_SERVER,
            CATEGORY => SERVER
        ],500);
    }
}
