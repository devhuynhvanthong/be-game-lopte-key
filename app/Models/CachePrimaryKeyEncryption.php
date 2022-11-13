<?php

namespace App\Models;

class CachePrimaryKeyEncryption
{
    function __construct($key,$code,$date,$name,$endpoint){
        $this->key = $key;
        $this->code = $code;
        $this->date = $date;
        $this->name = $name;
        $this->endpoint = $endpoint;
    }
}
