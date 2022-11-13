<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sessions extends Model
{
    use HasFactory;
    protected $table = TABLE_SESSIONS;
    protected $fillable = [FIELD_ID, FIELD_ID_ACCOUNT, FIELD_TIME_LOGIN,FIELD_TIME_EXPRIED,FIELD_TOKEN,FIELD_ID_SERVICE];
}
