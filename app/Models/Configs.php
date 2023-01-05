<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configs extends Model
{
    use HasFactory;
    protected $table = TABLE_CONFIG;
    public $timestamps = false;
    protected $fillable = [FIELD_ID,FIELD_CODE, FIELD_NAME, FIELD_VALUE];
}
