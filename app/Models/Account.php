<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
    protected $table = TABLE_ACCOUNT;
    protected $fillable = [FIELD_ID,FIELD_CODE, FIELD_USERNAME, FIELD_PASSWORD, FIELD_TIME_CREATE];

}
