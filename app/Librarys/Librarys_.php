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

    public static function getCode(){
        return sha1(self::getDateTime());
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
}
?>
