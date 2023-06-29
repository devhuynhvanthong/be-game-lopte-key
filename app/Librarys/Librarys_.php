<?php
namespace App\Librarys;

use App\Models\Queues;
use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\returnArgument;

class Librarys_{
    public static function getDateTime(){
        return date(FORMAT_DATE_TIME, time());
    }

    public static function getDateTimeStartDay(){
        return self::getDate()." 00:00:01";
    }

    public static function getCode(){
        return sha1(self::getDateTime());
    }

    public static function createDateTime($time){
        return date(FORMAT_DATE_TIME, $time);
    }

    public static function getDate(){
        return date(FORMAT_DATE, time());
    }

    public static function callApi($url,$isPost = true ,$data = [],$header_ = null){
        $init = curl_init();
        curl_setopt($init,CURLOPT_URL,$url);
        curl_setopt($init,CURLOPT_POST,$isPost);

        $header = [
            "Content-Type: application/json",
        ];

        if ($header_!=null){
            $header = array_merge($header,$header_);
        }
        curl_setopt($init,CURLOPT_HTTPHEADER,$header);

        curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($init,CURLOPT_POSTFIELDS,json_encode($data));
        $query = curl_exec($init);
        curl_close($init);
        return json_decode($query,true);
    }

    public static function callApiKeys($url,$code_service){
        $url_ = $url."/api/primary_key_encryption";
        $init = curl_init();
        $header = [
            "Content-Type: application/json"
        ];
        curl_setopt($init,CURLOPT_URL,$url_);
        curl_setopt($init,CURLOPT_POST,true);
        curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($init,CURLOPT_HTTPHEADER,$header);
        curl_setopt($init,CURLOPT_POSTFIELDS,json_encode([
            CODE_SERVICE => $code_service
        ]));
        $query = curl_exec($init);
        curl_close($init);
        $data = json_decode($query,true);
        if ($data!=null){
            if ($data[STATUS]==SUCCESS){
                return $data;
            }else{
                return null;
            }
        }else{
            return null;
        }
    }

    public static function getMyServicecCode(){
        $cache = json_decode(Cache::get(KEY_CACHE_PRIMARY_KEY_ENCRYPTION),true);
        if ($cache!=null){
            if ($cache[FIELD_MY_SERVICE]!=null){
                return $cache[FIELD_MY_SERVICE];
            }else{
                return Services::where([
                    FIELD_NAME =>FIELD_MY_SERVICE
                ])->get()->value(FIELD_CODE);
            }
        }else{
            return Services::where([
                FIELD_NAME =>FIELD_MY_SERVICE
            ])->get()->value(FIELD_CODE);
        }
    }

    //public static function getServiceCode($url=null,$name=null){
    //    $cache = Cache::get(KEY_CACHE_PRIMARY_KEY_ENCRYPTION);
    //    if ($cache!=null){
    //        $codeService = null;
    //        foreach ($cache as $cache_){
    //            if ($cache_[fi])
    //        }
    //    }else{
    //        if ($url!=null){
    //            $queryService = Queues::where([
    //                FIELD_END_POINT =>$url
    //            ])->get();
    //            return $queryService->value(FIELD_CODE);
    //        }else{
    //            $queryService = Queues::where([
    //                FIELD_NAME =>$name
    //            ])->get();
    //            return $queryService->value(FIELD_CODE);
    //        }
    //    }
    //}
}
?>
