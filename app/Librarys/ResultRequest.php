<?php

namespace App\Librarys;

class ResultRequest
{
    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    static function exportResultSuccess($result,$category=VALIDATE,$code=200)
    {
        return response()->json([
            STATUS => SUCCESS,
            BODY => $result,
            CATEGORY => $category
        ],$code);
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    static function exportResultFailed($message,$code=200){
        return response()->json([
            STATUS => FAILED,
            MESSAGE => $message,
            CATEGORY => VALIDATE
        ],$code);
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    static function exportResultAuthention($message = MESSAGE_AUTHENTICATION){
        return response()->json([
            STATUS => FAILED,
            DATA => $message,
            CATEGORY => AUTHENTICATION
        ],401);
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
